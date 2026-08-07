<?php
/**
 * admin 模块命令行配置
 */

return [
    // 模块管理
    'module:install'   => 'app\admin\command\ModuleInstallCommand',
    'module:uninstall' => 'app\admin\command\ModuleUninstallCommand',
    'module:list'      => 'app\admin\command\ModuleListCommand',
];
