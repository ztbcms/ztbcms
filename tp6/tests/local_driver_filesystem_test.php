<?php

use app\common\libs\upload\LocalDriver;
use think\App;
use think\facade\Config;
use think\facade\Filesystem;

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
function assertLocalDriverFilesystem(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tempDiskRoot = sys_get_temp_dir() . '/ztbcms_local_driver_test_' . uniqid('', true);
if (!is_dir($tempDiskRoot)) {
    mkdir($tempDiskRoot, 0777, true);
}

try {
    // 1. 核对默认磁盘配置中的 ztbcms 磁盘定义
    $filesystemConfig = Config::get('filesystem');
    $ztbcmsRoot = $filesystemConfig['disks']['ztbcms']['root'] ?? '';
    $normalizedRoot = str_replace('\\', '/', $ztbcmsRoot);
    assertLocalDriverFilesystem(
        substr($normalizedRoot, -strlen('/public/d/file')) === '/public/d/file',
        'ztbcms 磁盘默认根目录应指向 public/d/file，当前为: ' . $ztbcmsRoot
    );

    // 2. 将 ztbcms 磁盘指向临时目录进行隔离测试
    $filesystemConfig['disks']['ztbcms']['root'] = $tempDiskRoot;
    Config::set($filesystemConfig, 'filesystem');
    $app->delete('filesystem');

    $driver = new LocalDriver([
        'attachment_local_domain' => 'http://127.0.0.1',
    ]);

    $relativePath = 'image/20261114/test_file.txt';
    $realFile = $tempDiskRoot . DIRECTORY_SEPARATOR . $relativePath;
    mkdir(dirname($realFile), 0777, true);
    file_put_contents($realFile, 'ztbcms-local-driver-test');

    // 3. 验证 exists()
    assertLocalDriverFilesystem($driver->exists($relativePath) === true, 'ztbcms 磁盘下存在的文件应返回 true');
    assertLocalDriverFilesystem($driver->exists('image/20261114/missing.txt') === false, '不存在的文件应返回 false');
    assertLocalDriverFilesystem($driver->exists('') === false, '空路径 exists() 应返回 false');
    assertLocalDriverFilesystem($driver->exists('   ') === false, '空白路径 exists() 应返回 false');
    assertLocalDriverFilesystem($driver->exists('../outside.txt') === false, '越权相对路径 exists() 应返回 false');

    // 4. 验证 delete()
    assertLocalDriverFilesystem($driver->delete($relativePath) === true, '删除存在的文件应返回 true');
    assertLocalDriverFilesystem(!file_exists($realFile), '删除后磁盘物理文件应已被移除');
    assertLocalDriverFilesystem($driver->exists($relativePath) === false, '删除后 exists() 应返回 false');
    assertLocalDriverFilesystem($driver->delete('image/20261114/missing.txt') === true, '删除不存在的文件应保持幂等返回 true');
    assertLocalDriverFilesystem($driver->delete('') === true, '空路径 delete() 应返回 true');
    assertLocalDriverFilesystem($driver->delete('../outside.txt') === false, '越权相对路径 delete() 应捕获异常并返回 false');

    // 5. 验证通过 Flysystem 写入的文件同样可被识别并删除
    Filesystem::disk(LocalDriver::DISK_CONFIG)->write('file/20261114/fly.log', 'fly-content');
    assertLocalDriverFilesystem($driver->exists('file/20261114/fly.log') === true, 'Flysystem 写入文件后 exists() 应返回 true');
    assertLocalDriverFilesystem($driver->delete('file/20261114/fly.log') === true, 'Flysystem 写入文件应可被 delete() 删除');
    assertLocalDriverFilesystem($driver->exists('file/20261114/fly.log') === false, 'Flysystem 写入文件删除后 exists() 应返回 false');

    echo "local_driver_filesystem_test passed\n";
} finally {
    restore_error_handler();
    if (is_dir($tempDiskRoot)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDiskRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tempDiskRoot);
    }
}
