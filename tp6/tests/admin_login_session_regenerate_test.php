<?php

use app\admin\controller\Login;
use app\admin\model\AdminUserModel;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\common\libs\helper\PasswordHelper;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\middleware\SessionInit;
use think\Request;
use think\Response;

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
function assertSessionTestTrue(bool $condition, string $message): void
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
function assertSessionTestSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 仅清空 AdminUserService 内存静态缓存（模拟新 HTTP 请求）
 *
 * @return void
 */
function clearSessionTestStaticCache(): void
{
    $ref = new ReflectionClass(AdminUserService::class);
    $prop = $ref->getProperty('userInfo');
    $prop->setAccessible(true);
    $prop->setValue(null, []);
}

/**
 * 重置 AdminUserService 静态缓存及当前 Session 状态
 *
 * @return void
 */
function resetSessionTestState(): void
{
    clearSessionTestStaticCache();
    Session::clear();
}

/**
 * 创建测试用 SQLite 内存表结构
 *
 * @return void
 */
function createSessionTestTables(): void
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

    Db::execute('CREATE TABLE IF NOT EXISTS login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');
}

/**
 * 初始化测试夹具数据
 *
 * @return void
 */
function seedSessionTestFixture(): void
{
    createSessionTestTables();
    Db::execute('DELETE FROM config');
    Db::execute('DELETE FROM role');
    Db::execute('DELETE FROM user');
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

    Db::name('user')->insertAll([
        [
            'id' => 1,
            'username' => 'super_admin',
            'nickname' => '超级管理员',
            'password' => PasswordHelper::hashPassword('AdminPass123!', $verify1),
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
            'nickname' => '普通运营',
            'password' => PasswordHelper::hashPassword('OperatorPass123!', $verify2),
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
            'password' => PasswordHelper::hashPassword('DisabledPass123!', $verify3),
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
function bindSessionTestRequest(App $app, array $options = []): Request
{
    $method = strtoupper($options['method'] ?? 'POST');
    $baseUrl = $options['base_url'] ?? '/admin/Login/doLogin';
    $server = [
        'REQUEST_METHOD' => $method,
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_HOST' => 'localhost',
        'REQUEST_URI' => $baseUrl,
    ];

    $request = new Request();
    $request->withServer($server);
    $request->setMethod($method);
    $request->setController($options['controller'] ?? 'Login');
    $request->setAction($options['action'] ?? 'doLogin');
    $request->setBaseUrl($baseUrl);
    if (isset($options['cookie'])) {
        $request->withCookie($options['cookie']);
    }
    if (isset($options['post'])) {
        $request->withPost($options['post']);
    }

    $app->instance('request', $request);

    return $request;
}

/**
 * 模拟一次完整经过 SessionInit 中间件的 HTTP 请求（含请求结束保存 Session）
 *
 * @param App $app 应用实例
 * @param string|null $cookieSid 请求携带的 SID Cookie 值
 * @param callable $handler 业务回调
 * @param array $requestOptions 请求选项
 * @return array{response: Response, cookies: array, session_id: string}
 */
function runWithSessionInit(App $app, ?string $cookieSid, callable $handler, array $requestOptions = []): array
{
    // 模拟全新请求生命周期：清理内存态 Session 数据、静态缓存与待下发 Cookie
    resetSessionTestState();
    $app->cookie->set('SID', '', ['expire' => -3600]);
    $cookieRef = new ReflectionClass($app->cookie);
    $cookieProp = $cookieRef->getProperty('cookie');
    $cookieProp->setAccessible(true);
    $cookieProp->setValue($app->cookie, []);

    $sessionName = (string) Config::get('session.name', 'SID');
    if ($cookieSid !== null) {
        $requestOptions['cookie'] = array_merge($requestOptions['cookie'] ?? [], [$sessionName => $cookieSid]);
    } else {
        // 未提供 SID 时重置一个随机新 ID，避免沿用上一次请求在单例 Store 中的 ID
        $app->session->setId(null);
    }

    $request = bindSessionTestRequest($app, $requestOptions);
    $sessionInit = new SessionInit($app, $app->session);

    $response = $sessionInit->handle($request, function () use ($handler) {
        $result = $handler();
        return $result instanceof Response ? $result : response((string) $result);
    });
    $sessionInit->end($response);

    return [
        'response' => $response,
        'cookies' => $app->cookie->getCookie(),
        'session_id' => $app->session->getId(),
    ];
}

/**
 * 读取指定 Session ID 在服务端缓存存储中的原始持久化数据
 *
 * @param string $sessionId 会话 ID
 * @return mixed
 */
function readRawSessionStore(string $sessionId)
{
    $prefix = (string) Config::get('session.prefix', 'SID_');
    return Cache::store()->get($prefix . $sessionId);
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_admin_session_regen_cache_' . getmypid() . '_' . uniqid('', true) . '/';
$tmpRuntimeDir = sys_get_temp_dir() . '/ztbcms_admin_session_regen_runtime_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    $app->http->name('admin');
    $app->setAppPath($app->getBasePath() . 'admin' . DIRECTORY_SEPARATOR);
    $app->setNamespace('app\\admin');
    $app->setRuntimePath($tmpRuntimeDir);

    Config::set([
        'authcode' => 'admin-login-session-regenerate-test-key',
    ], 'system');

    configureIsolatedTestFileCache($tmpCacheDir, ['prefix' => 'test_']);

    Config::set([
        'default' => 'admin_session_regen_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_session_regen_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    View::assign('_Config', ['sitename' => 'ZTBCMS Test']);

    seedSessionTestFixture();
    resetSessionTestState();

    $adminUserService = AdminUserService::getInstance();
    $sessionName = (string) Config::get('session.name', 'SID');

    // ==================== 1. 登录成功防会话固定（完整 SessionInit 中间件链路） ====================
    $fixedAttackerSid = md5('attacker-fixed-session-id-1');

    // 1.1 预置固定 SID 的登录前会话数据（验证码与攻击者预写入脏键）
    $preLoginResult = runWithSessionInit($app, $fixedAttackerSid, function () {
        Session::set('verify', 'abcd');
        Session::set('pre_login_key', 'attacker_injected_value');
        return 'pre-login-ok';
    });
    assertSessionTestSame($fixedAttackerSid, $preLoginResult['session_id'], '预置请求应使用指定固定 SID');
    assertSessionTestTrue(!empty(readRawSessionStore($fixedAttackerSid)), '预置请求后服务端缓存中应存在固定 SID 数据');

    // 1.2 携带固定 SID 发起登录请求
    $loginResult = runWithSessionInit($app, $fixedAttackerSid, function () use ($adminUserService) {
        $ok = $adminUserService->login('operator', 'OperatorPass123!');
        assertSessionTestSame(true, $ok, '启用账号使用正确密码应登录成功');
        return 'login-ok';
    });

    $newSid = $loginResult['session_id'];
    assertSessionTestTrue(
        strlen($newSid) === 32 && ctype_alnum($newSid),
        '登录后生成的新 Session ID 应为 32 位字母数字字符串'
    );
    assertSessionTestTrue(
        $newSid !== $fixedAttackerSid,
        '登录后 Session ID 必须发生变化以防范会话固定'
    );
    assertSessionTestSame(
        $newSid,
        $loginResult['cookies'][$sessionName][0] ?? null,
        'SessionInit 响应 Cookie 中的 SID 必须同步更新为新的 Session ID'
    );

    // 1.3 验证旧固定 SID 服务端存储已销毁且不能访问后台会话
    assertSessionTestTrue(
        empty(readRawSessionStore($fixedAttackerSid)),
        '登录后旧固定 SID 对应的服务端缓存存储必须已被删除'
    );
    runWithSessionInit($app, $fixedAttackerSid, function () use ($adminUserService) {
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '携带旧固定 SID 发起新请求时 isLogin 必须为 0');
        assertSessionTestSame(null, $adminUserService->getInfo(), '携带旧固定 SID 发起新请求时 getInfo 必须为 null');
        assertSessionTestSame(false, Session::has('pre_login_key'), '旧固定 SID 不得再恢复登录前脏数据');
        return 'old-sid-check';
    });

    // 1.4 验证新 SID 拥有有效登录态且登录前脏数据已被清除
    runWithSessionInit($app, $newSid, function () use ($adminUserService) {
        assertSessionTestSame(2, (int) $adminUserService->isLogin(), '携带新 SID 发起新请求时 isLogin 应返回用户 ID 2');
        $info = $adminUserService->getInfo();
        assertSessionTestTrue(is_array($info), '携带新 SID 发起新请求时 getInfo 应返回用户数组');
        assertSessionTestSame('operator', $info['username'] ?? '', '新 SID 会话用户名应为 operator');
        assertSessionTestSame(false, Session::has('pre_login_key'), '新 SID 会话中不得保留登录前的 pre_login_key 脏数据');
        assertSessionTestSame(false, Session::has('verify'), '新 SID 会话中不得保留登录前的 verify 数据');
        return 'new-sid-check';
    });

    // 1.5 验证通过 Login::doLogin() 完整控制器入口登录同样轮换 Session ID 且清除验证码
    $controllerFixedSid = md5('attacker-fixed-session-id-controller');
    $verifySessionKey = md5('verify');
    $verifyCode = '';
    runWithSessionInit($app, $controllerFixedSid, function () use (&$verifyCode) {
        $checkcode = new \app\common\libs\checkcode\Checkcode(0);
        $checkcode->type = 'verify';
        $verifyCode = $checkcode->getCode();
        Session::set('pre_login_key', 'controller_dirty');
        return 'seeded-verify';
    });
    $doLoginResult = runWithSessionInit($app, $controllerFixedSid, function () use ($app) {
        $controller = new Login($app);
        return $controller->doLogin();
    }, [
        'method' => 'POST',
        'controller' => 'Login',
        'action' => 'doLogin',
        'base_url' => '/admin/Login/doLogin',
        'post' => [
            'form' => [
                'username' => base64_encode(urlencode('super_admin')),
                'password' => base64_encode(urlencode('AdminPass123!')),
                'code' => $verifyCode,
            ],
        ],
    ]);
    $doLoginData = $doLoginResult['response']->getData();
    assertSessionTestSame(true, $doLoginData['status'] ?? false, 'Login::doLogin 使用有效验证码与凭据应返回成功: ' . ($doLoginData['msg'] ?? ''));
    $controllerNewSid = $doLoginResult['session_id'];
    assertSessionTestTrue($controllerNewSid !== $controllerFixedSid, 'Login::doLogin 成功后必须轮换 Session ID');
    assertSessionTestSame($controllerNewSid, $doLoginResult['cookies'][$sessionName][0] ?? null, 'Login::doLogin 响应 Cookie 应为新 SID');
    assertSessionTestTrue(empty(readRawSessionStore($controllerFixedSid)), 'Login::doLogin 成功后旧 SID 存储应已删除');
    runWithSessionInit($app, $controllerNewSid, function () use ($adminUserService, $verifySessionKey) {
        assertSessionTestSame(1, (int) $adminUserService->isLogin(), 'Login::doLogin 新 SID 应持有管理员 ID 1');
        assertSessionTestSame(false, Session::has($verifySessionKey), 'Login::doLogin 新 SID 中应清除登录前验证码');
        assertSessionTestSame(false, Session::has($verifySessionKey . 'count'), 'Login::doLogin 新 SID 中应清除登录前验证码计数');
        assertSessionTestSame(false, Session::has('pre_login_key'), 'Login::doLogin 新 SID 中应清除登录前脏键');
        return 'controller-new-sid-check';
    });

    // ==================== 2. 登录失败不轮换 Session ID ====================
    $failTestSid = md5('pre-auth-fail-test-sid');
    runWithSessionInit($app, $failTestSid, function () {
        Session::set('pre_login_key', 'keep_on_failure');
        return 'seed-fail-sid';
    });

    // 2.1 密码错误
    $wrongPassResult = runWithSessionInit($app, $failTestSid, function () use ($adminUserService) {
        $ok = $adminUserService->login('operator', 'WrongPassword!');
        assertSessionTestSame(false, $ok, '密码错误应登录失败');
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '密码错误不应写入登录态');
        assertSessionTestSame(null, $adminUserService->getInfo(), '密码错误 getInfo 应为 null');
        return 'wrong-pass';
    });
    assertSessionTestSame($failTestSid, $wrongPassResult['session_id'], '密码错误时不应轮换 Session ID');

    // 2.2 用户不存在
    $unknownUserResult = runWithSessionInit($app, $failTestSid, function () use ($adminUserService) {
        $ok = $adminUserService->login('non_existent_user', 'SomePass123!');
        assertSessionTestSame(false, $ok, '用户不存在应登录失败');
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '用户不存在不应写入登录态');
        return 'unknown-user';
    });
    assertSessionTestSame($failTestSid, $unknownUserResult['session_id'], '用户不存在时不应轮换 Session ID');

    // 2.3 账号已禁用
    $disabledUserResult = runWithSessionInit($app, $failTestSid, function () use ($adminUserService) {
        $ok = $adminUserService->login('disabled_user', 'DisabledPass123!');
        assertSessionTestSame(false, $ok, '禁用账号应登录失败');
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '禁用账号不应写入登录态');
        return 'disabled-user';
    });
    assertSessionTestSame($failTestSid, $disabledUserResult['session_id'], '禁用账号登录失败时不应轮换 Session ID');
    assertSessionTestTrue(!empty(readRawSessionStore($failTestSid)), '登录失败后原未登录 Session 存储仍应保留');

    // ==================== 3. 注销登录销毁旧会话并轮换 Session ID ====================
    $logoutResult = runWithSessionInit($app, $newSid, function () use ($adminUserService) {
        assertSessionTestSame(2, (int) $adminUserService->isLogin(), '注销前应处于已登录状态');
        $ok = $adminUserService->logout();
        assertSessionTestSame(true, $ok, 'logout 应返回 true');
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), 'logout 后当前请求 isLogin 应为 0');
        assertSessionTestSame(null, $adminUserService->getInfo(), 'logout 后当前请求 getInfo 应为 null');
        return 'logout-ok';
    });

    $postLogoutSid = $logoutResult['session_id'];
    assertSessionTestTrue(
        strlen($postLogoutSid) === 32 && ctype_alnum($postLogoutSid),
        '注销后生成的新 Session ID 应为 32 位字母数字字符串'
    );
    assertSessionTestTrue(
        $postLogoutSid !== $newSid,
        '注销登录后 Session ID 必须发生变化'
    );
    assertSessionTestSame(
        $postLogoutSid,
        $logoutResult['cookies'][$sessionName][0] ?? null,
        '注销后响应 Cookie 中的 SID 应同步更新为新 Session ID'
    );
    assertSessionTestTrue(
        empty(readRawSessionStore($newSid)),
        '注销后原已登录 Session ID 对应的服务端存储必须已被删除'
    );

    // 3.1 使用注销前的旧 SID 发起新请求应处于未登录状态
    runWithSessionInit($app, $newSid, function () use ($adminUserService) {
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '携带注销前旧 SID 发起新请求时应为未登录');
        assertSessionTestSame(null, $adminUserService->getInfo(), '携带注销前旧 SID 发起新请求时 getInfo 应为 null');
        return 'old-logged-sid-check';
    });

    // 3.2 使用注销后的新 SID 发起新请求同样无登录态
    runWithSessionInit($app, $postLogoutSid, function () use ($adminUserService) {
        assertSessionTestSame(0, (int) $adminUserService->isLogin(), '携带注销后新 SID 发起新请求时应为未登录');
        assertSessionTestSame(null, $adminUserService->getInfo(), '携带注销后新 SID 发起新请求时 getInfo 应为 null');
        return 'post-logout-sid-check';
    });
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    resetSessionTestState();
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

echo "admin_login_session_regenerate_test passed\n";
