<?php

use app\admin\libs\module\ModuleInstaller;
use app\admin\libs\module\ModuleUninstaller;
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
function assertModuleInstallerUnusedUse(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tmpModule = '__TmpModUnusedUse_' . getmypid() . '_' . str_replace('.', '', uniqid('', true));
$tmpModuleBaseDir = base_path() . $tmpModule;
$tmpSourceExtraDir = $tmpModuleBaseDir . '/install/extra/';
$extraRootDir = public_path() . 'statics/extra';
$extraRootExisted = is_dir($extraRootDir);
$tmpDesDir = $extraRootDir . '/' . strtolower($tmpModule) . '/';
$installer = new ModuleInstaller($tmpModule);
$uninstaller = new ModuleUninstaller($tmpModule);

try {
    // 1. 验证两个类的源码已清除未使用的 use，且 _copyResource 改用 is_dir
    $uninstallerSource = file_get_contents(dirname(__DIR__) . '/app/admin/libs/module/ModuleUninstaller.php');
    $installerSource = file_get_contents(dirname(__DIR__) . '/app/admin/libs/module/ModuleInstaller.php');

    assertModuleInstallerUnusedUse(
        $uninstallerSource !== false && strpos($uninstallerSource, 'ErrorException') === false,
        'ModuleUninstaller.php 中不应再包含未使用的 ErrorException 导入'
    );
    assertModuleInstallerUnusedUse(
        $installerSource !== false
        && strpos($installerSource, 'use think\File;') === false
        && strpos($installerSource, 'new File(') === false
        && strpos($installerSource, 'is_dir($resource_dir)') !== false,
        'ModuleInstaller.php 中应移除 use think\File; 与 new File(...)，并在 _copyResource 中使用 is_dir($resource_dir)'
    );

    // 2. 验证两类可正常实例化
    assertModuleInstallerUnusedUse(
        $installer instanceof ModuleInstaller && $uninstaller instanceof ModuleUninstaller,
        'ModuleInstaller 与 ModuleUninstaller 应能正常实例化'
    );

    // 3. 验证 _copyResource('') 返回失败
    $emptyRes = $installer->_copyResource('');
    assertModuleInstallerUnusedUse(
        ($emptyRes['status'] ?? true) === false,
        '_copyResource 传入空模块名应返回 status=false'
    );

    // 4. 验证当 install/extra/ 不存在时返回成功且不创建目标目录
    $nonexistentModule = '__nonexistent_mod_' . getmypid();
    $nonexistentDesDir = $extraRootDir . '/' . strtolower($nonexistentModule) . '/';
    $nonexistentRes = $installer->_copyResource($nonexistentModule);
    assertModuleInstallerUnusedUse(
        ($nonexistentRes['status'] ?? false) === true && !is_dir($nonexistentDesDir),
        '_copyResource 在模块不存在 install/extra/ 时应直接返回成功且不创建目标目录'
    );

    // 5. 验证当临时模块 install/extra/asset.txt 存在时，资源能被正确复制到 public/statics/extra/<lowercase>/
    if (!is_dir($tmpSourceExtraDir)) {
        mkdir($tmpSourceExtraDir, 0755, true);
    }
    $expectedContent = 'unused-use-cleanup-asset-' . getmypid();
    file_put_contents($tmpSourceExtraDir . 'asset.txt', $expectedContent);

    $copyRes = $installer->_copyResource($tmpModule);
    assertModuleInstallerUnusedUse(
        ($copyRes['status'] ?? false) === true,
        '_copyResource 存在资源目录时应返回 status=true'
    );
    $copiedFile = $tmpDesDir . 'asset.txt';
    assertModuleInstallerUnusedUse(
        is_file($copiedFile) && file_get_contents($copiedFile) === $expectedContent,
        '_copyResource 应将 asset.txt 正确复制到 ' . $copiedFile
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    if (is_dir($tmpModuleBaseDir)) {
        $installer->_delDir($tmpModuleBaseDir);
    }
    if (is_dir($tmpDesDir)) {
        $installer->_delDir($tmpDesDir);
    }
    if (!$extraRootExisted && is_dir($extraRootDir)) {
        @rmdir($extraRootDir);
    }
    restore_error_handler();
}

echo "admin_module_installer_unused_use_test passed\n";
