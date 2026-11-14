<?php

use think\App;

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
function assertRemoveUnusedNetworkIp(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证文件与目录均已删除，且类不可自动加载
    $ipFile = dirname(__DIR__) . '/app/common/libs/network/Ip.php';
    assertRemoveUnusedNetworkIp(!file_exists($ipFile), 'libs/network/Ip.php 应已删除');
    assertRemoveUnusedNetworkIp(
        !class_exists('app\\common\\libs\\network\\Ip'),
        'app\\common\\libs\\network\\Ip 类不应再存在'
    );

    // 2. 验证整个 tp6/app 目录下无任何对 app\common\libs\network\Ip 的残留引用
    $appIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__) . '/app', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($appIterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $content = file_get_contents($fileInfo->getPathname());
        assertRemoveUnusedNetworkIp(is_string($content), '读取文件失败: ' . $fileInfo->getPathname());
        assertRemoveUnusedNetworkIp(
            strpos($content, 'libs\\network\\Ip') === false,
            '源码中不应存在 libs\\network\\Ip 引用: ' . $fileInfo->getPathname()
        );
    }

    echo "remove_unused_network_ip_test passed\n";
} finally {
    restore_error_handler();
}
