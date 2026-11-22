<?php

use app\admin\controller\Login;
use app\admin\model\AdminUserModel;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\common\libs\helper\PasswordHelper;
use app\common\middleware\AdminAuth;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Request;
use think\Response;

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
function assertDisabledAuthTrue(bool $condition, string $message): void
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
function assertDisabledAuthSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 重置 AdminUserService 静态缓存及当前 Session 登录态
 *
 * @return void
 */
function resetAdminUserState(): void
{
    $ref = new ReflectionClass(AdminUserService::class);
    $prop = $ref->getProperty('userInfo');
    $prop->setAccessible(true);
    $prop->setValue(null, []);
    Session::clear();
}

/**
 * 仅清空 AdminUserService 内存静态缓存（模拟新 HTTP 请求进程）
 *
 * @return void
 */
function clearAdminUserStaticCache(): void
{
    $ref = new ReflectionClass(AdminUserService::class);
    $prop = $ref->getProperty('userInfo');
    $prop->setAccessible(true);
    $prop->setValue(null, []);
}

/**
 * 创建测试用 SQLite 内存表结构
 *
 * @return void
 */
function createDisabledAuthTables(): void
{
    Db::execute('CREATE TABLE IF NOT EXISTS config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE IF NOT EXISTS role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(64) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE IF NOT EXISTS user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        nickname VARCHAR(64) NOT NULL DEFAULT "",
        password VARCHAR(64) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(128) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT "",
        avatar VARCHAR(255) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT "",
        role_id INTEGER NOT NULL DEFAULT 1,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        last_login_ip VARCHAR(64) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE IF NOT EXISTS access (
        role_id INTEGER NOT NULL,
        app VARCHAR(64) NOT NULL DEFAULT "",
        controller VARCHAR(64) NOT NULL DEFAULT "",
        action VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 1
    )');

    Db::execute('CREATE TABLE IF NOT EXISTS login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');
}

/**
 * 初始化测试夹具数据
 *
 * @return void
 */
function seedDisabledAuthFixture(): void
{
    createDisabledAuthTables();
    Db::execute('DELETE FROM config');
    Db::execute('DELETE FROM role');
    Db::execute('DELETE FROM user');
    Db::execute('DELETE FROM access');
    Db::execute('DELETE FROM login_log');

    Db::name('config')->insertAll([
        ['varname' => 'sitename', 'info' => '站点名称', 'groupid' => 1, 'value' => 'ZTBCMS Test'],
        ['varname' => 'admin_panel_security_code', 'info' => '后台安全码', 'groupid' => 1, 'value' => ''],
        ['varname' => 'checkcode_type', 'info' => '验证码类型', 'groupid' => 1, 'value' => '0'],
    ]);

    $now = time() - 100;
    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1, 'create_time' => $now, 'update_time' => $now],
        ['id' => 2, 'name' => '普通运营', 'parentid' => 1, 'status' => 1, 'create_time' => $now, 'update_time' => $now],
    ]);

    $verify1 = 'salt01';
    $verify2 = 'salt02';
    $verify3 = 'salt03';
    $verify4 = 'salt04';
    $pwdHash1 = PasswordHelper::hashPassword('AdminPass123!', $verify1);
    $pwdHash2 = PasswordHelper::hashPassword('OperatorPass123!', $verify2);
    $pwdHash3 = PasswordHelper::hashPassword('DisabledPass123!', $verify3);
    $pwdHash4 = PasswordHelper::hashPassword('OtherStatusPass123!', $verify4);

    Db::name('user')->insertAll([
        [
            'id' => 1,
            'username' => 'super_admin',
            'nickname' => '超级管理员',
            'password' => $pwdHash1,
            'verify' => $verify1,
            'email' => 'super@test.com',
            'info' => 'super info',
            'avatar' => '/super.png',
            'phone' => '13800000001',
            'role_id' => 1,
            'status' => AdminUserModel::STATUS_ENABLE,
            'remark' => '超管',
            'last_login_ip' => '',
            'last_login_time' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ],
        [
            'id' => 2,
            'username' => 'operator',
            'nickname' => '旧昵称',
            'password' => $pwdHash2,
            'verify' => $verify2,
            'email' => 'operator@test.com',
            'info' => 'op info',
            'avatar' => '/op.png',
            'phone' => '13800000002',
            'role_id' => 2,
            'status' => AdminUserModel::STATUS_ENABLE,
            'remark' => '运营',
            'last_login_ip' => '',
            'last_login_time' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ],
        [
            'id' => 3,
            'username' => 'disabled_user',
            'nickname' => '禁用账号',
            'password' => $pwdHash3,
            'verify' => $verify3,
            'email' => 'disabled@test.com',
            'info' => '',
            'avatar' => '',
            'phone' => '',
            'role_id' => 2,
            'status' => AdminUserModel::STATUS_DISABLE,
            'remark' => '已禁用',
            'last_login_ip' => '',
            'last_login_time' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ],
        [
            'id' => 4,
            'username' => 'abnormal_user',
            'nickname' => '异常状态账号',
            'password' => $pwdHash4,
            'verify' => $verify4,
            'email' => 'abnormal@test.com',
            'info' => '',
            'avatar' => '',
            'phone' => '',
            'role_id' => 2,
            'status' => 2,
            'remark' => '异常状态',
            'last_login_ip' => '',
            'last_login_time' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ],
    ]);

    Db::name('access')->insert([
        'role_id' => 2,
        'app' => 'admin',
        'controller' => 'AdminManager',
        'action' => 'index',
        'status' => 1,
    ]);

    Cache::tag(RbacService::CacheTagName)->clear();
    Cache::tag(AdminConfigService::CacheTagName)->clear();
}

