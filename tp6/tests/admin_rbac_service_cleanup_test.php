<?php

use app\admin\service\RbacService;
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
function assertRbacServiceCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_rbac_service_cleanup_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证 RbacService::getUserAccessList 已移除且核心方法存在
    $ref = new ReflectionClass(RbacService::class);
    assertRbacServiceCleanup(
        !$ref->hasMethod('getUserAccessList'),
        'RbacService::getUserAccessList 应已移除'
    );
    foreach (['getRoleAccessList', 'enableUserAccess', 'enableRoleAccess', 'authorizeRoleAccess'] as $method) {
        assertRbacServiceCleanup(
            $ref->hasMethod($method),
            "RbacService::{$method} 应保留"
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
        'default' => 'admin_rbac_service_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_rbac_service_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

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

    Db::execute('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL,
        nickname VARCHAR(64) NOT NULL,
        password VARCHAR(32) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(128) NOT NULL DEFAULT "",
        avatar VARCHAR(255) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT "",
        role_id INTEGER NOT NULL,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        last_login_ip VARCHAR(64) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
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

    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1],
        ['id' => 2, 'name' => '编辑人员', 'parentid' => 1, 'status' => 1],
    ]);

    Db::name('user')->insertAll([
        ['id' => 1, 'username' => 'admin', 'nickname' => '超管', 'role_id' => 1, 'status' => 1],
        ['id' => 2, 'username' => 'editor', 'nickname' => '编辑', 'role_id' => 2, 'status' => 1],
    ]);

    Db::name('menu')->insert([
        'id' => 20,
        'name' => '日志查看',
        'parentid' => 0,
        'app' => 'Admin',
        'controller' => 'Logs',
        'action' => 'index',
        'type' => 1,
    ]);

    $rbacService = new RbacService();

    // 2. 授权菜单并验证 getRoleAccessList / enableUserAccess
    $authRes = $rbacService->authorizeRoleAccess(2, [20]);
    assertRbacServiceCleanup(($authRes['status'] ?? false) === true, 'authorizeRoleAccess 应成功');

    $roleAccessRes = $rbacService->getRoleAccessList(2);
    assertRbacServiceCleanup(
        ($roleAccessRes['status'] ?? false) === true && isset($roleAccessRes['data']['admin']['logs']['index']),
        'getRoleAccessList(2) 应包含 admin.logs.index 权限'
    );

    $allowRes = $rbacService->enableUserAccess(2, 'Admin', 'Logs', 'index');
    assertRbacServiceCleanup(($allowRes['status'] ?? false) === true, '用户 2 对已授权接口应鉴权通过');

    $denyRes = $rbacService->enableUserAccess(2, 'Admin', 'Role', 'index');
    assertRbacServiceCleanup(($denyRes['status'] ?? true) === false, '用户 2 对未授权接口应鉴权拒绝');
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

echo "admin_rbac_service_cleanup_test passed\n";
