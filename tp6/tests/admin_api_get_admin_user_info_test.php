<?php

use app\admin\controller\AdminApi;
use app\admin\service\AdminUserService;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;

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
function assertAdminApiUserInfo(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 重置测试管理员登录态信息
 *
 * @param array $userInfo 管理员信息
 * @return void
 */
function setTestAdminApiUserInfo(array $userInfo): void
{
    $service = AdminUserService::getInstance();
    $ref = new ReflectionObject($service);
    if ($ref->hasProperty('userInfo')) {
        $prop = $ref->getProperty('userInfo');
        $prop->setAccessible(true);
        if ($prop->isStatic()) {
            $prop->setValue(null, null);
        } else {
            $prop->setValue($service, null);
        }
    }
    Session::set(AdminUserService::KEY_USER_ID, $userInfo['id'] ?? null);
    Session::set(AdminUserService::KEY_USER_INFO, $userInfo ?: null);
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_admin_api_userinfo_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证 AdminApi.php 源码已迁移至 Model，不再直接调用 Db::name
    $source = file_get_contents(dirname(__DIR__) . '/app/admin/controller/AdminApi.php');
    assertAdminApiUserInfo(
        $source !== false
        && strpos($source, 'Db::name(') === false
        && strpos($source, 'use think\facade\Db;') === false
        && strpos($source, 'AdminUserModel::') !== false
        && strpos($source, 'RoleModel::') !== false,
        'AdminApi.php 应改用 AdminUserModel 与 RoleModel，并移除 Db::name( 与 think\facade\Db 导入'
    );

    // 2. 初始化临时缓存与 SQLite 内存数据库
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
        'default' => 'admin_api_get_admin_user_info_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_api_get_admin_user_info_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(20) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0,
        listorder INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        nickname VARCHAR(50) NOT NULL DEFAULT "",
        password VARCHAR(32) NOT NULL DEFAULT "",
        bind_account VARCHAR(50) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
        last_login_ip VARCHAR(40) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(50) NOT NULL DEFAULT "",
        remark VARCHAR(255) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 0,
        role_id INTEGER NOT NULL DEFAULT 0,
        info TEXT NOT NULL DEFAULT "",
        avatar VARCHAR(256) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT ""
    )');

    Db::name('role')->insert([
        'id' => 2,
        'name' => '站点管理员',
        'parentid' => 1,
        'status' => 1,
        'remark' => '站点管理员',
        'create_time' => 1700000000,
        'update_time' => 1700000000,
        'listorder' => 0,
    ]);

    Db::name('user')->insertAll([
        [
            'id' => 101,
            'username' => 'alice',
            'nickname' => 'Alice',
            'password' => 'secret_hash_101',
            'verify' => 'verify_101',
            'email' => 'alice@example.com',
            'status' => 1,
            'role_id' => 2,
            'info' => '',
        ],
        [
            'id' => 102,
            'username' => 'bob_orphan_role',
            'nickname' => 'Bob',
            'password' => 'secret_hash_102',
            'verify' => 'verify_102',
            'email' => 'bob@example.com',
            'status' => 1,
            'role_id' => 999,
            'info' => '',
        ],
    ]);

    $controllerRef = new ReflectionClass(AdminApi::class);
    /** @var AdminApi $controller */
    $controller = $controllerRef->newInstanceWithoutConstructor();

    // 场景 A：管理员与对应角色均存在
    setTestAdminApiUserInfo([
        'id' => 101,
        'username' => 'alice',
        'role_id' => 2,
    ]);
    $resNormal = $controller->getAdminUserInfo()->getData();
    assertAdminApiUserInfo(
        ($resNormal['status'] ?? false) === true && is_array($resNormal['data'] ?? null),
        '场景 A 应返回 status=true 且 data 为数组'
    );
    assertAdminApiUserInfo(
        ($resNormal['data']['username'] ?? '') === 'alice'
        && ($resNormal['data']['role_name'] ?? '') === '站点管理员',
        '场景 A 应返回正确的 username 与 role_name'
    );
    assertAdminApiUserInfo(
        !array_key_exists('password', $resNormal['data']) && !array_key_exists('verify', $resNormal['data']),
        '场景 A 返回数据不得暴露 password 与 verify 敏感字段'
    );

    // 场景 B：管理员存在但角色不存在
    setTestAdminApiUserInfo([
        'id' => 102,
        'username' => 'bob_orphan_role',
        'role_id' => 999,
    ]);
    $resMissingRole = $controller->getAdminUserInfo()->getData();
    assertAdminApiUserInfo(
        ($resMissingRole['status'] ?? false) === true
        && is_array($resMissingRole['data'] ?? null)
        && ($resMissingRole['data']['username'] ?? '') === 'bob_orphan_role'
        && array_key_exists('role_name', $resMissingRole['data'])
        && $resMissingRole['data']['role_name'] === '',
        '场景 B 角色缺失时应返回 role_name 为空字符串且不报错'
    );
    assertAdminApiUserInfo(
        !array_key_exists('password', $resMissingRole['data']) && !array_key_exists('verify', $resMissingRole['data']),
        '场景 B 返回数据不得暴露 password 与 verify 敏感字段'
    );

    // 场景 C：管理员不存在
    setTestAdminApiUserInfo([
        'id' => 9999,
        'username' => 'missing_user',
        'role_id' => 0,
    ]);
    $resMissingUser = $controller->getAdminUserInfo()->getData();
    assertAdminApiUserInfo(
        ($resMissingUser['status'] ?? false) === true && ($resMissingUser['data'] ?? null) === [],
        '场景 C 管理员不存在时应返回 status=true 且 data 为空数组 []'
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    setTestAdminApiUserInfo([]);
    Session::delete(AdminUserService::KEY_USER_ID);
    Session::delete(AdminUserService::KEY_USER_INFO);
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

echo "admin_api_get_admin_user_info_test passed\n";
