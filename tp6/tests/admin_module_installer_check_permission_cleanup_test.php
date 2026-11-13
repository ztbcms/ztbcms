<?php

use app\admin\libs\module\ModuleInstaller;
use app\admin\libs\module\ModuleUninstaller;
use think\App;
use think\facade\Config;

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
function assertModuleInstallerCheckPermissionCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证 ModuleInstaller 与 ModuleUninstaller 均不再存在 _checkPermission 方法
    $installerRef = new ReflectionClass(ModuleInstaller::class);
    $uninstallerRef = new ReflectionClass(ModuleUninstaller::class);

    assertModuleInstallerCheckPermissionCleanup(
        !$installerRef->hasMethod('_checkPermission'),
        'ModuleInstaller::_checkPermission 应已移除'
    );
    assertModuleInstallerCheckPermissionCleanup(
        !$uninstallerRef->hasMethod('_checkPermission'),
        'ModuleUninstaller 不应继承或声明 _checkPermission'
    );

    // 2. 验证两文件源码中均不再残留 _checkPermission 引用
    $installerSource = file_get_contents(dirname(__DIR__) . '/app/admin/libs/module/ModuleInstaller.php');
    $uninstallerSource = file_get_contents(dirname(__DIR__) . '/app/admin/libs/module/ModuleUninstaller.php');
    assertModuleInstallerCheckPermissionCleanup(
        $installerSource !== false && strpos($installerSource, '_checkPermission') === false,
        'ModuleInstaller.php 中不应再包含 _checkPermission'
    );
    assertModuleInstallerCheckPermissionCleanup(
        $uninstallerSource !== false && strpos($uninstallerSource, '_checkPermission') === false,
        'ModuleUninstaller.php 中不应再包含 _checkPermission'
    );

    Config::set([
        'default' => 'admin_module_installer_check_permission_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_module_installer_check_permission_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    // 3. 验证 ModuleInstaller::run 与 ModuleUninstaller::run 正常向下执行，不会触发 Call to undefined method
    $installer = new ModuleInstaller('__nonexistent_test_module__');
    $installRes = $installer->run();
    assertModuleInstallerCheckPermissionCleanup(
        ($installRes['status'] ?? true) === false && ($installRes['msg'] ?? '') === '找不到模块配置文件',
        'ModuleInstaller::run() 应正常进入模块配置检查并返回「找不到模块配置文件」'
    );

    $uninstaller = new ModuleUninstaller('__nonexistent_test_module__');
    $uninstallRes = $uninstaller->run();
    assertModuleInstallerCheckPermissionCleanup(
        ($uninstallRes['status'] ?? false) === true && ($uninstallRes['msg'] ?? '') === '该模块未安装，无需卸载',
        'ModuleUninstaller::run() 应正常进入模块信息检查并返回「该模块未安装，无需卸载」'
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    restore_error_handler();
}

echo "admin_module_installer_check_permission_cleanup_test passed\n";
