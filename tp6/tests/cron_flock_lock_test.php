<?php

use app\common\command\cron\Run;
use app\common\controller\cron\Index;
use app\common\model\cron\CronConfigModel;
use app\common\model\cron\CronModel;
use app\common\model\cron\CronSchedulingLogModel;
use app\common\service\cron\CronService;
use think\App;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\response\Json;

/**
 * 计划任务调度测试脚本类
 */
class CronServiceTestDummyTask
{
    /**
     * @var array 已执行的任务 ID 顺序
     */
    public static array $executedCronIds = [];

    /**
     * 执行测试任务
     *
     * @param int $cronId 任务 ID
     * @return array
     */
    public function run($cronId): array
    {
        self::$executedCronIds[] = (int) $cronId;
        return ['status' => true, 'cron_id' => $cronId];
    }
}

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
function assertCronFlockLock(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$originalDbConfig = Config::get('database');
$originalCacheConfig = Config::get('cache');
$originalRuntimePath = $app->getRuntimePath();

$tempRoot = sys_get_temp_dir() . '/ztbcms_cron_flock_test_' . uniqid('', true);
$tempRuntimeDir = $tempRoot . '/runtime';
$tempCacheDir = $tempRoot . '/cache';
mkdir($tempRuntimeDir, 0777, true);
mkdir($tempCacheDir, 0777, true);

$holderProcess = null;
$holderPipes = [];

try {
    // 1. 隔离 runtime_path()、缓存目录与内存 SQLite 数据库
    $app->setRuntimePath($tempRuntimeDir . DIRECTORY_SEPARATOR);

    Config::set([
        'default' => 'cron_flock_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'cron_flock_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $tempCacheDir . DIRECTORY_SEPARATOR,
                'prefix' => '',
                'expire' => 0,
            ],
        ],
    ], 'cache');
    Cache::clear();

    Db::execute('CREATE TABLE cron_config (
        key VARCHAR(32) NOT NULL DEFAULT "",
        value VARCHAR(256) NOT NULL DEFAULT "",
        title VARCHAR(32) NOT NULL DEFAULT "",
        descrption VARCHAR(32) NOT NULL DEFAULT ""
    )');
    Db::execute('CREATE TABLE cron (
        cron_id INTEGER PRIMARY KEY AUTOINCREMENT,
        type INTEGER DEFAULT 0,
        subject VARCHAR(50) NOT NULL DEFAULT "",
        loop_type VARCHAR(10) NOT NULL DEFAULT "",
        loop_daytime VARCHAR(50) NOT NULL DEFAULT "",
        cron_file VARCHAR(512) NOT NULL DEFAULT "",
        isopen INTEGER NOT NULL DEFAULT 1,
        created_time INTEGER NOT NULL DEFAULT 0,
        modified_time INTEGER NOT NULL DEFAULT 0,
        next_time INTEGER NOT NULL DEFAULT 0,
        data TEXT
    )');
    Db::execute('CREATE TABLE cron_scheduling_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        start_time INTEGER NOT NULL,
        end_time INTEGER NOT NULL,
        use_time INTEGER NOT NULL,
        error_count INTEGER NOT NULL,
        cron_count INTEGER NOT NULL
    )');
    Db::execute('CREATE TABLE cron_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cron_id INTEGER NOT NULL DEFAULT 0,
        start_time INTEGER NOT NULL DEFAULT 0,
        end_time INTEGER NOT NULL DEFAULT 0,
        result INTEGER NOT NULL DEFAULT 0,
        use_time INTEGER NOT NULL DEFAULT 0,
        result_msg TEXT
    )');

    Db::name('cron_config')->insertAll([
        ['key' => CronConfigModel::KEY_ENABLE_CRON, 'value' => (string) CronConfigModel::ENABLE_YES, 'title' => '是否启用', 'descrption' => ''],
        ['key' => CronConfigModel::KEY_ENABLE_SECRET_KEY, 'value' => 'test_secret', 'title' => '私钥', 'descrption' => ''],
    ]);

    // 2. 源码检查：确保调度与 flock 锁机制统一收口在 CronService，且 Index/Run 委托给 CronService
    $serviceSource = file_get_contents(dirname(__DIR__) . '/app/common/service/cron/CronService.php');
    $indexSource = file_get_contents(dirname(__DIR__) . '/app/common/controller/cron/Index.php');
    $runSource = file_get_contents(dirname(__DIR__) . '/app/common/command/cron/Run.php');

    assertCronFlockLock(
        is_string($serviceSource) && strpos($serviceSource, 'flock($lockHandle, LOCK_EX | LOCK_NB)') !== false,
        'CronService.php 必须使用 flock($lockHandle, LOCK_EX | LOCK_NB)'
    );
    assertCronFlockLock(
        strpos($serviceSource, 'flock($lockHandle, LOCK_UN)') !== false,
        'CronService.php 必须在 finally 中调用 flock($lockHandle, LOCK_UN)'
    );
    assertCronFlockLock(
        strpos($serviceSource, 'unlink($lockfile)') === false,
        'CronService.php 不应 unlink 锁文件以免造成 inode 竞态'
    );
    foreach (['Index.php' => $indexSource, 'Run.php' => $runSource] as $filename => $source) {
        assertCronFlockLock(
            is_string($source) && strpos($source, 'CronService::runScheduler(') !== false,
            "{$filename} 必须复用 CronService::runScheduler"
        );
        assertCronFlockLock(
            strpos($source, 'flock(') === false && strpos($source, 'CronSchedulingLogModel') === false,
            "{$filename} 不应保留重复的锁处理或调度日志写入逻辑"
        );
    }

    $lockfile = runtime_path() . 'cron.lock';

    // 3. 模拟进程被 kill 后残留 cron.lock 文件（文件存在但未持 flock 锁），不应阻塞调度
    file_put_contents($lockfile, 'stale-lock-from-killed-process');
    assertCronFlockLock(file_exists($lockfile), '前置测试条件：残留锁文件应存在');

    $controller = new Index($app);
    $resp1 = $controller->index('test_secret');
    assertCronFlockLock($resp1 instanceof Json, 'Web 调度入口应返回 Json');
    $data1 = $resp1->getData();
    assertCronFlockLock(
        ($data1['msg'] ?? '') === 'Cron status: finish',
        '残留未加锁的 cron.lock 不应阻塞 Web 调度，实际返回: ' . ($data1['msg'] ?? '')
    );

    $command = new Run();
    $command->setApp($app);
    $output1 = new Output('buffer');
    $command->run(new Input([]), $output1);
    $cmdOut1 = $output1->fetch();
    assertCronFlockLock(
        strpos($cmdOut1, 'Cron status: finish') !== false,
        '残留未加锁的 cron.lock 不应阻塞 cron:run 命令，实际输出: ' . $cmdOut1
    );
    assertCronFlockLock(CronSchedulingLogModel::count() === 2, '两次调度应各写入 1 条调度日志');

    // 4. 启动独立子进程对 cron.lock 持有排他锁，验证真实跨进程并发互斥
    $workerPhp = sprintf(
        '$h = fopen(%s, "c+"); if (!$h || !flock($h, LOCK_EX | LOCK_NB)) { echo "FAIL\n"; exit(2); } echo "LOCKED\n"; fflush(STDOUT); fgets(STDIN); flock($h, LOCK_UN); fclose($h);',
        var_export($lockfile, true)
    );
    $cmd = escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($workerPhp);
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $holderProcess = proc_open($cmd, $descriptorSpec, $holderPipes);
    assertCronFlockLock(is_resource($holderProcess), '未能启动持锁测试子进程');

    $readyLine = fgets($holderPipes[1]);
    assertCronFlockLock(
        is_string($readyLine) && trim($readyLine) === 'LOCKED',
        '持锁子进程未能成功获取 flock 排他锁: ' . var_export($readyLine, true)
    );

    $respLocked = $controller->index('test_secret');
    $dataLocked = $respLocked->getData();
    assertCronFlockLock(
        ($dataLocked['used_time'] ?? -1) === 0 && ($dataLocked['msg'] ?? '') === 'Cron is Locked',
        '并发持锁期间 Web 调度应返回 Cron is Locked，实际: ' . json_encode($dataLocked, JSON_UNESCAPED_UNICODE)
    );

    $outputLocked = new Output('buffer');
    $command->run(new Input([]), $outputLocked);
    $cmdOutLocked = $outputLocked->fetch();
    assertCronFlockLock(
        strpos($cmdOutLocked, '已存在文件锁 cron.lock') !== false,
        '并发持锁期间 cron:run 应输出锁冲突提示，实际: ' . $cmdOutLocked
    );
    assertCronFlockLock(CronSchedulingLogModel::count() === 2, '被锁拦截的调度不应新增日志记录');

    // 5. 通知子进程释放锁并退出后，两个入口均恢复正常执行
    fwrite($holderPipes[0], "release\n");
    fclose($holderPipes[0]);
    fclose($holderPipes[1]);
    fclose($holderPipes[2]);
    $exitCode = proc_close($holderProcess);
    $holderProcess = null;
    $holderPipes = [];
    assertCronFlockLock($exitCode === 0, '持锁子进程应正常退出，退出码: ' . $exitCode);

    $respAfter = $controller->index('test_secret');
    $dataAfter = $respAfter->getData();
    assertCronFlockLock(
        ($dataAfter['msg'] ?? '') === 'Cron status: finish',
        '锁释放后 Web 调度应恢复正常，实际: ' . ($dataAfter['msg'] ?? '')
    );

    $outputAfter = new Output('buffer');
    $command->run(new Input([]), $outputAfter);
    $cmdOutAfter = $outputAfter->fetch();
    assertCronFlockLock(
        strpos($cmdOutAfter, 'Cron status: finish') !== false,
        '锁释放后 cron:run 应恢复正常，实际: ' . $cmdOutAfter
    );
    assertCronFlockLock(CronSchedulingLogModel::count() === 4, '锁释放后两次调度应累计写入 4 条调度日志');

    // 6. 验证 Web 私钥拦截与计划任务停用状态
    $respBadSecret = $controller->index('wrong_secret');
    $badSecretData = $respBadSecret->getData();
    assertCronFlockLock(
        ($badSecretData['used_time'] ?? -1) === 0 && ($badSecretData['msg'] ?? '') === 'Secret key invalidated',
        'Web 私钥错误应返回 Secret key invalidated，实际: ' . json_encode($badSecretData, JSON_UNESCAPED_UNICODE)
    );

    Db::name('cron_config')
        ->where('key', CronConfigModel::KEY_ENABLE_CRON)
        ->update(['value' => '0']);

    $respDisabled = $controller->index('test_secret');
    $disabledData = $respDisabled->getData();
    assertCronFlockLock(
        ($disabledData['msg'] ?? '') === 'Cron status: stop',
        '停用后 Web 调度应返回 Cron status: stop，实际: ' . ($disabledData['msg'] ?? '')
    );

    $outputDisabled = new Output('buffer');
    $command->run(new Input([]), $outputDisabled);
    $cmdOutDisabled = $outputDisabled->fetch();
    assertCronFlockLock(
        strpos($cmdOutDisabled, '计划任务功能已关闭') !== false,
        '停用后 cron:run 应提示功能已关闭，实际: ' . $cmdOutDisabled
    );

    // 7. 恢复启用并验证到期任务实际调度、进度输出与不遗漏小 ID 任务
    Db::name('cron_config')
        ->where('key', CronConfigModel::KEY_ENABLE_CRON)
        ->update(['value' => (string) CronConfigModel::ENABLE_YES]);

    $now = time();
    Db::name('cron')->insertAll([
        [
            'cron_id' => 1,
            'type' => 0,
            'subject' => '小ID晚到期任务',
            'loop_type' => 'now',
            'loop_daytime' => '0-0-5',
            'cron_file' => CronServiceTestDummyTask::class,
            'isopen' => CronModel::OPEN_STATUS_ON,
            'created_time' => $now - 100,
            'modified_time' => $now - 100,
            'next_time' => $now - 10,
            'data' => '',
        ],
        [
            'cron_id' => 2,
            'type' => 0,
            'subject' => '大ID早到期任务',
            'loop_type' => 'now',
            'loop_daytime' => '0-0-5',
            'cron_file' => CronServiceTestDummyTask::class,
            'isopen' => CronModel::OPEN_STATUS_ON,
            'created_time' => $now - 100,
            'modified_time' => $now - 100,
            'next_time' => $now - 60,
            'data' => '',
        ],
    ]);

    CronServiceTestDummyTask::$executedCronIds = [];
    $outputProgress = new Output('buffer');
    $command->run(new Input(['-p', '1']), $outputProgress);
    $cmdOutProgress = $outputProgress->fetch();

    assertCronFlockLock(
        CronServiceTestDummyTask::$executedCronIds === [2, 1],
        '调度应按 next_time ASC 顺序执行全部到期任务且不遗漏小 ID 任务，实际顺序: ' . json_encode(CronServiceTestDummyTask::$executedCronIds)
    );
    assertCronFlockLock(
        strpos($cmdOutProgress, 'Processing:' . CronServiceTestDummyTask::class) !== false
        && strpos($cmdOutProgress, 'Processed :' . CronServiceTestDummyTask::class) !== false,
        '传 -p 1 时 cron:run 应输出进度信息，实际: ' . $cmdOutProgress
    );
    assertCronFlockLock(
        strpos($cmdOutProgress, '"total_amount":2') !== false && strpos($cmdOutProgress, '"error_amount":0') !== false,
        'cron:run 输出 JSON 应统计 total_amount=2 与 error_amount=0，实际: ' . $cmdOutProgress
    );
    assertCronFlockLock(
        (int) Db::name('cron')->where('cron_id', 1)->value('next_time') > $now
        && (int) Db::name('cron')->where('cron_id', 2)->value('next_time') > $now,
        '两个到期任务执行后 next_time 应被推进至未来时间'
    );
    assertCronFlockLock(
        (int) Db::name('cron_log')->count() === 2,
        '两个到期任务应写入 2 条 cron_log 执行日志'
    );

    echo "cron_flock_lock_test passed\n";
} finally {
    if (is_resource($holderProcess)) {
        foreach ($holderPipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        proc_terminate($holderProcess);
        proc_close($holderProcess);
    }
    $app->setRuntimePath($originalRuntimePath);
    if (is_array($originalDbConfig)) {
        Config::set($originalDbConfig, 'database');
    }
    if (is_array($originalCacheConfig)) {
        Config::set($originalCacheConfig, 'cache');
    }
    restore_error_handler();
    if (is_dir($tempRoot)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tempRoot);
    }
}
