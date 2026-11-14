<?php

use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;
use think\App;
use think\facade\Config;
use think\facade\Db;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

set_error_handler(
    /**
     * 将 PHP 警告与提示转换为异常
     *
     * @param int $severity 错误级别
     * @param string $message 错误信息
     * @param string $file 错误文件
     * @param int $line 错误行号
     * @return bool
     */
    function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

/**
 * 断言条件成立
 *
 * @param bool $condition 条件结果
 * @param string $message 失败提示
 * @return void
 */
function assertDownloaderServiceCreateTaskState(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    Config::set([
        'default' => 'downloader_service_state_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'downloader_service_state_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Config::set([
        'default' => 'database',
        'connections' => [
            'database' => [
                'type' => 'database',
                'queue' => 'default',
                'table' => 'queue_jobs',
                'connection' => 'downloader_service_state_test',
                'retry_after' => 180,
            ],
        ],
        'monitor' => [
            'enable' => false,
        ],
    ], 'queue');
    $app->delete('queue');

    Db::execute('CREATE TABLE downloader (
        downloader_id INTEGER PRIMARY KEY AUTOINCREMENT,
        downloader_url TEXT NOT NULL,
        downloader_url_hash VARCHAR(255) DEFAULT NULL,
        downloader_state INTEGER NOT NULL DEFAULT 0,
        downloader_result VARCHAR(255) NOT NULL DEFAULT "",
        downloader_duration INTEGER NOT NULL DEFAULT 0,
        process_start_time INTEGER NOT NULL DEFAULT 0,
        process_end_time INTEGER NOT NULL DEFAULT 0,
        file_name VARCHAR(255) NOT NULL DEFAULT "",
        file_path VARCHAR(255) NOT NULL DEFAULT "",
        file_url VARCHAR(255) NOT NULL DEFAULT "",
        file_thumb VARCHAR(256) NOT NULL DEFAULT "",
        file_hash VARCHAR(255) NOT NULL DEFAULT "",
        file_size INTEGER NOT NULL DEFAULT 0,
        file_ext VARCHAR(32) NOT NULL DEFAULT "",
        downloader_implement_num INTEGER NOT NULL DEFAULT 0,
        downloader_next_implement_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER DEFAULT 0,
        update_time INTEGER DEFAULT 0,
        delete_time INTEGER DEFAULT 0
    )');

    Db::execute('CREATE TABLE queue_jobs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        queue VARCHAR(255) NOT NULL,
        payload TEXT NOT NULL,
        attempts INTEGER NOT NULL,
        reserve_time INTEGER DEFAULT NULL,
        available_time INTEGER NOT NULL,
        create_time INTEGER NOT NULL
    )');

    // 1. STATE_PROCESSING（下载中）：修复后应返回下载中，不能落入状态异常分支
    $processingUrl = 'https://example.com/processing.zip';
    $processingId = Db::name('downloader')->insertGetId([
        'downloader_url' => $processingUrl,
        'downloader_url_hash' => md5($processingUrl),
        'downloader_state' => DownloaderModel::STATE_PROCESSING,
    ]);
    $processingRes = DownloaderService::createDownloaderTask($processingUrl);
    assertDownloaderServiceCreateTaskState(
        ($processingRes['status'] ?? false) === true,
        'STATE_PROCESSING 时 status 应为 true'
    );
    assertDownloaderServiceCreateTaskState(
        intval($processingRes['data']['downloader_id'] ?? 0) === intval($processingId),
        'STATE_PROCESSING 时返回的 downloader_id 应一致'
    );
    assertDownloaderServiceCreateTaskState(
        ($processingRes['msg'] ?? '') === '下载中,请稍候',
        'STATE_PROCESSING 时提示应为 下载中,请稍候，实际为: ' . ($processingRes['msg'] ?? '')
    );

    // 2. STATE_WAIT（待下载）
    $waitUrl = 'https://example.com/wait.zip';
    $waitId = Db::name('downloader')->insertGetId([
        'downloader_url' => $waitUrl,
        'downloader_url_hash' => md5($waitUrl),
        'downloader_state' => DownloaderModel::STATE_WAIT,
    ]);
    $waitRes = DownloaderService::createDownloaderTask($waitUrl);
    assertDownloaderServiceCreateTaskState(
        ($waitRes['status'] ?? false) === true && ($waitRes['msg'] ?? '') === '下载中,请稍候',
        'STATE_WAIT 时应返回 下载中,请稍候'
    );
    assertDownloaderServiceCreateTaskState(
        intval($waitRes['data']['downloader_id'] ?? 0) === intval($waitId),
        'STATE_WAIT 时返回的 downloader_id 应一致'
    );

    // 3. STATE_SUCCESS（已下载）
    $successUrl = 'https://example.com/success.zip';
    $successId = Db::name('downloader')->insertGetId([
        'downloader_url' => $successUrl,
        'downloader_url_hash' => md5($successUrl),
        'downloader_state' => DownloaderModel::STATE_SUCCESS,
    ]);
    $successRes = DownloaderService::createDownloaderTask($successUrl);
    assertDownloaderServiceCreateTaskState(
        ($successRes['status'] ?? false) === true && ($successRes['msg'] ?? '') === '已下载',
        'STATE_SUCCESS 时应返回 已下载'
    );
    assertDownloaderServiceCreateTaskState(
        intval($successRes['data']['downloader_id'] ?? 0) === intval($successId),
        'STATE_SUCCESS 时返回的 downloader_id 应一致'
    );

    // 4. STATE_FAIL（失败重试，重置状态并入队）
    $failUrl = 'https://example.com/fail.zip';
    $failId = Db::name('downloader')->insertGetId([
        'downloader_url' => $failUrl,
        'downloader_url_hash' => md5($failUrl),
        'downloader_state' => DownloaderModel::STATE_FAIL,
        'process_start_time' => 1700000000,
        'process_end_time' => 1700000005,
    ]);
    $failRes = DownloaderService::createDownloaderTask($failUrl);
    assertDownloaderServiceCreateTaskState(
        ($failRes['status'] ?? false) === true && ($failRes['msg'] ?? '') === '下载任务创建成功',
        'STATE_FAIL 时应触发失败重试并重新创建下载任务'
    );
    $retriedRow = Db::name('downloader')->where('downloader_id', $failId)->find();
    assertDownloaderServiceCreateTaskState(
        intval($retriedRow['downloader_state']) === DownloaderModel::STATE_WAIT,
        '失败重试后状态应重置为 STATE_WAIT'
    );
    assertDownloaderServiceCreateTaskState(
        Db::name('queue_jobs')->where('queue', 'downloader')->count() === 1,
        '失败重试后应向 downloader 队列推入 1 条任务'
    );

    // 5. 未知异常状态应落入状态异常分支
    $invalidUrl = 'https://example.com/invalid.zip';
    Db::name('downloader')->insertGetId([
        'downloader_url' => $invalidUrl,
        'downloader_url_hash' => md5($invalidUrl),
        'downloader_state' => 99,
    ]);
    $invalidRes = DownloaderService::createDownloaderTask($invalidUrl);
    assertDownloaderServiceCreateTaskState(
        ($invalidRes['status'] ?? true) === false && ($invalidRes['msg'] ?? '') === '状态异常，请开发人员检查',
        '未知状态应返回 状态异常，请开发人员检查'
    );

    echo "downloader_service_create_task_state_test passed\n";
} finally {
    restore_error_handler();
}
