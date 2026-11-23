<?php

use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\Request;
use app\admin\controller\AdminManager;
use app\admin\controller\Role;
use app\admin\service\AdminManagerService;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\admin\service\RoleService;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/TestCacheHelper.php';

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
function assertAdminPrivilegeTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

/**
 * 断言两个值严格相等
 *
 * @param mixed $expected 预期值
 * @param mixed $actual 实际值
 * @param string $message 失败提示
 * @return void
 */
function assertAdminPrivilegeSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 切换当前登录管理员会话并验证获取结果
 *
 * @param array $userInfo 管理员会话信息
 * @return void
 */
function setAdminPrivilegeOperator(array $userInfo): void
{
    Session::set(AdminUserService::KEY_USER_INFO, $userInfo);
    assertAdminPrivilegeSame(
        (int) $userInfo['id'],
        (int) AdminUserService::getInstance()->getInfo()['id'],
        '切换当前操作者 Session 失败'
    );
}

/**
 * 绑定独立的模拟 HTTP 请求实例
 *
 * @param App $app 应用实例
 * @param string $method 请求方法
 * @param array $get GET 参数
 * @param array $post POST 参数
 * @return void
 */
function setAdminPrivilegeRequest(App $app, string $method, array $get = [], array $post = []): void
{
    $request = new Request();
    $request->withServer(['REQUEST_METHOD' => strtoupper($method)]);
    $request->setMethod($method);
    $request->withGet($get);
    $request->withPost($post);
    $app->instance('request', $request);
}

/**
 * 初始化角色树、管理员账号、菜单与权限测试数据
 *
 * @return void
 */
