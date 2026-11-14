<?php

use app\common\libs\downloader\DownloaderTool;
use app\common\model\upload\AttachmentModel;
use think\App;
use think\facade\Cache;
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
function assertDownloaderToolSameBucket(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$uniqueId = str_replace('.', '_', uniqid('', true));
$tempSourceDir = sys_get_temp_dir() . '/ztbcms_downloader_src_' . $uniqueId;
$tempCacheDir = sys_get_temp_dir() . '/ztbcms_downloader_cache_' . $uniqueId;
mkdir($tempSourceDir, 0777, true);
mkdir($tempCacheDir, 0777, true);

$createdTargets = [];
$createdBucketDir = null;
$downloaderRootExisted = is_dir($app->getRootPath() . 'public/downloader');

try {
    // 1. 初始化隔离的 SQLite 内存库与文件缓存
    Config::set([
        'default' => 'downloader_tool_same_bucket_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'downloader_tool_same_bucket_test' => [
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

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'downloader_domain',
        'value' => 'http://127.0.0.1/',
    ]);

    // 2. 构造两个 md5(url) 前 2 位相同的本地 file:// URL
    $srcPath1 = $tempSourceDir . '/file_a.txt';
    $url1 = 'file://' . $srcPath1;
    $bucket = substr(md5($url1), 0, 2);

    $srcPath2 = '';
    $url2 = '';
    for ($i = 0; $i < 10000; $i++) {
        $candidatePath = $tempSourceDir . '/file_b_' . $i . '.txt';
        $candidateUrl = 'file://' . $candidatePath;
        if (substr(md5($candidateUrl), 0, 2) === $bucket) {
            $srcPath2 = $candidatePath;
            $url2 = $candidateUrl;
            break;
        }
    }
    assertDownloaderToolSameBucket($url2 !== '', '未能构造出同分桶测试 URL');

    $content1 = 'first-file-in-bucket-' . $uniqueId;
    $content2 = 'second-file-in-same-bucket-with-different-length-' . $uniqueId;
    file_put_contents($srcPath1, $content1);
    file_put_contents($srcPath2, $content2);

    $bucketDir = $app->getRootPath() . 'public/downloader/' . $bucket;
    if (!is_dir($bucketDir)) {
        $createdBucketDir = $bucketDir;
    }

    $targetName1 = 'test_bucket_first_' . $uniqueId . '.txt';
    $targetName2 = 'test_bucket_second_' . $uniqueId . '.txt';
    $targetFile1 = $bucketDir . '/' . $targetName1;
    $targetFile2 = $bucketDir . '/' . $targetName2;
    $createdTargets[] = $targetFile1;
    $createdTargets[] = $targetFile2;

    // 3. 下载同桶第 1 个文件
    $res1 = DownloaderTool::doDownloadFile($url1, $targetName1, 'txt', AttachmentModel::MODULE_FILE);
    assertDownloaderToolSameBucket(($res1['status'] ?? false) === true, '同桶第 1 个文件应下载成功: ' . ($res1['msg'] ?? ''));
    assertDownloaderToolSameBucket(is_file($targetFile1), '第 1 个目标文件应写入磁盘');
    assertDownloaderToolSameBucket(file_get_contents($targetFile1) === $content1, '第 1 个文件写入内容不一致');
    assertDownloaderToolSameBucket(
        intval($res1['data']['file_size'] ?? 0) === strlen($content1),
        '第 1 个文件大小不匹配'
    );
    assertDownloaderToolSameBucket(
        ($res1['data']['file_hash'] ?? '') === md5($content1),
        '第 1 个文件哈希不匹配'
    );

    // 4. 下载同桶第 2 个文件（目录已存在时仍应正常执行 curl 下载并写入）
    $res2 = DownloaderTool::doDownloadFile($url2, $targetName2, 'txt', AttachmentModel::MODULE_FILE);
    assertDownloaderToolSameBucket(($res2['status'] ?? false) === true, '同桶第 2 个文件应下载成功: ' . ($res2['msg'] ?? ''));
    assertDownloaderToolSameBucket(is_file($targetFile2), '第 2 个目标文件应写入磁盘');
    assertDownloaderToolSameBucket(file_get_contents($targetFile2) === $content2, '第 2 个文件写入内容不一致');
    assertDownloaderToolSameBucket(
        intval($res2['data']['file_size'] ?? 0) === strlen($content2),
        '第 2 个文件大小不匹配'
    );
    assertDownloaderToolSameBucket(
        ($res2['data']['file_hash'] ?? '') === md5($content2),
        '第 2 个文件哈希不匹配'
    );
    assertDownloaderToolSameBucket(
        ($res2['data']['file_path'] ?? '') === '/downloader/' . $bucket . '/' . $targetName2,
        '第 2 个文件相对路径不正确'
    );
    assertDownloaderToolSameBucket(
        ($res2['data']['file_url'] ?? '') === 'http://127.0.0.1/downloader/' . $bucket . '/' . $targetName2,
        '第 2 个文件访问 URL 不正确'
    );

    // 5. 验证不存在源 URL 时能安全返回 CURL 错误且不报资源未关闭警告
    $missingRes = DownloaderTool::doDownloadFile(
        'file://' . $tempSourceDir . '/not_found_' . $uniqueId . '.txt',
        'test_bucket_missing_' . $uniqueId . '.txt',
        'txt',
        AttachmentModel::MODULE_FILE
    );
    assertDownloaderToolSameBucket(($missingRes['status'] ?? true) === false, '缺失源文件的下载应返回失败');
    assertDownloaderToolSameBucket(
        strpos((string) ($missingRes['msg'] ?? ''), 'CURL Error:') === 0,
        '缺失源文件应返回 CURL Error 提示'
    );

    echo "downloader_tool_same_bucket_test passed\n";
} finally {
    restore_error_handler();
    foreach ($createdTargets as $targetFile) {
        if (is_file($targetFile)) {
            unlink($targetFile);
        }
    }
    if ($createdBucketDir !== null && is_dir($createdBucketDir)) {
        $remaining = array_diff(scandir($createdBucketDir), ['.', '..']);
        if (empty($remaining)) {
            rmdir($createdBucketDir);
        }
    }
    if (!$downloaderRootExisted && is_dir($app->getRootPath() . 'public/downloader')) {
        $rootRemaining = array_diff(scandir($app->getRootPath() . 'public/downloader'), ['.', '..']);
        if (empty($rootRemaining)) {
            rmdir($app->getRootPath() . 'public/downloader');
        }
    }
    foreach ([$tempSourceDir, $tempCacheDir] as $dir) {
        if (is_dir($dir)) {
            $items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($items as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($dir);
        }
    }
}
