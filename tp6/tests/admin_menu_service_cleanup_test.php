<?php

use app\admin\service\MenuService;
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
function assertMenuServiceCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_menu_service_cleanup_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证 MenuService::getMenuTreeArray 已移除，其余方法保留
    $ref = new ReflectionClass(MenuService::class);
    assertMenuServiceCleanup(
        !$ref->hasMethod('getMenuTreeArray'),
        'MenuService::getMenuTreeArray 应已移除'
    );
    foreach (['getMenuList', 'getDetails', 'doDelete', 'addEditDetails', 'getMenuByRole'] as $method) {
        assertMenuServiceCleanup(
            $ref->hasMethod($method),
            "MenuService::{$method} 应保留"
        );
    }

    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $cacheDir,
                'prefix' => '',
                'expire' => 0,
                'tag_prefix' => 'tag:',
                'serialize' => [],
            ],
        ],
    ], 'cache');

    Config::set([
        'default' => 'admin_menu_service_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_menu_service_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(64) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        app VARCHAR(64) NOT NULL DEFAULT "",
        controller VARCHAR(64) NOT NULL DEFAULT "",
        action VARCHAR(64) NOT NULL DEFAULT "",
        parameter VARCHAR(255) NOT NULL DEFAULT "",
        type INTEGER NOT NULL DEFAULT 1,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        icon VARCHAR(64) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(64) NOT NULL,
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE access (
        role_id INTEGER NOT NULL,
        app VARCHAR(64) NOT NULL,
        controller VARCHAR(64) NOT NULL,
        action VARCHAR(64) NOT NULL,
        status INTEGER NOT NULL DEFAULT 1
    )');

    Db::name('menu')->insertAll([
        ['id' => 1, 'name' => '系统设置', 'parentid' => 0, 'app' => 'Admin', 'controller' => 'Setting', 'action' => 'index', 'type' => 0, 'listorder' => 1],
        ['id' => 2, 'name' => '日志管理', 'parentid' => 1, 'app' => 'Admin', 'controller' => 'Logs', 'action' => 'index', 'type' => 1, 'listorder' => 2],
        ['id' => 3, 'name' => '菜单管理', 'parentid' => 1, 'app' => 'Admin', 'controller' => 'Menu', 'action' => 'index', 'type' => 1, 'listorder' => 1],
    ]);

    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1],
        ['id' => 2, 'name' => '运维角色', 'parentid' => 1, 'status' => 1],
    ]);

    Db::name('access')->insert([
        'role_id' => 2,
        'app' => 'Admin',
        'controller' => 'Logs',
        'action' => 'index',
        'status' => 1,
    ]);

    // 2. 验证 getMenuList 正常工作并返回层级列表
    $menuListRes = MenuService::getMenuList();
    assertMenuServiceCleanup(
        ($menuListRes['status'] ?? false) === true && is_array($menuListRes['data']) && count($menuListRes['data']) === 3,
        'MenuService::getMenuList() 应返回 3 条菜单数据'
    );

    // 3. 验证 getMenuByRole 正常工作
    $roleMenuRes = MenuService::getMenuByRole(2);
    assertMenuServiceCleanup(
        ($roleMenuRes['status'] ?? false) === true && count($roleMenuRes['data']) === 1 && (int) $roleMenuRes['data'][0]['id'] === 2,
        'MenuService::getMenuByRole(2) 应仅返回授权的日志管理菜单'
    );
} finally {
    restore_error_handler();
    if (is_dir($cacheDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($cacheDir);
    }
}

echo "admin_menu_service_cleanup_test passed\n";