function seedAdminPrivilegeFixture(): void
{
    Db::execute('DELETE FROM cms_role');
    Db::execute('DELETE FROM cms_user');
    Db::execute('DELETE FROM cms_menu');
    Db::execute('DELETE FROM cms_access');

    $now = time() - 100;
    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
        ['id' => 2, 'name' => '站点管理员', 'parentid' => 1, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
        ['id' => 3, 'name' => '发布人员', 'parentid' => 2, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
        ['id' => 4, 'name' => '审核专员', 'parentid' => 3, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
        ['id' => 5, 'name' => '财务主管', 'parentid' => 1, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
        ['id' => 6, 'name' => '财务出纳', 'parentid' => 5, 'status' => 1, 'remark' => '', 'create_time' => $now, 'update_time' => $now, 'listorder' => 0],
    ]);

    Db::name('user')->insertAll([
        ['id' => 1, 'username' => 'admin', 'nickname' => '超管', 'password' => 'hash1', 'verify' => 'aaaaaa', 'email' => 'admin@test.com', 'role_id' => 1, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 2, 'username' => 'site_admin', 'nickname' => '站点管理员', 'password' => 'hash2', 'verify' => 'bbbbbb', 'email' => 'site@test.com', 'role_id' => 2, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 3, 'username' => 'publisher', 'nickname' => '发布员', 'password' => 'hash3', 'verify' => 'cccccc', 'email' => 'pub@test.com', 'role_id' => 3, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 4, 'username' => 'reviewer', 'nickname' => '审核员', 'password' => 'hash4', 'verify' => 'dddddd', 'email' => 'rev@test.com', 'role_id' => 4, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 5, 'username' => 'finance_lead', 'nickname' => '财务主管', 'password' => 'hash5', 'verify' => 'eeeeee', 'email' => 'fin@test.com', 'role_id' => 5, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 6, 'username' => 'finance_staff', 'nickname' => '财务出纳', 'password' => 'hash6', 'verify' => 'ffffff', 'email' => 'staff@test.com', 'role_id' => 6, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
        ['id' => 7, 'username' => 'super2', 'nickname' => '二号超管', 'password' => 'hash7', 'verify' => 'gggggg', 'email' => 'super2@test.com', 'role_id' => 1, 'status' => 1, 'create_time' => $now, 'update_time' => $now, 'info' => '', 'avatar' => '', 'phone' => ''],
    ]);

    Db::name('menu')->insertAll([
        ['id' => 10, 'name' => '管理员列表', 'parentid' => 0, 'app' => 'admin', 'controller' => 'AdminManager', 'action' => 'index', 'type' => 1, 'status' => 1, 'listorder' => 1],
        ['id' => 11, 'name' => '新增管理员', 'parentid' => 10, 'app' => 'admin', 'controller' => 'AdminManager', 'action' => 'managerAdd', 'type' => 1, 'status' => 0, 'listorder' => 2],
        ['id' => 12, 'name' => '系统高危授权', 'parentid' => 0, 'app' => 'admin', 'controller' => 'Role', 'action' => 'authorize', 'type' => 1, 'status' => 1, 'listorder' => 3],
        ['id' => 13, 'name' => '无权限验证菜单', 'parentid' => 0, 'app' => 'admin', 'controller' => 'Menu', 'action' => 'index', 'type' => 0, 'status' => 1, 'listorder' => 4],
    ]);

    Db::name('access')->insertAll([
        ['role_id' => 2, 'app' => 'admin', 'controller' => 'AdminManager', 'action' => 'index', 'status' => 1],
        ['role_id' => 2, 'app' => 'admin', 'controller' => 'AdminManager', 'action' => 'managerAdd', 'status' => 1],
        ['role_id' => 2, 'app' => 'admin', 'controller' => 'Menu13', 'action' => 'index13', 'status' => 1],
    ]);

    Cache::tag(RbacService::CacheTagName)->clear();
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_admin_privilege_test_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    Config::set([
        'default' => 'admin_priv_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_priv_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => 'cms_',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    configureIsolatedTestFileCache($tmpCacheDir, ['prefix' => 'test_']);

    Db::execute('CREATE TABLE cms_role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(20) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0,
        listorder INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE cms_user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        nickname VARCHAR(50) NOT NULL DEFAULT "",
        password CHAR(32) NOT NULL DEFAULT "",
        bind_account VARCHAR(50) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
        last_login_ip VARCHAR(40) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(50) NOT NULL DEFAULT "",
        remark VARCHAR(255) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        role_id INTEGER NOT NULL DEFAULT 0,
        info TEXT NOT NULL DEFAULT "",
        avatar VARCHAR(256) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE cms_menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        app VARCHAR(255) NOT NULL DEFAULT "",
        controller VARCHAR(255) NOT NULL DEFAULT "",
        action VARCHAR(255) NOT NULL DEFAULT "",
        parameter VARCHAR(255) NOT NULL DEFAULT "",
        type INTEGER NOT NULL DEFAULT 1,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        icon VARCHAR(255) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE cms_access (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role_id INTEGER NOT NULL DEFAULT 0,
        app VARCHAR(255) NOT NULL DEFAULT "",
        controller VARCHAR(255) NOT NULL DEFAULT "",
        action VARCHAR(255) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 1
    )');

    Db::execute('CREATE TABLE cms_config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(50) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL DEFAULT ""
    )');

    seedAdminPrivilegeFixture();

    $adminManagerService = new AdminManagerService();
    $roleService = new RoleService();
    $rbacService = new RbacService();

    $superOperator = ['id' => 1, 'username' => 'admin', 'role_id' => 1];
    $siteOperator = ['id' => 2, 'username' => 'site_admin', 'role_id' => 2];

    // ==================== 1. AdminUserService Session 刷新测试 ====================
    setAdminPrivilegeOperator($superOperator);
    setAdminPrivilegeOperator($siteOperator);
    AdminUserService::getInstance()->logout();
    assertAdminPrivilegeSame(null, AdminUserService::getInstance()->getInfo(), 'logout 后 getInfo 应返回 null');

    // ==================== 2. 管理员写操作层级校验（非超管 role_id=2） ====================
    setAdminPrivilegeOperator($siteOperator);

    // 2.1 新增超管 / 平级 / 其他分支 / 不存在角色 -> 拒绝
    foreach ([1, 2, 5, 6, 999] as $forbiddenRoleId) {
        $res = $adminManagerService->addOrEditAdminManager([
            'username' => 'bad_user_' . $forbiddenRoleId,
            'nickname' => '非法用户',
            'email' => 'bad_' . $forbiddenRoleId . '@test.com',
            'password' => '123456',
            'role_id' => $forbiddenRoleId,
            'status' => 1,
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能新建 role_id={$forbiddenRoleId} 的管理员");
    }

    // 2.2 新增下级角色（role_id=3 和 孙级 role_id=4）-> 成功
    $res = $adminManagerService->addOrEditAdminManager([
        'username' => 'new_pub',
        'nickname' => '新发布员',
        'email' => 'new_pub@test.com',
        'password' => '123456',
        'role_id' => 3,
        'status' => 1,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管应能新建下级角色(role_id=3)管理员: ' . $res['msg']);

    $res = $adminManagerService->addOrEditAdminManager([
        'username' => 'new_rev',
        'nickname' => '新审核员',
        'email' => 'new_rev@test.com',
        'password' => '123456',
        'role_id' => 4,
        'status' => 1,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管应能新建孙级角色(role_id=4)管理员: ' . $res['msg']);

    // 2.3 编辑超管(id=1, id=7)、平级自己(id=2)、其他分支(id=5, id=6) -> 拒绝
    foreach ([1, 2, 5, 6, 7] as $forbiddenUserId) {
        $res = $adminManagerService->addOrEditAdminManager([
            'id' => $forbiddenUserId,
            'nickname' => '被篡改',
            'password' => 'hacked123',
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能编辑 id={$forbiddenUserId} 的管理员");
    }
    assertAdminPrivilegeSame('hash1', Db::name('user')->where('id', 1)->value('password'), '超管密码不应被篡改');

    // 2.4 编辑下级管理员(id=3)并试图把其 role_id 改为 1/2/5/6/0 -> 拒绝
    foreach ([0, 1, 2, 5, 6] as $targetRoleId) {
        $res = $adminManagerService->addOrEditAdminManager([
            'id' => 3,
            'nickname' => '发布员改角色',
            'role_id' => $targetRoleId,
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能将下级管理员改为 role_id={$targetRoleId}");
    }
    assertAdminPrivilegeSame(3, (int) Db::name('user')->where('id', 3)->value('role_id'), '下级管理员角色不应被篡改');

    // 2.5 正常编辑下级管理员（保持或降级到 role_id=4）-> 成功
    $res = $adminManagerService->addOrEditAdminManager([
        'id' => 3,
        'nickname' => '发布员已更新',
        'email' => 'pub@test.com',
        'role_id' => 4,
        'status' => 1,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管应能正常编辑下级管理员: ' . $res['msg']);
    assertAdminPrivilegeSame(4, (int) Db::name('user')->where('id', 3)->value('role_id'), '下级管理员角色应更新为 4');

    // 2.6 删除超管/平级/自己/其他分支 -> 拒绝
    foreach ([1, 2, 5, 6, 7] as $forbiddenDelId) {
        $res = $adminManagerService->deleteAdminManager($forbiddenDelId);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能删除管理员 id={$forbiddenDelId}");
    }

    // 2.7 删除下级管理员(id=4) -> 成功
    $res = $adminManagerService->deleteAdminManager(4);
    assertAdminPrivilegeSame(true, $res['status'], '非超管应能删除下级管理员: ' . $res['msg']);

    // 2.8 个人基本资料更新 updateBasicInfo -> 成功且忽略 role_id / password 等字段
    $res = $adminManagerService->updateBasicInfo(2, [
        'nickname' => '新站点昵称',
        'email' => 'site_new@test.com',
        'remark' => '个人备注',
        'role_id' => 1,
        'password' => 'hack',
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '本人 updateBasicInfo 应成功: ' . $res['msg']);
    $user2 = Db::name('user')->where('id', 2)->find();
    assertAdminPrivilegeSame('新站点昵称', $user2['nickname'], 'nickname 应已更新');
    assertAdminPrivilegeSame(2, (int) $user2['role_id'], 'updateBasicInfo 不应修改 role_id');
    assertAdminPrivilegeSame('hash2', $user2['password'], 'updateBasicInfo 不应修改 password');

    // 2.9 超级管理员对管理员的正常边界测试
    setAdminPrivilegeOperator($superOperator);
    $res = $adminManagerService->addOrEditAdminManager([
        'id' => 1,
        'role_id' => 2,
    ]);
    assertAdminPrivilegeSame(false, $res['status'], '初始超管角色不能被修改为非超管');

    $res = $adminManagerService->addOrEditAdminManager([
        'username' => 'super3',
        'nickname' => '三号超管',
        'email' => 'super3@test.com',
        'password' => '123456',
        'role_id' => 1,
        'status' => 1,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '超管应能新建超管角色管理员: ' . $res['msg']);

    $res = $adminManagerService->deleteAdminManager(6);
    assertAdminPrivilegeSame(true, $res['status'], '超管应能删除普通管理员: ' . $res['msg']);

    // ==================== 3. 角色授权 / 编辑 / 删除与权限子集校验 ====================
    seedAdminPrivilegeFixture();
    setAdminPrivilegeOperator($siteOperator);

    // 3.1 角色授权越权：给自己(2)、超管(1)、其他分支(5, 6)授权 -> 拒绝
    foreach ([1, 2, 5, 6] as $forbiddenTargetRole) {
        $res = $rbacService->authorizeRoleAccess($forbiddenTargetRole, [10, 11]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能给 role_id={$forbiddenTargetRole} 授权");
    }

    // 3.2 角色授权越权：给下级角色(3)授予自身没有的菜单(12)或不存在的菜单(999) -> 拒绝
    $res = $rbacService->authorizeRoleAccess(3, [10, 12]);
    assertAdminPrivilegeSame(false, $res['status'], '非超管不应能授予超出自身权限范围的菜单(12)');

    $res = $rbacService->authorizeRoleAccess(3, [10, 999]);
    assertAdminPrivilegeSame(false, $res['status'], '不应能授予不存在的菜单(999)');

    // 3.3 角色授权合法：给下级角色(3)授予自身已有菜单子集(10, 13) -> 成功
    $res = $rbacService->authorizeRoleAccess(3, [10, 13]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管应能给下级角色授予自身权限子集: ' . $res['msg']);
    $role3AccessCount = Db::name('access')->where('role_id', 3)->count();
    assertAdminPrivilegeSame(2, (int) $role3AccessCount, 'role_id=3 应写入 2 条权限');

    // 3.4 角色编辑/删除越权：编辑或删除 1(超管)、2(自己)、5/6(其他分支) -> 拒绝
    foreach ([1, 2, 5, 6] as $forbiddenRoleId) {
        $res = $roleService->addOrEditRole([
            'id' => $forbiddenRoleId,
            'name' => '越权修改',
            'remark' => '',
            'status' => 1,
            'parentid' => 2,
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管不应能编辑 role_id={$forbiddenRoleId}");

        $delRes = $roleService->deleteRole($forbiddenRoleId);
        assertAdminPrivilegeSame(false, $delRes['status'], "非超管不应能删除 role_id={$forbiddenRoleId}");
    }

    // 3.5 新增/编辑角色时 parentid 越权或成环 -> 拒绝
    foreach ([1, 5, 6, 999] as $badParentId) {
        $res = $roleService->addOrEditRole([
            'name' => '越权子角色',
            'remark' => '',
            'status' => 1,
            'parentid' => $badParentId,
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管新增角色不应能挂到 parentid={$badParentId}");
    }
    foreach ([1, 3, 4, 5] as $badEditParentId) {
        $res = $roleService->addOrEditRole([
            'id' => 3,
            'name' => '发布人员改父级',
            'remark' => '',
            'status' => 1,
            'parentid' => $badEditParentId,
        ]);
        assertAdminPrivilegeSame(false, $res['status'], "非超管编辑 role_id=3 时不应能将 parentid 设为 {$badEditParentId}");
    }

    // 3.6 正常新增、编辑、删除下级角色 -> 成功
    $res = $roleService->addOrEditRole([
        'name' => '编辑助理',
        'remark' => '测试',
        'status' => 1,
        'parentid' => 0,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管新增下级角色应成功: ' . $res['msg']);
    $newRole = Db::name('role')->where('name', '编辑助理')->find();
    assertAdminPrivilegeTrue(!empty($newRole), '应已插入新角色');
    assertAdminPrivilegeSame(2, (int) $newRole['parentid'], '非超管新增角色 parentid=0 应自动设为操作者角色ID 2');

    $res = $roleService->addOrEditRole([
        'id' => $newRole['id'],
        'name' => '编辑助理二组',
        'remark' => '测试更新',
        'status' => 1,
        'parentid' => 3,
    ]);
    assertAdminPrivilegeSame(true, $res['status'], '非超管编辑下级角色并挂到子角色 3 下应成功: ' . $res['msg']);
    assertAdminPrivilegeSame(3, (int) Db::name('role')->where('id', $newRole['id'])->value('parentid'), 'parentid 应更新为 3');

    $res = $roleService->deleteRole($newRole['id']);
    assertAdminPrivilegeSame(true, $res['status'], '非超管删除下级空角色应成功: ' . $res['msg']);

    // ==================== 4. 控制器查询详情越权与授权 JSON 返回校验 ====================
    setAdminPrivilegeRequest($app, 'GET', ['_action' => 'getDetail', 'id' => 1]);
    $adminController = new AdminManager($app);
    $resp = $adminController->managerEdit()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], '非超管通过 AdminManager::managerEdit getDetail 查看超管详情应被拒绝');

    setAdminPrivilegeRequest($app, 'GET', ['_action' => 'getDetail', 'id' => 3]);
    $adminController = new AdminManager($app);
    $resp = $adminController->managerEdit()->getData();
    assertAdminPrivilegeSame(true, $resp['status'], '非超管通过 AdminManager::managerEdit getDetail 查看下级详情应成功');

    // AdminManager::myBasicsInfo POST 修改自己基本资料，试图携带 role_id 提权应无效
    setAdminPrivilegeRequest($app, 'POST', [], [
        'nickname' => '控制器改昵称',
        'email' => 'ctrl@test.com',
        'remark' => '控制器备注',
        'role_id' => 1,
    ]);
    $adminController = new AdminManager($app);
    $resp = $adminController->myBasicsInfo()->getData();
    assertAdminPrivilegeSame(true, $resp['status'], '非超管通过 AdminManager::myBasicsInfo 更新基本资料应成功');
    assertAdminPrivilegeSame(2, (int) Db::name('user')->where('id', 2)->value('role_id'), 'myBasicsInfo 不应被注入修改 role_id');

    setAdminPrivilegeRequest($app, 'GET', ['_action' => 'getDetail', 'id' => 2]);
    $roleController = new Role($app);
    $resp = $roleController->roleEdit()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], '非超管通过 Role::roleEdit getDetail 查看自身角色详情应被拒绝');

    setAdminPrivilegeRequest($app, 'GET', ['_action' => 'getDetail', 'id' => 3]);
    $roleController = new Role($app);
    $resp = $roleController->roleEdit()->getData();
    assertAdminPrivilegeSame(true, $resp['status'], '非超管通过 Role::roleEdit getDetail 查看下级角色详情应成功');

    setAdminPrivilegeRequest($app, 'GET', ['id' => 2]);
    $roleController = new Role($app);
    $resp = $roleController->getAuthorizeList()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], '非超管通过 Role::getAuthorizeList 查看自身角色授权树应被拒绝');

    setAdminPrivilegeRequest($app, 'GET', ['id' => 3]);
    $roleController = new Role($app);
    $resp = $roleController->getAuthorizeList()->getData();
    assertAdminPrivilegeSame(true, $resp['status'], '非超管通过 Role::getAuthorizeList 查看下级角色授权树应成功');

    // Role::authorize POST 应返回 Json 响应对象且拒绝越权
    setAdminPrivilegeRequest($app, 'POST', [], ['roleid' => 2, 'menuid' => '10,11']);
    $roleController = new Role($app);
    $jsonResp = $roleController->authorize();
    assertAdminPrivilegeTrue($jsonResp instanceof \think\response\Json, 'Role::authorize 应返回 Json 响应对象');
    assertAdminPrivilegeSame(false, $jsonResp->getData()['status'], 'Role::authorize 应拒绝给自己授权');
    assertAdminPrivilegeSame('当前登录用户无权限操作', $jsonResp->getData()['msg'], 'Role::authorize 应因层级校验拒绝给自己授权');

    setAdminPrivilegeRequest($app, 'POST', [], ['roleid' => 3, 'menuid' => '10,12']);
    $roleController = new Role($app);
    $jsonResp = $roleController->authorize();
    assertAdminPrivilegeTrue($jsonResp instanceof \think\response\Json, 'Role::authorize 应返回 Json 响应对象');
    assertAdminPrivilegeSame(false, $jsonResp->getData()['status'], 'Role::authorize 应拒绝越权授予超出当前角色的菜单');
    assertAdminPrivilegeSame('不能授予超出当前角色权限范围的菜单', $jsonResp->getData()['msg'], 'Role::authorize 应提示权限子集校验失败');

    setAdminPrivilegeRequest($app, 'POST', [], ['roleid' => 3, 'menuid' => '10,11,']);
    $roleController = new Role($app);
    $jsonResp = $roleController->authorize();
    assertAdminPrivilegeTrue($jsonResp instanceof \think\response\Json, 'Role::authorize 应返回 Json 响应对象');
    assertAdminPrivilegeSame(true, $jsonResp->getData()['status'], 'Role::authorize 给下级授予合法子集应成功: ' . $jsonResp->getData()['msg']);

    // AdminManager::managerAdd POST 试图越权创建超管应被拒绝
    setAdminPrivilegeRequest($app, 'POST', [], [
        'username' => 'ctrl_bad',
        'nickname' => '非法管理员',
        'email' => 'ctrl_bad@test.com',
        'password' => '123456',
        'pwdconfirm' => '123456',
        'role_id' => 1,
        'status' => 1,
    ]);
    $adminController = new AdminManager($app);
    $resp = $adminController->managerAdd()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], 'AdminManager::managerAdd 应拒绝非超管创建超管账号');

    // AdminManager::managerEdit POST 试图修改超管密码应被拒绝
    setAdminPrivilegeRequest($app, 'POST', [], [
        'id' => 1,
        'username' => 'admin',
        'nickname' => '篡改超管',
        'email' => 'admin@test.com',
        'password' => 'hacked',
        'pwdconfirm' => 'hacked',
        'role_id' => 1,
        'status' => 1,
    ]);
    $adminController = new AdminManager($app);
    $resp = $adminController->managerEdit()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], 'AdminManager::managerEdit 应拒绝非超管编辑超管账号');

    // AdminManager::managerDelete 试图删除同级/其他分支管理员应被拒绝
    setAdminPrivilegeRequest($app, 'POST', [], ['id' => 5]);
    $adminController = new AdminManager($app);
    $deleteResponse = $adminController->managerDelete();
    $resp = $deleteResponse->getData();
    assertAdminPrivilegeSame(false, $resp['status'], 'AdminManager::managerDelete 应拒绝非超管删除平级/其他分支管理员');
    assertAdminPrivilegeSame(400, $resp['code'], 'AdminManager::managerDelete 越权拦截应走业务校验而非 405');
    assertAdminPrivilegeSame(200, $deleteResponse->getCode(), 'AdminManager::managerDelete POST 请求 HTTP 状态码不应为 405');

    // ==================== 5. 已登录账号被降权/禁用后持旧 Session 请求回归测试 ====================
    seedAdminPrivilegeFixture();
    Db::name('access')->insert([
        'role_id' => 2,
        'app' => 'admin',
        'controller' => 'AdminManager',
        'action' => 'managerEdit',
        'status' => 1,
    ]);
    Cache::tag(RbacService::CacheTagName)->clear();

    // 5.1 id=7 原为超管并登录保留旧 Session，之后被初始超管 id=1 降权为 role_id=2
    $staleSuperSession = ['id' => 7, 'username' => 'super2', 'role_id' => 1, 'status' => 1];
    setAdminPrivilegeOperator($superOperator);
    $demoteRes = $adminManagerService->addOrEditAdminManager([
        'id' => 7,
        'role_id' => 2,
    ]);
    assertAdminPrivilegeSame(true, $demoteRes['status'], '初始超管将 id=7 降为 role_id=2 应成功: ' . $demoteRes['msg']);
    assertAdminPrivilegeSame(2, (int) Db::name('user')->where('id', 7)->value('role_id'), '数据库中 id=7 的 role_id 应已降为 2');

    // 5.2 恢复 id=7 降权前留存的旧 Session（role_id=1），确认 role_id=2 仍保有 managerEdit 菜单访问权限
    Session::set(AdminUserService::KEY_USER_INFO, $staleSuperSession);
    $userRef = new ReflectionClass(AdminUserService::class);
    $userProp = $userRef->getProperty('userInfo');
    $userProp->setAccessible(true);
    $userProp->setValue(null, []);
    $accessCheck = $rbacService->enableUserAccess(7, 'admin', 'AdminManager', 'managerEdit');
    assertAdminPrivilegeSame(true, $accessCheck['status'], '降权后 role_id=2 仍保留 managerEdit 接口菜单权限');

    // 5.3 id=7 持旧 Session 通过 managerEdit POST 试图修改初始超管 id=1 的密码 -> 必须拦截
    setAdminPrivilegeRequest($app, 'POST', [], [
        'id' => 1,
        'username' => 'admin',
        'nickname' => '超管',
        'email' => 'admin@test.com',
        'password' => 'hijack123',
        'pwdconfirm' => 'hijack123',
        'role_id' => 1,
        'status' => 1,
    ]);
    $adminController = new AdminManager($app);
    $resp = $adminController->managerEdit()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], '降权后持旧 Session 的 id=7 不应能编辑超管 id=1');
    assertAdminPrivilegeSame('hash1', Db::name('user')->where('id', 1)->value('password'), '超管 id=1 密码不得被已降权账号篡改');

    // 5.4 验证旧 Session 已被实时同步刷新为数据库中的 role_id=2
    $syncedSession = Session::get(AdminUserService::KEY_USER_INFO);
    assertAdminPrivilegeSame(2, (int) ($syncedSession['role_id'] ?? 0), '旧 Session 中的 role_id 应已被同步更新为数据库最新的 2');

    // 5.5 再次模拟陈旧 Session 对管理员详情、角色编辑、角色授权及显式旧操作者参数发起越权请求 -> 全部拦截
    Session::set(AdminUserService::KEY_USER_INFO, $staleSuperSession);
    $userProp->setValue(null, []);

    setAdminPrivilegeRequest($app, 'GET', ['_action' => 'getDetail', 'id' => 1]);
    $resp = (new AdminManager($app))->managerEdit()->getData();
    assertAdminPrivilegeSame(false, $resp['status'], '降权后持旧 Session 不应能查看超管详情');

    $roleRes = $roleService->addOrEditRole([
        'id' => 2,
        'name' => '篡改自身角色',
        'remark' => '',
        'status' => 1,
        'parentid' => 1,
    ]);
    assertAdminPrivilegeSame(false, $roleRes['status'], '降权后持旧 Session 不应能修改自身所属角色 role_id=2');

    $authSelfRes = $rbacService->authorizeRoleAccess(2, [10, 11]);
    assertAdminPrivilegeSame(false, $authSelfRes['status'], '降权后持旧 Session 不应能给自己 role_id=2 授权');

    $authScopeRes = $rbacService->authorizeRoleAccess(3, [10, 12]);
    assertAdminPrivilegeSame(false, $authScopeRes['status'], '降权后持旧 Session 不应能授予超出 role_id=2 权限范围的菜单');

    $explicitStaleRes = $adminManagerService->addOrEditAdminManager([
        'id' => 1,
        'password' => 'hijack456',
    ], $staleSuperSession);
    assertAdminPrivilegeSame(false, $explicitStaleRes['status'], '即使显式传入过期操作者快照，也应按数据库实时角色拦截越权');

    // 5.6 数据库中将 id=7 禁用（status=0）后，持旧 Session 编辑下级管理员 id=3 也必须被拦截
    Db::name('user')->where('id', 7)->update(['status' => 0]);
    Session::set(AdminUserService::KEY_USER_INFO, $staleSuperSession);
    $userProp->setValue(null, []);
    setAdminPrivilegeRequest($app, 'POST', [], [
        'id' => 3,
        'username' => 'publisher',
        'nickname' => '被禁用账号尝试修改',
        'email' => 'pub@test.com',
        'role_id' => 3,
        'status' => 1,
    ]);
    $disabledResp = (new AdminManager($app))->managerEdit()->getData();
    assertAdminPrivilegeSame(false, $disabledResp['status'], '已禁用账号持旧 Session 不应能继续执行写操作');
} finally {
    AdminUserService::getInstance()->logout();
    restore_error_handler();
    if (is_dir($tmpCacheDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpCacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($tmpCacheDir);
    }
}

echo "admin_privilege_escalation_test passed\n";