/**
 * 构造并绑定模拟 HTTP 请求到应用容器
 *
 * @param App $app 应用实例
 * @param array $options 请求选项
 * @return Request
 */
function bindDisabledAuthRequest(App $app, array $options = []): Request
{
    $method = strtoupper($options['method'] ?? 'GET');
    $baseUrl = $options['base_url'] ?? '/admin/AdminManager/index';
    $server = [
        'REQUEST_METHOD' => $method,
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_HOST' => 'localhost',
        'REQUEST_URI' => $baseUrl,
    ];
    if (!empty($options['ajax'])) {
        $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }

    $request = new Request();
    $request->withServer($server);
    $request->setMethod($method);
    $request->setController($options['controller'] ?? 'AdminManager');
    $request->setAction($options['action'] ?? 'index');
    $request->setBaseUrl($baseUrl);
    if (isset($options['get'])) {
        $request->withGet($options['get']);
    }
    if (isset($options['post'])) {
        $request->withPost($options['post']);
    }
    $request->noNeedLogin = $options['noNeedLogin'] ?? [];
    $request->noNeedPermission = $options['noNeedPermission'] ?? [];

    $app->instance('request', $request);

    return $request;
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_admin_disabled_auth_cache_' . getmypid() . '_' . uniqid('', true) . '/';
$tmpRuntimeDir = sys_get_temp_dir() . '/ztbcms_admin_disabled_auth_runtime_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    $app->http->name('admin');
    $app->setAppPath($app->getBasePath() . 'admin' . DIRECTORY_SEPARATOR);
    $app->setNamespace('app\\admin');
    $app->setRuntimePath($tmpRuntimeDir);

    Config::set([
        'authcode' => 'admin-disabled-account-auth-test-key',
    ], 'system');

    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $tmpCacheDir,
                'prefix' => 'test_',
                'expire' => 0,
                'tag_prefix' => 'tag:',
                'serialize' => [],
            ],
        ],
    ], 'cache');

    Config::set([
        'default' => 'admin_disabled_auth_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_disabled_auth_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    View::assign('_Config', ['sitename' => 'ZTBCMS Test']);

    seedDisabledAuthFixture();
    resetAdminUserState();

    $adminUserService = AdminUserService::getInstance();
    $middleware = new AdminAuth();
    $rbacService = new RbacService();

    // ==================== 1. AdminUserService::login 校验账号状态 ====================
    bindDisabledAuthRequest($app, ['method' => 'POST', 'base_url' => '/admin/Login/doLogin']);

    // 1.1 禁用账号密码正确也必须拒绝登录
    $disabledLoginOk = $adminUserService->login('disabled_user', 'DisabledPass123!');
    assertDisabledAuthSame(false, $disabledLoginOk, '禁用账号输入正确密码也必须拒绝登录');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '禁用账号登录失败后不应写入 KEY_USER_ID');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '禁用账号登录失败后不应写入 KEY_USER_INFO');
    $disabledRow = Db::name('user')->where('id', 3)->find();
    assertDisabledAuthSame(0, (int) $disabledRow['last_login_time'], '禁用账号拒绝登录时不应更新 last_login_time');
    assertDisabledAuthSame('', (string) $disabledRow['last_login_ip'], '禁用账号拒绝登录时不应更新 last_login_ip');

    // 1.2 异常非启用状态（status=2）同样拒绝登录
    $abnormalLoginOk = $adminUserService->login('abnormal_user', 'OtherStatusPass123!');
    assertDisabledAuthSame(false, $abnormalLoginOk, '非 STATUS_ENABLE 状态账号必须拒绝登录');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '非启用账号登录失败后不应写入会话');

    // 1.3 启用账号密码正确应登录成功
    $enableLoginOk = $adminUserService->login('operator', 'OperatorPass123!');
    assertDisabledAuthSame(true, $enableLoginOk, '启用账号输入正确密码应登录成功');
    assertDisabledAuthSame(2, (int) $adminUserService->isLogin(), '启用账号登录后 isLogin 应返回用户 ID 2');
    $operatorRow = Db::name('user')->where('id', 2)->find();
    assertDisabledAuthTrue((int) $operatorRow['last_login_time'] > 0, '启用账号登录后应更新 last_login_time');
    assertDisabledAuthSame('127.0.0.1', (string) $operatorRow['last_login_ip'], '启用账号登录后应更新 last_login_ip');

    // 登录日志应包含两条失败记录与一条成功记录
    $loginLogs = Db::name('login_log')->order('id', 'asc')->select()->toArray();
    assertDisabledAuthSame(3, count($loginLogs), '应生成 3 条登录日志');
    assertDisabledAuthSame(0, (int) $loginLogs[0]['status'], '禁用账号登录应记录 status=0 失败日志');
    assertDisabledAuthSame(0, (int) $loginLogs[1]['status'], '异常状态账号登录应记录 status=0 失败日志');
    assertDisabledAuthSame(1, (int) $loginLogs[2]['status'], '启用账号登录应记录 status=1 成功日志');

    // 保存一份旧 Session 快照备用
    $savedOperatorToken = Session::get(AdminUserService::KEY_USER_ID);
    $savedOperatorInfo = Session::get(AdminUserService::KEY_USER_INFO);
    assertDisabledAuthTrue(is_string($savedOperatorToken) && $savedOperatorToken !== '', '登录后应存在有效 KEY_USER_ID');
    assertDisabledAuthSame(AdminUserModel::STATUS_ENABLE, (int) ($savedOperatorInfo['status'] ?? 0), '登录后 Session 快照应为启用状态');

    // ==================== 2. AdminAuth 实时拦截禁用账号（含需鉴权与 noNeedPermission 路由） ====================
    Db::name('user')->where('id', 2)->update(['status' => AdminUserModel::STATUS_DISABLE]);
    clearAdminUserStaticCache();

    // 2.1 AJAX 访问需鉴权路由 -> 403 且强制登出
    $nextCalled = false;
    $reqDisabledAjax = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'controller' => 'AdminManager',
        'action' => 'index',
        'base_url' => '/admin/AdminManager/index',
    ]);
    $resp = $middleware->handle($reqDisabledAjax, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, '禁用账号请求需鉴权路由不应放行到控制器');
    assertDisabledAuthSame(false, $respData['status'] ?? true, '禁用账号 AJAX 响应 status 应为 false');
    assertDisabledAuthSame(403, $respData['code'] ?? 0, '禁用账号 AJAX 响应 code 应为 403');
    assertDisabledAuthSame('账号已被禁用', $respData['msg'] ?? '', '禁用账号 AJAX 响应 msg 应为「账号已被禁用」');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '被拦截后 KEY_USER_ID 应已注销');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '被拦截后 KEY_USER_INFO 应已清空');

    // 2.2 恢复旧 status=1 快照，AJAX 访问 noNeedPermission 路由 -> 仍被 403 拦截并登出
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::set(AdminUserService::KEY_USER_INFO, $savedOperatorInfo);
    clearAdminUserStaticCache();
    $nextCalled = false;
    $reqDisabledNoPerm = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'controller' => 'Dashboard',
        'action' => 'index',
        'base_url' => '/admin/Dashboard/index',
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqDisabledNoPerm, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, '禁用账号访问 noNeedPermission 路由也绝不能放行');
    assertDisabledAuthSame(403, $respData['code'] ?? 0, '禁用账号访问 noNeedPermission 路由应返回 403');
    assertDisabledAuthSame('账号已被禁用', $respData['msg'] ?? '', '禁用账号访问 noNeedPermission 路由应返回「账号已被禁用」');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '禁用账号访问 noNeedPermission 路由后应强制注销');

    // 2.3 恢复旧 status=1 快照，非 AJAX 页面请求 -> 返回包含「账号已被禁用」页面并登出
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::set(AdminUserService::KEY_USER_INFO, $savedOperatorInfo);
    clearAdminUserStaticCache();
    $nextCalled = false;
    $reqDisabledPage = bindDisabledAuthRequest($app, [
        'ajax' => false,
        'controller' => 'Dashboard',
        'action' => 'index',
        'base_url' => '/admin/Dashboard/index',
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqDisabledPage, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $pageContent = (string) $resp->getContent();
    assertDisabledAuthSame(false, $nextCalled, '禁用账号页面请求不应放行');
    assertDisabledAuthTrue(strpos($pageContent, '账号已被禁用') !== false, '非 AJAX 拦截页面应展示「账号已被禁用」');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '非 AJAX 拦截后应已清空 KEY_USER_ID');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '非 AJAX 拦截后应已清空 KEY_USER_INFO');

    // ==================== 3. 防止 KEY_USER_INFO 缺失或被篡改为其他启用账号 ====================
    // 3.1 KEY_USER_ID 对应已禁用账号 id=2，KEY_USER_INFO 置空
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::delete(AdminUserService::KEY_USER_INFO);
    clearAdminUserStaticCache();
    $nextCalled = false;
    $reqMissingInfo = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqMissingInfo, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, 'KEY_USER_INFO 缺失时仍应按 KEY_USER_ID 实时拦截禁用账号');
    assertDisabledAuthSame(403, $respData['code'] ?? 0, 'KEY_USER_INFO 缺失时禁用账号应返回 403');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), 'KEY_USER_INFO 缺失触发拦截后应注销会话');

    // 3.2 KEY_USER_ID 对应已禁用账号 id=2，但 KEY_USER_INFO 篡改为启用超管 id=1
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::set(AdminUserService::KEY_USER_INFO, [
        'id' => 1,
        'username' => 'super_admin',
        'role_id' => 1,
        'status' => AdminUserModel::STATUS_ENABLE,
    ]);
    clearAdminUserStaticCache();
    $nextCalled = false;
    $reqMismatchedInfo = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqMismatchedInfo, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, 'KEY_USER_INFO 被篡改为其他启用超管时，仍必须以 KEY_USER_ID 为准拦截');
    assertDisabledAuthSame(403, $respData['code'] ?? 0, '会话信息与 KEY_USER_ID 不一致且实际账号禁用时应返回 403');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '拦截后应销毁伪造会话');

    // ==================== 4. AdminAuth 实时拦截已删除账号 ====================
    Db::name('user')->where('id', 2)->update(['status' => AdminUserModel::STATUS_ENABLE]);
    Db::name('user')->where('id', 2)->delete();

    // 4.1 AJAX 请求已删除账号
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::set(AdminUserService::KEY_USER_INFO, $savedOperatorInfo);
    clearAdminUserStaticCache();
    $nextCalled = false;
    $reqDeletedAjax = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqDeletedAjax, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, '已删除账号不应放行');
    assertDisabledAuthSame(401, $respData['code'] ?? 0, '已删除账号 AJAX 应返回 401');
    assertDisabledAuthSame('请登录账号', $respData['msg'] ?? '', '已删除账号 AJAX 应返回「请登录账号」');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '已删除账号拦截后应强制登出');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '已删除账号拦截后应清空 KEY_USER_INFO');

    // 4.2 非 AJAX 页面请求已删除账号
    Session::set(AdminUserService::KEY_USER_ID, $savedOperatorToken);
    Session::set(AdminUserService::KEY_USER_INFO, $savedOperatorInfo);
    clearAdminUserStaticCache();
    $reqDeletedPage = bindDisabledAuthRequest($app, [
        'ajax' => false,
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqDeletedPage, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    assertDisabledAuthTrue(strpos((string) $resp->getContent(), '请登录账号') !== false, '已删除账号页面请求应展示未登录页面');
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '已删除账号页面拦截后应清空会话');

    // ==================== 5. 正常启用账号放行与 Session 实时同步 ====================
    seedDisabledAuthFixture();
    resetAdminUserState();
    assertDisabledAuthSame(true, $adminUserService->login('operator', 'OperatorPass123!'), '重新登录启用账号应成功');

    // 在数据库中修改昵称，验证通过 AdminAuth 后 Session 同步更新且不含敏感字段
    Db::name('user')->where('id', 2)->update(['nickname' => '新实时昵称']);
    clearAdminUserStaticCache();

    // 5.1 访问 noNeedPermission 路由放行
    $nextCalled = false;
    $reqEnabledNoPerm = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'controller' => 'Dashboard',
        'action' => 'index',
        'base_url' => '/admin/Dashboard/index',
        'noNeedPermission' => ['index'],
    ]);
    $resp = $middleware->handle($reqEnabledNoPerm, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('ok-no-perm');
    });
    assertDisabledAuthSame(true, $nextCalled, '启用账号访问 noNeedPermission 路由应正常放行');
    assertDisabledAuthSame('ok-no-perm', $resp->getContent(), '放行后应返回控制器响应');

    $syncedSession = Session::get(AdminUserService::KEY_USER_INFO);
    assertDisabledAuthSame('新实时昵称', $syncedSession['nickname'] ?? '', 'AdminAuth 放行后 Session nickname 应同步为数据库最新值');
    assertDisabledAuthTrue(!array_key_exists('password', $syncedSession), '同步后的 Session 不得包含 password 字段');
    assertDisabledAuthTrue(!array_key_exists('verify', $syncedSession), '同步后的 Session 不得包含 verify 字段');

    // 5.2 访问已授权菜单路由放行
    $nextCalled = false;
    $reqEnabledAuthorized = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'controller' => 'AdminManager',
        'action' => 'index',
        'base_url' => '/admin/AdminManager/index',
    ]);
    $resp = $middleware->handle($reqEnabledAuthorized, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('ok-authorized');
    });
    assertDisabledAuthSame(true, $nextCalled, '启用账号访问已授权菜单路由应正常放行');
    assertDisabledAuthSame('ok-authorized', $resp->getContent(), '已授权菜单路由应返回控制器响应');

    // 5.3 访问未授权菜单路由拦截并返回 403 无权限
    $nextCalled = false;
    $reqEnabledUnauthorized = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'controller' => 'Role',
        'action' => 'index',
        'base_url' => '/admin/Role/index',
    ]);
    $resp = $middleware->handle($reqEnabledUnauthorized, function () use (&$nextCalled) {
        $nextCalled = true;
        return response('should not reach');
    });
    $respData = $resp->getData();
    assertDisabledAuthSame(false, $nextCalled, '未授权菜单路由不应放行');
    assertDisabledAuthSame(403, $respData['code'] ?? 0, '未授权菜单路由应返回 403');
    assertDisabledAuthSame('无权限', $respData['msg'] ?? '', '未授权菜单路由应返回「无权限」');

    // ==================== 6. AdminAuth Fail-closed 验证 ====================
    Db::execute('DROP TABLE user');
    clearAdminUserStaticCache();
    $failClosedCaught = false;
    $nextCalled = false;
    $reqDbFailure = bindDisabledAuthRequest($app, [
        'ajax' => true,
        'noNeedPermission' => ['index'],
    ]);
    try {
        $middleware->handle($reqDbFailure, function () use (&$nextCalled) {
            $nextCalled = true;
            return response('should not reach');
        });
    } catch (\Throwable $e) {
        $failClosedCaught = true;
    }
    assertDisabledAuthSame(false, $nextCalled, '数据库异常时 AdminAuth 绝不允许静默放行');
    assertDisabledAuthSame(true, $failClosedCaught, '数据库异常时 AdminAuth 必须 fail-closed 抛出异常');

    // 重建表结构并初始化数据
    seedDisabledAuthFixture();
    resetAdminUserState();

    // ==================== 7. 权限服务与超管判断实时状态校验 ====================
    assertDisabledAuthSame(true, $adminUserService->login('super_admin', 'AdminPass123!'), '超管登录应成功');
    assertDisabledAuthSame(true, $adminUserService->isAdministrator(), '启用超管调用 isAdministrator 应返回 true');
    assertDisabledAuthSame(true, $adminUserService->hasPermission('admin', 'AdminManager', 'index'), '启用超管调用 hasPermission 应返回 true');

    // 数据库中将超管禁用后，持旧 Session 验证权限接口与超管判断
    Db::name('user')->where('id', 1)->update(['status' => AdminUserModel::STATUS_DISABLE]);
    clearAdminUserStaticCache();

    $enableAccessRes = $rbacService->enableUserAccess(1, 'admin', 'AdminManager', 'index');
    assertDisabledAuthSame(false, $enableAccessRes['status'] ?? true, '超管被禁用后 RbacService::enableUserAccess 应拒绝');
    assertDisabledAuthSame('账号已被禁用', $enableAccessRes['msg'] ?? '', '超管被禁用后 RbacService::enableUserAccess 应提示「账号已被禁用」');
    assertDisabledAuthSame(false, $adminUserService->isAdministrator(), '超管被禁用后 isAdministrator 应实时返回 false');
    assertDisabledAuthSame(false, $adminUserService->hasPermission('admin', 'AdminManager', 'index'), '超管被禁用后 hasPermission 应返回 false');
    assertDisabledAuthSame(null, $adminUserService->getRealtimeOperatorInfo(), '超管被禁用后 getRealtimeOperatorInfo 应返回 null');
    assertDisabledAuthSame(0, $adminUserService->getRealtimeOperatorRoleId(), '超管被禁用后 getRealtimeOperatorRoleId 应返回 0');

    // 未登录时调用 hasPermission 应安全返回 false
    resetAdminUserState();
    assertDisabledAuthSame(false, $adminUserService->hasPermission('admin', 'AdminManager', 'index'), '未登录调用 hasPermission 应返回 false');

    // ==================== 8. Login::index() 对失效会话的清理 ====================
    Config::set(['admin_panel_security_code' => ''], 'admin');

    // 8.1 启用账号访问 Login::index() 应重定向到后台 Dashboard
    resetAdminUserState();
    assertDisabledAuthSame(true, $adminUserService->login('operator', 'OperatorPass123!'), '启用账号登录应成功');
    bindDisabledAuthRequest($app, ['controller' => 'Login', 'action' => 'index', 'base_url' => '/admin/Login/index']);
    $loginController = new Login($app);
    $loginResp = $loginController->index();
    assertDisabledAuthTrue($loginResp instanceof Response && $loginResp->getCode() === 302, '启用账号访问 Login::index 应返回 302 重定向');

    // 8.2 被禁用账号持旧 Session 访问 Login::index() 不得跳转到 Dashboard 且应清理 Session
    $opToken = Session::get(AdminUserService::KEY_USER_ID);
    $opInfo = Session::get(AdminUserService::KEY_USER_INFO);
    Db::name('user')->where('id', 2)->update(['status' => AdminUserModel::STATUS_DISABLE]);
    clearAdminUserStaticCache();
    bindDisabledAuthRequest($app, ['controller' => 'Login', 'action' => 'index', 'base_url' => '/admin/Login/index']);
    $loginController = new Login($app);
    $disabledLoginIndexResp = $loginController->index();
    assertDisabledAuthTrue(
        is_string($disabledLoginIndexResp) && strpos($disabledLoginIndexResp, 'doLogin') !== false,
        '禁用账号持旧 Session 访问 Login::index 应展示登录页而非 302 跳转至后台首页'
    );
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '禁用账号访问 Login::index 后应清空 KEY_USER_ID');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '禁用账号访问 Login::index 后应清空 KEY_USER_INFO');

    // 8.3 已删除账号持旧 Session 访问 Login::index() 不得跳转到 Dashboard 且应清理 Session
    Session::set(AdminUserService::KEY_USER_ID, $opToken);
    Session::set(AdminUserService::KEY_USER_INFO, $opInfo);
    Db::name('user')->where('id', 2)->delete();
    clearAdminUserStaticCache();
    bindDisabledAuthRequest($app, ['controller' => 'Login', 'action' => 'index', 'base_url' => '/admin/Login/index']);
    $loginController = new Login($app);
    $deletedLoginIndexResp = $loginController->index();
    assertDisabledAuthTrue(
        is_string($deletedLoginIndexResp) && strpos($deletedLoginIndexResp, 'doLogin') !== false,
        '已删除账号持旧 Session 访问 Login::index 应展示登录页而非 302 跳转至后台首页'
    );
    assertDisabledAuthSame(0, (int) $adminUserService->isLogin(), '已删除账号访问 Login::index 后应清空 KEY_USER_ID');
    assertDisabledAuthSame(null, $adminUserService->getInfo(), '已删除账号访问 Login::index 后应清空 KEY_USER_INFO');
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    resetAdminUserState();
    restore_error_handler();
    foreach ([$tmpCacheDir, $tmpRuntimeDir] as $dir) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
            }
            @rmdir($dir);
        }
    }
}

echo "admin_disabled_account_auth_test passed\n";
