<?php

use app\common\libs\upload\LocalDriver;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\file\UploadedFile;
use think\Request;

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
function assertUploadServiceClientIp(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 构造带有测试上传文件及客户端 IP 的隔离请求对象
 *
 * @param string $sourcePath 源文件路径
 * @param string $originalName 原始文件名
 * @param string $clientIp 客户端 IP
 * @return Request
 */
function createMockUploadRequest(string $sourcePath, string $originalName, string $clientIp): Request
{
    $uploadedFile = new UploadedFile($sourcePath, $originalName, 'image/png', UPLOAD_ERR_OK, true);
    $request = new class($uploadedFile) extends Request {
        private UploadedFile $mockFile;

        public function __construct(UploadedFile $mockFile)
        {
            parent::__construct();
            $this->mockFile = $mockFile;
        }

        public function file(string $name = '')
        {
            if ($name === '') {
                return ['file' => $this->mockFile];
            }

            return $name === 'file' ? $this->mockFile : null;
        }
    };
    $request->withServer(['REMOTE_ADDR' => $clientIp]);

    return $request;
}

$tempRoot = sys_get_temp_dir() . '/ztbcms_upload_service_ip_test_' . uniqid('', true);
$tempCacheDir = $tempRoot . '/cache';
$tempDiskDir = $tempRoot . '/disk';
$tempUploadSrcDir = $tempRoot . '/src';
mkdir($tempCacheDir, 0777, true);
mkdir($tempDiskDir, 0777, true);
mkdir($tempUploadSrcDir, 0777, true);

$originalRequest = $app->make('request');

try {
    // 1. 源码检查：确认 UploadService 已彻底脱离 EasyWeChat\Kernel\Support\get_client_ip，改用 request()->ip()
    $serviceSource = file_get_contents(dirname(__DIR__) . '/app/common/service/upload/UploadService.php');
    assertUploadServiceClientIp(is_string($serviceSource), '读取 UploadService.php 失败');
    assertUploadServiceClientIp(
        strpos($serviceSource, 'EasyWeChat') === false,
        'UploadService.php 不应再依赖 EasyWeChat'
    );
    assertUploadServiceClientIp(
        strpos($serviceSource, 'get_client_ip') === false,
        'UploadService.php 不应再调用 get_client_ip'
    );
    assertUploadServiceClientIp(
        strpos($serviceSource, 'request()->ip()') !== false,
        'UploadService.php 应使用 request()->ip() 获取上传客户端 IP'
    );

    // 2. 配置隔离的 SQLite 内存库、文件缓存与 ztbcms 本地存储磁盘
    Config::set([
        'default' => 'upload_service_ip_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'upload_service_ip_test' => [
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

    $filesystemConfig = Config::get('filesystem');
    $filesystemConfig['disks'][LocalDriver::DISK_CONFIG]['root'] = $tempDiskDir;
    Config::set($filesystemConfig, 'filesystem');
    $app->delete('filesystem');

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        value TEXT NOT NULL
    )');
    Db::name('config')->insertAll([
        ['varname' => 'attachment_driver', 'value' => 'Local'],
        ['varname' => 'attachment_local_domain', 'value' => 'http://127.0.0.1'],
        ['varname' => 'uploadmaxsize', 'value' => '2048'],
        ['varname' => 'uploadallowext', 'value' => 'png|jpg'],
        ['varname' => 'qtuploadmaxsize', 'value' => '2048'],
        ['varname' => 'qtuploadallowext', 'value' => 'png|jpg'],
    ]);

    Db::execute('CREATE TABLE attachment (
        aid INTEGER PRIMARY KEY AUTOINCREMENT,
        driver VARCHAR(32) NOT NULL DEFAULT "",
        module VARCHAR(32) NOT NULL DEFAULT "",
        filename VARCHAR(255) NOT NULL DEFAULT "",
        filepath VARCHAR(255) NOT NULL DEFAULT "",
        fileurl VARCHAR(255) NOT NULL DEFAULT "",
        filethumb VARCHAR(255) NOT NULL DEFAULT "",
        filesize INTEGER NOT NULL DEFAULT 0,
        fileext VARCHAR(32) NOT NULL DEFAULT "",
        user_type VARCHAR(32) NOT NULL DEFAULT "",
        user_id INTEGER NOT NULL DEFAULT 0,
        group_id INTEGER NOT NULL DEFAULT 0,
        is_private INTEGER NOT NULL DEFAULT 0,
        hash VARCHAR(64) NOT NULL DEFAULT "",
        upload_ip VARCHAR(64) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0,
        delete_time INTEGER NOT NULL DEFAULT 0
    )');

    // 生成合法 1x1 PNG 测试图片
    $pngBytes = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
    );
    assertUploadServiceClientIp(is_string($pngBytes) && $pngBytes !== '', '生成测试 PNG 数据失败');

    // 3. 验证首次上传记录的 upload_ip 与 request()->ip() 一致
    $srcFile1 = $tempUploadSrcDir . '/pixel1.png';
    file_put_contents($srcFile1, $pngBytes);
    $expectedIp1 = '203.0.113.88';
    $request1 = createMockUploadRequest($srcFile1, 'pixel1.png', $expectedIp1);
    $app->instance('request', $request1);

    $service = new UploadService('Local');
    $attachment = $service->uploadAny(0, 1001, AttachmentModel::USER_TYPE_ADMIN);
    assertUploadServiceClientIp(
        $attachment instanceof AttachmentModel,
        'uploadAny 应成功保存附件记录，错误: ' . $service->getError()
    );
    assertUploadServiceClientIp(
        $attachment->upload_ip === $expectedIp1,
        "uploadAny 写入的 upload_ip 应为 {$expectedIp1}，实际为: " . (string) $attachment->upload_ip
    );

    // 4. 验证不同请求更换 REMOTE_ADDR 后 uploadAny 也能准确记录新的 request()->ip()，不存在静态缓存污染
    $srcFile2 = $tempUploadSrcDir . '/pixel2.png';
    file_put_contents($srcFile2, $pngBytes);
    $expectedIp2 = '198.51.100.77';
    $request2 = createMockUploadRequest($srcFile2, 'pixel2.png', $expectedIp2);
    $app->instance('request', $request2);

    $attachment2 = $service->uploadAny(0, 2002, 'api');
    assertUploadServiceClientIp(
        $attachment2 instanceof AttachmentModel,
        '第二次 uploadAny 应成功保存附件记录，错误: ' . $service->getError()
    );
    assertUploadServiceClientIp(
        $attachment2->upload_ip === $expectedIp2,
        "第二次请求写入的 upload_ip 应为 {$expectedIp2}，实际为: " . (string) $attachment2->upload_ip
    );

    echo "upload_service_client_ip_test passed\n";
} finally {
    $app->instance('request', $originalRequest);
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
