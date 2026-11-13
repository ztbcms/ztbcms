<?php

use app\admin\model\AccessModel;
use app\admin\service\AccessService;
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
function assertAccessModelCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_access_model_cleanup_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证 AccessModel::getAccessList 已移除
    $ref = new ReflectionClass(AccessModel::class);
    assertAccessModelCleanup(
        !$ref->hasMethod('getAccessList'),
        'AccessModel::getAccessList 应已移除'
    );

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
        'default' => 'admin_access_model_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_access_model_cleanup_test' => [
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
        ['id' => 2, 'name' => '普通管理员', 'parentid' => 1, 'status' => 1],
    ]);

    Db::name('menu')->insertAll([
        ['id' => 10, 'name' => '日志查看', 'parentid' => 0, 'app' => 'Admin', 'controller' => 'Logs', 'action' => 'index', 'type' => 1],
        ['id' => 11, 'name' => '消息中心', 'parentid' => 0, 'app' => 'Admin', 'controller' => 'AdminMessage', 'action' => 'index', 'type' => 0],
    ]);

    // 2. 验证 RbacService::authorizeRoleAccess 通过 AccessModel 正常写入权限
    $rbacService = new RbacService();
    $authRes = $rbacService->authorizeRoleAccess(2, [10, 11]);
    assertAccessModelCleanup(
        ($authRes['status'] ?? false) === true,
        'RbacService::authorizeRoleAccess 应成功写入权限'
    );

    // 3. 验证 AccessService::getAccessListByRoleId 通过 AccessModel 正常读取权限
    $accessService = new AccessService();
    $listRes = $accessService->getAccessListByRoleId(2);
    assertAccessModelCleanup(
        ($listRes['status'] ?? false) === true && is_array($listRes['data']) && count($listRes['data']) === 2,
        'AccessService::getAccessListByRoleId(2) 应返回 2 条权限记录'
    );
    assertAccessModelCleanup(
        ($listRes['data'][0]['controller'] ?? '') === 'Logs' && ($listRes['data'][1]['controller'] ?? '') === 'AdminMessage11',
        'AccessService::getAccessListByRoleId(2) 返回的控制器字段应正确'
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

echo "admin_access_model_cleanup_test passed\n";
