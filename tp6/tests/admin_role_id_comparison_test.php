<?php

use app\admin\controller\AdminManager;
use app\admin\controller\Role;
use app\admin\service\AccessService;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\admin\service\RoleService;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\Request;

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
function assertRoleIdComparison(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 切换测试登录管理员会话并清理静态缓存
 *
 * @param array|null $userInfo 登录用户数组或 null
 * @return void
 */
function setTestAdminUserInfo(?array $userInfo): void
{
    $ref = new ReflectionClass(AdminUserService::class);
    $prop = $ref->getProperty('userInfo');
    $prop->setAccessible(true);
    $prop->setValue(null, []);

    if ($userInfo === null) {
        Session::delete(AdminUserService::KEY_USER_INFO);
        return;
    }

    Session::set(AdminUserService::KEY_USER_INFO, $userInfo);
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_role_id_cmp_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
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
        'default' => 'admin_role_id_comparison_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_role_id_comparison_test' => [
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

    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1],
        ['id' => 2, 'name' => '运营总监', 'parentid' => 1, 'status' => 1],
        ['id' => 3, 'name' => '运营专员', 'parentid' => 2, 'status' => 1],
    ]);

    Db::name('user')->insertAll([
        ['id' => 1, 'username' => 'super', 'nickname' => '超管', 'role_id' => 1, 'status' => 1],
        ['id' => 2, 'username' => 'director', 'nickname' => '总监', 'role_id' => 2, 'status' => 1],
        ['id' => 3, 'username' => 'staff', 'nickname' => '专员', 'role_id' => 3, 'status' => 1],
    ]);

    // 1. RbacService::enableRoleAccess：传入字符串 '1' 且 ignore_unlimit_check=true 时超管也必须直接通过
    $rbacService = new RbacService();
    $rbacRes = $rbacService->enableRoleAccess('1', 'Admin', 'Menu', 'index', true);
    assertRoleIdComparison(
        ($rbacRes['status'] ?? false) === true,
        'RbacService::enableRoleAccess 传入字符串角色 ID "1" 时超管应直接通过鉴权'
    );

    // 2. Role::roleAdd / roleAddEdit：字符串与整型角色 ID 混用判断
    /** @var Role $roleController */
    $roleController = (new ReflectionClass(Role::class))->newInstanceWithoutConstructor();

    // 2a. 编辑超管角色 id='1' 必须被拦截
    setTestAdminUserInfo(['id' => 1, 'role_id' => '1']);
    $reqEditSuper = new Request();
    $reqEditSuper->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
        'id' => '1',
        'name' => '篡改超管',
        'parentid' => '0',
        'status' => '1',
    ]);
    $app->instance('request', $reqEditSuper);
    $editSuperRes = $roleController->roleAdd()->getData();
    assertRoleIdComparison(
        ($editSuperRes['status'] ?? true) === false && ($editSuperRes['msg'] ?? '') === '超级管理员角色不能被修改',
        '编辑超管角色时应拦截并返回「超级管理员角色不能被修改」'
    );

    // 2b. 超管登录态 role_id='1'（字符串）新增顶级角色（parentid='0'）时应保持 parentid=0
    setTestAdminUserInfo(['id' => 1, 'role_id' => '1']);
    $reqAddTop = new Request();
    $reqAddTop->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
        'id' => '',
        'name' => '顶级部门',
        'parentid' => '0',
        'status' => '1',
    ]);
    $app->instance('request', $reqAddTop);
    $addTopRes = $roleController->roleAdd()->getData();
    assertRoleIdComparison(($addTopRes['status'] ?? false) === true, '超管新增顶级角色应成功');
    $topRole = Db::name('role')->where('name', '顶级部门')->find();
    assertRoleIdComparison(
        (int) ($topRole['parentid'] ?? -1) === 0,
        '超管 role_id="1" 新增 parentid="0" 角色时 parentid 应保持 0，实际为 ' . ($topRole['parentid'] ?? 'null')
    );

    // 2c. 非超管 role_id='2' 新增未指定父级的角色时，parentid 应自动归属为 2
    setTestAdminUserInfo(['id' => 2, 'role_id' => '2']);
    $reqAddSub = new Request();
    $reqAddSub->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
        'id' => '',
        'name' => '下级组员',
        'parentid' => '0',
        'status' => '1',
    ]);
    $app->instance('request', $reqAddSub);
    $addSubRes = $roleController->roleAdd()->getData();
    assertRoleIdComparison(($addSubRes['status'] ?? false) === true, '非超管新增角色应成功');
    $subRole = Db::name('role')->where('name', '下级组员')->find();
    assertRoleIdComparison(
        (int) ($subRole['parentid'] ?? 0) === 2,
        '非超管 role_id="2" 新增未指定父级角色时 parentid 应设为 2'
    );

    // 3. AdminManager::getManagerList：超管登录态 role_id='1' 查看指定 role_id='1' 或 '2' 应正常返回列表
    /** @var AdminManager $adminManagerController */
    $adminManagerController = (new ReflectionClass(AdminManager::class))->newInstanceWithoutConstructor();
    setTestAdminUserInfo(['id' => 1, 'role_id' => '1']);

    $reqMgrSelfRole = new Request();
    $reqMgrSelfRole->withServer(['REQUEST_METHOD' => 'GET'])->withGet(['role_id' => '1']);
    $app->instance('request', $reqMgrSelfRole);
    $mgrSelfRes = $adminManagerController->getManagerList()->getData();
    assertRoleIdComparison(
        ($mgrSelfRes['status'] ?? false) === true && is_countable($mgrSelfRes['data']) && count($mgrSelfRes['data']) === 1,
        '超管 role_id="1" 查询指定 role_id="1" 管理员列表时应返回记录，实际 msg=' . ($mgrSelfRes['msg'] ?? '')
    );

    $reqMgrChildRole = new Request();
    $reqMgrChildRole->withServer(['REQUEST_METHOD' => 'GET'])->withGet(['role_id' => '2']);
    $app->instance('request', $reqMgrChildRole);
    $mgrChildRes = $adminManagerController->getManagerList()->getData();
    assertRoleIdComparison(
        ($mgrChildRes['status'] ?? false) === true && is_countable($mgrChildRes['data']) && count($mgrChildRes['data']) === 1,
        '超管 role_id="1" 查询指定 role_id="2" 管理员列表时应返回记录'
    );

    // 4. RoleService::compareRoleLevel / deleteRole / AdminUserService::isAdministrator / AccessService
    $roleService = new RoleService();
    $sameLevelRes = $roleService->compareRoleLevel('2', 2);
    assertRoleIdComparison(
        ($sameLevelRes['status'] ?? false) === true && ($sameLevelRes['data'] ?? -99) === RoleService::LEVEL_COMPARE_SAME,
        'RoleService::compareRoleLevel("2", 2) 应返回 LEVEL_COMPARE_SAME'
    );

    setTestAdminUserInfo(['id' => 1, 'role_id' => '1']);
    assertRoleIdComparison(
        AdminUserService::getInstance()->isAdministrator() === true,
        'AdminUserService::isAdministrator() 在 role_id="1" 时应返回 true'
    );

    $accessRes = (new AccessService())->getAccessListByRoleId('1');
    assertRoleIdComparison(
        ($accessRes['status'] ?? false) === true && (($accessRes['data'][0]['app'] ?? '') === '%'),
        'AccessService::getAccessListByRoleId("1") 应返回超管通配权限'
    );
} finally {
    setTestAdminUserInfo(null);
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

echo "admin_role_id_comparison_test passed\n";
