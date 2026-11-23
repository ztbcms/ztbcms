<?php

use app\admin\controller\AdminManager;
use app\admin\controller\AdminMessage;
use app\admin\controller\Cache as CacheController;
use app\admin\controller\Config as ConfigController;
use app\admin\controller\Login;
use app\admin\controller\Logs;
use app\admin\controller\Menu;
use app\admin\controller\Module;
use app\admin\controller\Role;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\common\util\Encrypt;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\middleware\SessionInit;
use think\Request;
use think\Response;
use think\response\Json;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

set_error_handler(
    /**
     * 将 PHP 警告与提示转换为异常
     *
     * @param int    $severity 错误级别
     * @param string $message  错误信息
     * @param string $file     错误文件
     * @param int    $line     错误行号
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
 * 断言条件为真
 *
 * @param bool   $condition 条件表达式
 * @param string $message   失败说明
 * @return void
 */
function assertCsrfFixTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

/**
 * 断言两个值严格相等
 *
 * @param mixed  $expected 期望值
 * @param mixed  $actual   实际值
 * @param string $message  失败说明
 * @return void
 */
function assertCsrfFixSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 期望: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 递归删除临时目录
 *
 * @param string $dir 目录路径
 * @return void
 */
function removeTempDirRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($dir);
}

/**
 * 构造并绑定模拟 HTTP 请求到应用容器
 *
 * @param App    $app        应用实例
 * @param string $method     请求方法
 * @param array  $get        GET 参数
 * @param array  $post       POST 参数
 * @param array  $route      路由参数
 * @param string $controller 控制器名
 * @param string $action     方法名
 * @return Request
 */
function bindCsrfTestRequest(
    App $app,
    string $method,
    array $get = [],
    array $post = [],
    array $route = [],
    string $controller = 'Index',
    string $action = 'index'
): Request {
    $upperMethod = strtoupper($method);
    $request = new Request();
    $request->withServer([
        'REQUEST_METHOD' => $upperMethod,
        'REMOTE_ADDR'    => '127.0.0.1',
        'HTTP_HOST'      => 'localhost',
        'REQUEST_URI'    => '/admin/' . $controller . '/' . $action,
    ]);
    $request->setMethod($upperMethod);
    $request->setController($controller);
    $request->setAction($action);
    $request->withGet($get);
    $request->withPost($post);
    $request->withRoute($route);

    $app->instance('request', $request);

    return $request;
}

/**
 * 重置 AdminUserService 内部缓存与 Session 登录态
 *
 * @param array $userInfo 管理员信息（空数组代表清除登录态）
 * @return void
 */
function setCsrfTestOperator(array $userInfo): void
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

    if (empty($userInfo)) {
        Session::delete(AdminUserService::KEY_USER_ID);
        Session::delete(AdminUserService::KEY_USER_INFO);
    } else {
        Session::set(AdminUserService::KEY_USER_ID, Encrypt::authcode((int) $userInfo['id'], ''));
        Session::set(AdminUserService::KEY_USER_INFO, $userInfo);
    }
}

/**
 * 断言响应为标准的 405 Method Not Allowed JSON 拒绝响应
 *
 * @param mixed  $response 控制器返回值
 * @param string $context  断言上下文说明
 * @return void
 */
function assertMethodNotAllowedResponse($response, string $context): void
{
    assertCsrfFixTrue($response instanceof Json, "{$context} 应返回 Json 响应对象");
    /** @var Json $response */
    assertCsrfFixSame(405, $response->getCode(), "{$context} HTTP 状态码应为 405");
    assertCsrfFixSame('POST', $response->getHeader('Allow'), "{$context} Allow 响应头应为 POST");

    $data = $response->getData();
    assertCsrfFixSame(false, $data['status'] ?? null, "{$context} status 应为 false");
    assertCsrfFixSame(405, $data['code'] ?? null, "{$context} 业务 code 应为 405");
    assertCsrfFixTrue(array_key_exists('data', $data), "{$context} 响应应包含 data 键");
    assertCsrfFixSame(null, $data['data'], "{$context} data 应为 null");
    assertCsrfFixSame('仅支持 POST 请求', $data['msg'] ?? null, "{$context} 错误文案应为「仅支持 POST 请求」");
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_csrf_samesite_cache_' . getmypid() . '_' . uniqid('', true) . '/';
$tmpRuntimeDir = sys_get_temp_dir() . '/ztbcms_csrf_samesite_runtime_' . getmypid() . '_' . uniqid('', true) . '/';
$tmpCleanRuntimeDir = sys_get_temp_dir() . '/ztbcms_csrf_cache_target_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    $app->http->name('admin');
    $app->setAppPath($app->getBasePath() . 'admin' . DIRECTORY_SEPARATOR);
    $app->setNamespace('app\\admin');
    $app->setRuntimePath($tmpRuntimeDir);

    // ==================== 1. Cookie SameSite=Lax 配置与 SessionInit 下发验证 ====================
    $cookieFileConfig = require dirname(__DIR__) . '/config/cookie.php';
    assertCsrfFixSame(
        'Lax',
        $cookieFileConfig['samesite'] ?? null,
        'config/cookie.php 必须配置 samesite => Lax'
    );
    assertCsrfFixSame(
        'Lax',
        Config::get('cookie.samesite'),
        '运行期 Config::get("cookie.samesite") 必须为 Lax'
    );

    Config::set([
        'default' => 'file',
        'stores'  => [
            'file' => [
                'type'       => 'File',
                'path'       => $tmpCacheDir,
                'prefix'     => 'test_',
                'expire'     => 0,
                'tag_prefix' => 'tag:',
                'serialize'  => [],
            ],
        ],
    ], 'cache');

    $sessionReq = bindCsrfTestRequest($app, 'GET', [], [], [], 'Dashboard', 'index');
    $sessionInit = new SessionInit($app, $app->session);
    $sessionResp = $sessionInit->handle($sessionReq, function () {
        return response('ok');
    });
    $sessionInit->end($sessionResp);

    $sessionName = (string) Config::get('session.name', 'SID');
    $pendingCookies = $app->cookie->getCookie();
    assertCsrfFixTrue(
        isset($pendingCookies[$sessionName]) && is_array($pendingCookies[$sessionName]),
        "SessionInit 应下发名为 {$sessionName} 的会话 Cookie"
    );
    $sessionCookieOption = $pendingCookies[$sessionName][2] ?? [];
    assertCsrfFixSame(
        'Lax',
        $sessionCookieOption['samesite'] ?? null,
        "SessionInit 下发的 {$sessionName} Cookie 必须带有 samesite => Lax"
    );

    // ==================== 2. 初始化内存 SQLite 与测试基础数据 ====================
    Config::set([
        'default'         => 'admin_csrf_post_only_samesite_test',
        'auto_timestamp'  => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections'     => [
            'admin_csrf_post_only_samesite_test' => [
                'type'          => 'sqlite',
                'database'      => ':memory:',
                'prefix'        => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL DEFAULT ""
    )');
    Db::name('config')->insertAll([
        ['varname' => 'sitename', 'info' => '站点名称', 'groupid' => 1, 'value' => 'ZTBCMS'],
        ['varname' => 'checkcode_type', 'info' => '验证码类型', 'groupid' => 1, 'value' => '1'],
        ['varname' => 'admin_operation_switch', 'info' => '操作日志开关', 'groupid' => 1, 'value' => '1'],
    ]);

    Db::execute('CREATE TABLE config_field (
        fid INTEGER PRIMARY KEY AUTOINCREMENT,
        fieldname VARCHAR(64) NOT NULL,
        type VARCHAR(32) NOT NULL,
        setting TEXT NOT NULL,
        createtime INTEGER NOT NULL DEFAULT 0
    )');
    Db::name('config_field')->insert([
        'fid'        => 1,
        'fieldname'  => 'custom_ext_key',
        'type'       => 'input',
        'setting'    => serialize(['title' => '自定义字段', 'tips' => '', 'option' => []]),
        'createtime' => time(),
    ]);
    Db::name('config')->insert([
        'varname' => 'custom_ext_key',
        'info'    => '自定义字段',
        'groupid' => 2,
        'value'   => 'ext_val',
    ]);

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
    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1],
        ['id' => 2, 'name' => '待删角色', 'parentid' => 1, 'status' => 1],
    ]);

    Db::execute('CREATE TABLE access (
        role_id INTEGER NOT NULL,
        app VARCHAR(64) NOT NULL,
        controller VARCHAR(64) NOT NULL,
        action VARCHAR(64) NOT NULL,
        status INTEGER NOT NULL DEFAULT 1
    )');
    Db::name('access')->insert([
        'role_id'    => 2,
        'app'        => 'admin',
        'controller' => 'Main',
        'action'     => 'index',
        'status'     => 1,
    ]);

    Db::execute('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL,
        nickname VARCHAR(64) NOT NULL,
        password VARCHAR(64) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(128) NOT NULL DEFAULT "",
        avatar VARCHAR(255) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT "",
        role_id INTEGER NOT NULL,
        info VARCHAR(255) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        last_login_ip VARCHAR(64) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');
    Db::name('user')->insertAll([
        ['id' => 1, 'username' => 'admin', 'nickname' => '超管', 'role_id' => 1, 'status' => 1],
        ['id' => 2, 'username' => 'operator', 'nickname' => '待删管理员', 'role_id' => 2, 'status' => 1],
    ]);

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
    Db::name('menu')->insertAll([
        ['id' => 1, 'name' => '系统管理', 'parentid' => 0, 'app' => 'admin', 'controller' => 'Main', 'action' => 'index'],
        ['id' => 2, 'name' => '待删菜单', 'parentid' => 1, 'app' => 'admin', 'controller' => 'Test', 'action' => 'test'],
    ]);

    $now = time();
    $daySeconds = 24 * 60 * 60;
    Db::execute('CREATE TABLE login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');
    Db::name('login_log')->insertAll([
        ['username' => 'old_user', 'logintime' => $now - 40 * $daySeconds, 'loginip' => '127.0.0.1', 'status' => 1, 'info' => '40d'],
        ['username' => 'new_user', 'logintime' => $now - 5 * $daySeconds, 'loginip' => '127.0.0.1', 'status' => 1, 'info' => '5d'],
    ]);

    Db::execute('CREATE TABLE operation_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        time INTEGER NOT NULL DEFAULT 0,
        ip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 1,
        method VARCHAR(16) NOT NULL DEFAULT "POST",
        url VARCHAR(255) NOT NULL DEFAULT "",
        params TEXT NOT NULL DEFAULT "",
        response TEXT NOT NULL DEFAULT ""
    )');
    Db::name('operation_log')->insertAll([
        ['uid' => 1, 'time' => $now - 40 * $daySeconds, 'ip' => '127.0.0.1', 'status' => 1, 'method' => 'POST', 'url' => '/admin/Config/index', 'params' => '{}', 'response' => '{}'],
        ['uid' => 1, 'time' => $now - 5 * $daySeconds, 'ip' => '127.0.0.1', 'status' => 1, 'method' => 'POST', 'url' => '/admin/Config/index', 'params' => '{}', 'response' => '{}'],
    ]);

    Db::execute('CREATE TABLE user_operate_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL DEFAULT 0,
        user_name VARCHAR(64) NOT NULL DEFAULT "",
        create_time INTEGER NOT NULL DEFAULT 0,
        ip VARCHAR(64) NOT NULL DEFAULT "",
        source_type VARCHAR(64) NOT NULL DEFAULT "",
        source VARCHAR(64) NOT NULL DEFAULT "",
        content VARCHAR(255) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE admin_message (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL DEFAULT "",
        content VARCHAR(255) NOT NULL DEFAULT "",
        target VARCHAR(255) NOT NULL DEFAULT "",
        target_type VARCHAR(255) NOT NULL DEFAULT "",
        sender VARCHAR(255) NOT NULL DEFAULT "",
        sender_type VARCHAR(255) NOT NULL DEFAULT "",
        receiver VARCHAR(255) NOT NULL DEFAULT "",
        receiver_type VARCHAR(255) NOT NULL DEFAULT "",
        type VARCHAR(255) NOT NULL DEFAULT "",
        read_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        read_status INTEGER NOT NULL DEFAULT 0
    )');
    Db::name('admin_message')->insertAll([
        ['id' => 1, 'title' => '消息1', 'content' => '内容1', 'receiver' => '1', 'type' => 'notice', 'create_time' => $now, 'read_status' => 0],
        ['id' => 2, 'title' => '消息2', 'content' => '内容2', 'receiver' => '1', 'type' => 'system', 'create_time' => $now, 'read_status' => 0],
    ]);

    // 设定当前登录为超级管理员（id=1）
    setCsrfTestOperator(['id' => 1, 'username' => 'admin', 'role_id' => 1, 'status' => 1]);

    // ==================== 3. 5 个核心删除接口（+ 删除顺序确保 Role 先删子成员再删角色） ====================

    // 3.1 AdminManager::managerDelete
    bindCsrfTestRequest($app, 'GET', ['id' => 2], [], [], 'AdminManager', 'managerDelete');
    $managerCtrl = new AdminManager($app);
    assertMethodNotAllowedResponse($managerCtrl->managerDelete(), 'GET AdminManager::managerDelete');
    assertCsrfFixTrue(!empty(Db::name('user')->where('id', 2)->find()), 'GET managerDelete 不应删除管理员 id=2');

    // POST 仅在 Query 携带 id、POST body 为空时不得读取 GET 参数执行删除
    bindCsrfTestRequest($app, 'POST', ['id' => 2], [], [], 'AdminManager', 'managerDelete');
    $managerCtrl = new AdminManager($app);
    $managerQueryOnlyResp = $managerCtrl->managerDelete()->getData();
    assertCsrfFixSame(false, $managerQueryOnlyResp['status'] ?? null, 'POST 仅 Query 传 id 调用 managerDelete 应失败');
    assertCsrfFixTrue(!empty(Db::name('user')->where('id', 2)->find()), 'POST 仅 Query 传 id 不应删除管理员 id=2');

    // POST body 正常传 id=2 应成功删除
    bindCsrfTestRequest($app, 'POST', [], ['id' => 2], [], 'AdminManager', 'managerDelete');
    $managerCtrl = new AdminManager($app);
    $managerPostResp = $managerCtrl->managerDelete()->getData();
    assertCsrfFixSame(true, $managerPostResp['status'] ?? null, 'POST managerDelete 应成功删除管理员 id=2');
    assertCsrfFixTrue(empty(Db::name('user')->where('id', 2)->find()), 'POST managerDelete 后管理员 id=2 应已删除');

    // 3.2 Role::roleDelete（此时角色 2 已无下级成员）
    bindCsrfTestRequest($app, 'GET', ['id' => 2], [], [], 'Role', 'roleDelete');
    $roleCtrl = new Role($app);
    assertMethodNotAllowedResponse($roleCtrl->roleDelete(), 'GET Role::roleDelete');
    assertCsrfFixTrue(!empty(Db::name('role')->where('id', 2)->find()), 'GET roleDelete 不应删除角色 id=2');

    // POST 仅 Query 携带 id、POST body 为空时不得删除角色
    bindCsrfTestRequest($app, 'POST', ['id' => 2], [], [], 'Role', 'roleDelete');
    $roleCtrl = new Role($app);
    $roleQueryOnlyThrew = false;
    try {
        $roleCtrl->roleDelete();
    } catch (InvalidArgumentException $e) {
        $roleQueryOnlyThrew = $e->getMessage() === '请指定角色';
    }
    assertCsrfFixTrue($roleQueryOnlyThrew, 'POST 仅 Query 传 id 调用 roleDelete 应因 POST 未传 id 抛出请指定角色异常');
    assertCsrfFixTrue(!empty(Db::name('role')->where('id', 2)->find()), 'POST 仅 Query 传 id 不应删除角色 id=2');

    // POST body 正常传 id=2 应成功删除
    bindCsrfTestRequest($app, 'POST', [], ['id' => 2], [], 'Role', 'roleDelete');
    $roleCtrl = new Role($app);
    $rolePostResp = $roleCtrl->roleDelete()->getData();
    assertCsrfFixSame(true, $rolePostResp['status'] ?? null, 'POST roleDelete 应成功删除角色 id=2');
    assertCsrfFixTrue(empty(Db::name('role')->where('id', 2)->find()), 'POST roleDelete 后角色 id=2 应已删除');

    // 3.3 Menu::menuDelete
    bindCsrfTestRequest($app, 'GET', ['id' => 2], [], [], 'Menu', 'menuDelete');
    $menuCtrl = new Menu($app);
    assertMethodNotAllowedResponse($menuCtrl->menuDelete(), 'GET Menu::menuDelete');
    assertCsrfFixTrue(!empty(Db::name('menu')->where('id', 2)->find()), 'GET menuDelete 不应删除菜单 id=2');

    // POST 仅 Query 携带 id、POST body 为空时不得删除菜单 id=2
    bindCsrfTestRequest($app, 'POST', ['id' => 2], [], [], 'Menu', 'menuDelete');
    $menuCtrl = new Menu($app);
    $menuCtrl->menuDelete();
    assertCsrfFixTrue(!empty(Db::name('menu')->where('id', 2)->find()), 'POST 仅 Query 传 id 不应删除菜单 id=2');

    // POST body 正常传 id=2 应成功删除
    bindCsrfTestRequest($app, 'POST', [], ['id' => 2], [], 'Menu', 'menuDelete');
    $menuCtrl = new Menu($app);
    $menuPostResp = $menuCtrl->menuDelete()->getData();
    assertCsrfFixSame(true, $menuPostResp['status'] ?? null, 'POST menuDelete 应成功删除菜单 id=2');
    assertCsrfFixTrue(empty(Db::name('menu')->where('id', 2)->find()), 'POST menuDelete 后菜单 id=2 应已删除');

    // 3.4 Logs::deleteLoginLog 与 Logs::deleteAdminOperationLog
    foreach (['GET', 'PUT'] as $nonPostMethod) {
        bindCsrfTestRequest($app, $nonPostMethod, ['day' => 30], [], [], 'Logs', 'deleteLoginLog');
        $logsCtrl = new Logs($app);
        assertMethodNotAllowedResponse($logsCtrl->deleteLoginLog(), "{$nonPostMethod} Logs::deleteLoginLog");
        assertCsrfFixSame(2, Db::name('login_log')->count(), "{$nonPostMethod} 不应删除任何登录日志");

        bindCsrfTestRequest($app, $nonPostMethod, ['day' => 30], [], [], 'Logs', 'deleteAdminOperationLog');
        $logsCtrl = new Logs($app);
        assertMethodNotAllowedResponse($logsCtrl->deleteAdminOperationLog(), "{$nonPostMethod} Logs::deleteAdminOperationLog");
        assertCsrfFixSame(2, Db::name('operation_log')->count(), "{$nonPostMethod} 不应删除任何操作日志");
    }

    bindCsrfTestRequest($app, 'POST', [], ['day' => 30], [], 'Logs', 'deleteLoginLog');
    $logsCtrl = new Logs($app);
    $delLoginResp = $logsCtrl->deleteLoginLog()->getData();
    assertCsrfFixSame(true, $delLoginResp['status'] ?? null, 'POST deleteLoginLog 应成功');
    assertCsrfFixSame(1, Db::name('login_log')->count(), 'POST deleteLoginLog 应清理 40 天前日志并保留 5 天前日志');

    bindCsrfTestRequest($app, 'POST', [], ['day' => 30], [], 'Logs', 'deleteAdminOperationLog');
    $logsCtrl = new Logs($app);
    $delOpResp = $logsCtrl->deleteAdminOperationLog()->getData();
    assertCsrfFixSame(true, $delOpResp['status'] ?? null, 'POST deleteAdminOperationLog 应成功');
    assertCsrfFixSame(1, Db::name('operation_log')->count(), 'POST deleteAdminOperationLog 应清理 40 天前日志并保留 5 天前日志');

    // ==================== 4. 其余 admin 写接口 POST 限制验证 ====================

    // 4.1 Logs::adminOperationLogList（updateAdminOperationConfig 仅 POST，getList 保留 GET）
    $reqUpdateGet = bindCsrfTestRequest(
        $app,
        'GET',
        ['_action' => 'updateAdminOperationConfig', 'admin_operation_switch' => '0'],
        [],
        [],
        'Logs',
        'adminOperationLogList'
    );
    $logsCtrl = new Logs($app);
    assertMethodNotAllowedResponse($logsCtrl->adminOperationLogList($reqUpdateGet), 'GET updateAdminOperationConfig');
    assertCsrfFixSame(
        '1',
        Db::name('config')->where('varname', 'admin_operation_switch')->value('value'),
        'GET updateAdminOperationConfig 不应修改开关配置'
    );

    $reqUpdatePost = bindCsrfTestRequest(
        $app,
        'POST',
        [],
        ['_action' => 'updateAdminOperationConfig', 'admin_operation_switch' => '0'],
        [],
        'Logs',
        'adminOperationLogList'
    );
    $logsCtrl = new Logs($app);
    $updateConfigResp = $logsCtrl->adminOperationLogList($reqUpdatePost)->getData();
    assertCsrfFixSame(true, $updateConfigResp['status'] ?? null, 'POST updateAdminOperationConfig 应成功');
    assertCsrfFixSame(
        '0',
        Db::name('config')->where('varname', 'admin_operation_switch')->value('value'),
        'POST updateAdminOperationConfig 应将开关更新为 0'
    );

    $reqGetList = bindCsrfTestRequest($app, 'GET', ['_action' => 'getList'], [], [], 'Logs', 'adminOperationLogList');
    $logsCtrl = new Logs($app);
    $opListResp = $logsCtrl->adminOperationLogList($reqGetList)->getData();
    assertCsrfFixSame(true, $opListResp['status'] ?? null, 'GET adminOperationLogList?_action=getList 应保持正常');

    // 4.2 Cache::cache（匿名子类覆写 getRuntimeRootPath() 指向临时隔离目录）
    mkdir($tmpCleanRuntimeDir . 'cache', 0777, true);
    mkdir($tmpCleanRuntimeDir . 'log', 0777, true);
    mkdir($tmpCleanRuntimeDir . 'admin/temp', 0777, true);
    mkdir($tmpCleanRuntimeDir . 'admin/log', 0777, true);
    file_put_contents($tmpCleanRuntimeDir . 'cache/a.txt', 'cache');
    file_put_contents($tmpCleanRuntimeDir . 'log/b.log', 'log');
    file_put_contents($tmpCleanRuntimeDir . 'admin/temp/c.php', 'temp');
    file_put_contents($tmpCleanRuntimeDir . 'admin/log/d.log', 'applog');

    $isolatedCacheCtrl = new class($app, $tmpCleanRuntimeDir) extends CacheController {
        /** @var string */
        private $isolatedRoot;

        /**
         * 构造隔离运行时根目录的 Cache 控制器替身
         *
         * @param App    $app          应用实例
         * @param string $isolatedRoot 临时隔离根目录
         */
        public function __construct(App $app, string $isolatedRoot)
        {
            parent::__construct($app);
            $this->isolatedRoot = $isolatedRoot;
        }

        /**
         * 返回临时隔离的运行时目录
         *
         * @return string
         */
        protected function getRuntimeRootPath(): string
        {
            return $this->isolatedRoot;
        }
    };

    foreach (['site', 'template', 'logs', ['site']] as $badGetType) {
        bindCsrfTestRequest($app, 'GET', ['type' => $badGetType], [], [], 'Cache', 'cache');
        assertMethodNotAllowedResponse($isolatedCacheCtrl->cache(), 'GET Cache::cache type=' . var_export($badGetType, true));
    }
    assertCsrfFixTrue(is_file($tmpCleanRuntimeDir . 'cache/a.txt'), 'GET 不应清理 cache 目录');
    assertCsrfFixTrue(is_file($tmpCleanRuntimeDir . 'log/b.log'), 'GET 不应清理 log 目录');
    assertCsrfFixTrue(is_file($tmpCleanRuntimeDir . 'admin/temp/c.php'), 'GET 不应清理 temp 目录');
    assertCsrfFixTrue(is_file($tmpCleanRuntimeDir . 'admin/log/d.log'), 'GET 不应清理应用 log 目录');

    // GET 不带 type 仍应正常渲染页面
    bindCsrfTestRequest($app, 'GET', [], [], [], 'Cache', 'cache');
    $cacheViewHtml = $isolatedCacheCtrl->cache();
    assertCsrfFixTrue(
        is_string($cacheViewHtml) && strpos($cacheViewHtml, '缓存与日志维护') !== false,
        'GET 无 type 访问 Cache::cache 应正常渲染视图页面'
    );

    // POST 清理各类型缓存与日志
    foreach (['site', 'template', 'logs'] as $postCacheType) {
        bindCsrfTestRequest($app, 'POST', [], ['type' => $postCacheType], [], 'Cache', 'cache');
        $cachePostResp = $isolatedCacheCtrl->cache()->getData();
        assertCsrfFixSame(true, $cachePostResp['status'] ?? null, "POST Cache::cache type={$postCacheType} 应成功");
    }
    assertCsrfFixTrue(!is_file($tmpCleanRuntimeDir . 'cache/a.txt'), 'POST site 应清理临时 cache 目录文件');
    assertCsrfFixTrue(!is_file($tmpCleanRuntimeDir . 'admin/temp/c.php'), 'POST template 应清理临时 temp 目录文件');
    assertCsrfFixTrue(!is_file($tmpCleanRuntimeDir . 'log/b.log'), 'POST logs 应清理临时 log 目录文件');
    assertCsrfFixTrue(!is_file($tmpCleanRuntimeDir . 'admin/log/d.log'), 'POST logs 应清理临时应用 log 目录文件');

    // POST 非法 type 应返回 400 参数异常
    bindCsrfTestRequest($app, 'POST', [], ['type' => 'invalid_type'], [], 'Cache', 'cache');
    $cacheInvalidPostResp = $isolatedCacheCtrl->cache()->getData();
    assertCsrfFixSame(false, $cacheInvalidPostResp['status'] ?? null, 'POST 非法 type 应失败');
    assertCsrfFixSame(400, $cacheInvalidPostResp['code'] ?? null, 'POST 非法 type code 应为 400');
    assertCsrfFixSame('参数异常', $cacheInvalidPostResp['msg'] ?? null, 'POST 非法 type 应返回「参数异常」');

    // 4.3 Module::doInstallModule 与 Module::doUninstallModule
    bindCsrfTestRequest($app, 'GET', ['module' => 'demo'], [], [], 'Module', 'doInstallModule');
    $moduleCtrl = new Module($app);
    assertMethodNotAllowedResponse($moduleCtrl->doInstallModule(), 'GET Module::doInstallModule');

    bindCsrfTestRequest($app, 'GET', ['module' => 'demo'], [], [], 'Module', 'doUninstallModule');
    $moduleCtrl = new Module($app);
    assertMethodNotAllowedResponse($moduleCtrl->doUninstallModule(), 'GET Module::doUninstallModule');
    assertCsrfFixSame(0, Db::name('user_operate_log')->count(), 'GET 安装/卸载拦截后不得写入用户操作日志');

    bindCsrfTestRequest($app, 'POST', ['module' => 'demo'], [], [], 'Module', 'doInstallModule');
    $moduleCtrl = new Module($app);
    $installQueryOnly = $moduleCtrl->doInstallModule()->getData();
    assertCsrfFixSame(false, $installQueryOnly['status'] ?? null, 'POST 仅 Query 传 module 调用 doInstallModule 应失败');
    assertCsrfFixSame('参数异常', $installQueryOnly['msg'] ?? null, 'POST 仅 Query 传 module 应提示参数异常');

    bindCsrfTestRequest($app, 'POST', ['module' => 'demo'], [], [], 'Module', 'doUninstallModule');
    $moduleCtrl = new Module($app);
    $uninstallQueryOnly = $moduleCtrl->doUninstallModule()->getData();
    assertCsrfFixSame(false, $uninstallQueryOnly['status'] ?? null, 'POST 仅 Query 传 module 调用 doUninstallModule 应失败');
    assertCsrfFixSame('参数异常', $uninstallQueryOnly['msg'] ?? null, 'POST 仅 Query 传 module 应提示参数异常');
    assertCsrfFixSame(0, Db::name('user_operate_log')->count(), 'POST 仅 Query 传 module 不得写入用户操作日志');

    // 4.4 Config::doDeleteExtendField
    $reqDelExtGet = bindCsrfTestRequest($app, 'GET', ['fid' => 1], [], [], 'Config', 'doDeleteExtendField');
    $configCtrl = new ConfigController($app);
    assertMethodNotAllowedResponse($configCtrl->doDeleteExtendField($reqDelExtGet), 'GET Config::doDeleteExtendField');
    assertCsrfFixTrue(!empty(Db::name('config_field')->where('fid', 1)->find()), 'GET 不应删除扩展字段');
    assertCsrfFixTrue(!empty(Db::name('config')->where('varname', 'custom_ext_key')->find()), 'GET 不应删除关联配置项');

    $reqDelExtQueryOnly = bindCsrfTestRequest($app, 'POST', ['fid' => 1], [], [], 'Config', 'doDeleteExtendField');
    $configCtrl = new ConfigController($app);
    $delExtQueryOnlyResp = $configCtrl->doDeleteExtendField($reqDelExtQueryOnly)->getData();
    assertCsrfFixSame(false, $delExtQueryOnlyResp['status'] ?? null, 'POST 仅 Query 传 fid 调用 doDeleteExtendField 应失败');
    assertCsrfFixTrue(!empty(Db::name('config_field')->where('fid', 1)->find()), 'POST 仅 Query 传 fid 不应删除扩展字段');

    $reqDelExtPost = bindCsrfTestRequest($app, 'POST', [], ['fid' => 1], [], 'Config', 'doDeleteExtendField');
    $configCtrl = new ConfigController($app);
    $delExtResp = $configCtrl->doDeleteExtendField($reqDelExtPost)->getData();
    assertCsrfFixSame(true, $delExtResp['status'] ?? null, 'POST doDeleteExtendField 应成功');
    assertCsrfFixTrue(empty(Db::name('config_field')->where('fid', 1)->find()), 'POST 后应删除扩展字段');
    assertCsrfFixTrue(empty(Db::name('config')->where('varname', 'custom_ext_key')->find()), 'POST 后应删除关联配置项');

    // 4.5 AdminMessage::readMsg / readMsgAll / index / noRead / system / sendMessage
    bindCsrfTestRequest($app, 'GET', ['ids' => [1]], [], [], 'AdminMessage', 'readMsg');
    $msgCtrl = new AdminMessage($app);
    assertMethodNotAllowedResponse($msgCtrl->readMsg(), 'GET AdminMessage::readMsg');

    bindCsrfTestRequest($app, 'GET', [], [], [], 'AdminMessage', 'readMsgAll');
    $msgCtrl = new AdminMessage($app);
    assertMethodNotAllowedResponse($msgCtrl->readMsgAll(), 'GET AdminMessage::readMsgAll');

    foreach (['index', 'noRead', 'system'] as $msgEntry) {
        foreach (['readMsg', 'readMsgAll'] as $msgAction) {
            bindCsrfTestRequest(
                $app,
                'GET',
                ['_action' => $msgAction, 'ids' => [1]],
                [],
                [],
                'AdminMessage',
                $msgEntry
            );
            $msgCtrl = new AdminMessage($app);
            assertMethodNotAllowedResponse($msgCtrl->{$msgEntry}(), "GET AdminMessage::{$msgEntry}?_action={$msgAction}");
        }
    }
    assertCsrfFixSame(0, (int) Db::name('admin_message')->where('id', 1)->value('read_status'), 'GET 不应将消息 1 标为已读');
    assertCsrfFixSame(0, (int) Db::name('admin_message')->where('id', 2)->value('read_status'), 'GET 不应将消息 2 标为已读');

    // GET _action=getAdminMsgList 仍应正常工作
    bindCsrfTestRequest($app, 'GET', ['_action' => 'getAdminMsgList'], [], [], 'AdminMessage', 'index');
    $msgCtrl = new AdminMessage($app);
    $msgListResp = $msgCtrl->index()->getData();
    assertCsrfFixSame(true, $msgListResp['status'] ?? null, 'GET AdminMessage::index?_action=getAdminMsgList 应保持正常');

    // POST 仅 Query 传 ids、POST body 为空时不应标记消息 1 已读
    bindCsrfTestRequest($app, 'POST', ['ids' => [1]], [], [], 'AdminMessage', 'readMsg');
    $msgCtrl = new AdminMessage($app);
    $msgCtrl->readMsg();
    assertCsrfFixSame(0, (int) Db::name('admin_message')->where('id', 1)->value('read_status'), 'POST 仅 Query 传 ids 不应修改消息 1 状态');

    // POST body 正常标记 id=1 已读，再全部已读
    bindCsrfTestRequest($app, 'POST', [], ['ids' => [1]], [], 'AdminMessage', 'readMsg');
    $msgCtrl = new AdminMessage($app);
    $readMsgResp = $msgCtrl->readMsg()->getData();
    assertCsrfFixSame(true, $readMsgResp['status'] ?? null, 'POST readMsg 应成功');
    assertCsrfFixSame(1, (int) Db::name('admin_message')->where('id', 1)->value('read_status'), 'POST readMsg 应将消息 1 标为已读');
    assertCsrfFixSame(0, (int) Db::name('admin_message')->where('id', 2)->value('read_status'), 'POST readMsg 不应影响消息 2');

    bindCsrfTestRequest($app, 'POST', [], [], [], 'AdminMessage', 'readMsgAll');
    $msgCtrl = new AdminMessage($app);
    $readMsgAllResp = $msgCtrl->readMsgAll()->getData();
    assertCsrfFixSame(true, $readMsgAllResp['status'] ?? null, 'POST readMsgAll 应成功');
    assertCsrfFixSame(1, (int) Db::name('admin_message')->where('id', 2)->value('read_status'), 'POST readMsgAll 应将消息 2 标为已读');

    // AdminMessage::sendMessage
    bindCsrfTestRequest($app, 'GET', ['_action' => 'createMessage', 'title' => 'GET注入'], [], [], 'AdminMessage', 'sendMessage');
    $msgCtrl = new AdminMessage($app);
    assertMethodNotAllowedResponse($msgCtrl->sendMessage(), 'GET AdminMessage::sendMessage?_action=createMessage');
    assertCsrfFixSame(2, Db::name('admin_message')->count(), 'GET createMessage 不得新增消息记录');

    bindCsrfTestRequest($app, 'GET', [], [], [], 'AdminMessage', 'sendMessage');
    $msgCtrl = new AdminMessage($app);
    $sendMsgViewHtml = $msgCtrl->sendMessage();
    assertCsrfFixTrue(
        is_string($sendMsgViewHtml) && strpos($sendMsgViewHtml, 'createMessage') !== false,
        'GET 无 _action=createMessage 访问 sendMessage 应正常渲染视图页面'
    );

    bindCsrfTestRequest($app, 'POST', [], [
        '_action'       => 'createMessage',
        'title'         => 'POST通知',
        'content'       => 'POST消息内容',
        'receiver'      => '1',
        'sender'        => '1',
        'sender_type'   => 'admin',
        'target'        => '1',
        'target_type'   => 'system',
        'receiver_type' => 'admin',
        'type'          => 'notice',
    ], [], 'AdminMessage', 'sendMessage');
    $msgCtrl = new AdminMessage($app);
    $sendMsgPostResp = $msgCtrl->sendMessage()->getData();
    assertCsrfFixSame(true, $sendMsgPostResp['status'] ?? null, 'POST sendMessage createMessage 应成功');
    assertCsrfFixSame(3, Db::name('admin_message')->count(), 'POST sendMessage createMessage 应写入第 3 条消息');

    // 4.6 Login::doLogin 与 Login::doLogout
    $validLoginForm = [
        'username' => base64_encode(urlencode('admin')),
        'password' => base64_encode(urlencode('Secret123')),
        'code'     => 'abcd',
    ];
    setCsrfTestOperator([]);
    bindCsrfTestRequest($app, 'GET', ['form' => $validLoginForm], [], [], 'Login', 'doLogin');
    $loginCtrl = new Login($app);
    assertMethodNotAllowedResponse($loginCtrl->doLogin(), 'GET Login::doLogin');
    assertCsrfFixSame(0, (int) AdminUserService::getInstance()->isLogin(), 'GET doLogin 不应产生登录态');
    assertCsrfFixSame(1, Db::name('login_log')->count(), 'GET doLogin 不应写入登录日志');

    bindCsrfTestRequest($app, 'POST', ['form' => $validLoginForm], [], [], 'Login', 'doLogin');
    $loginCtrl = new Login($app);
    $loginQueryOnlyResp = $loginCtrl->doLogin()->getData();
    assertCsrfFixSame(false, $loginQueryOnlyResp['status'] ?? null, 'POST 仅 Query 传 form 调用 doLogin 应失败');
    assertCsrfFixSame('用户名或者密码不能为空，请重新输入', $loginQueryOnlyResp['msg'] ?? null, 'POST 仅 Query 传 form 应提示凭据为空');

    // Login::doLogout
    setCsrfTestOperator(['id' => 1, 'username' => 'admin', 'role_id' => 1, 'status' => 1]);
    bindCsrfTestRequest($app, 'GET', [], [], [], 'Login', 'doLogout');
    $loginCtrl = new Login($app);
    assertMethodNotAllowedResponse($loginCtrl->doLogout(), 'GET Login::doLogout');
    assertCsrfFixSame(1, (int) AdminUserService::getInstance()->isLogin(), 'GET doLogout 不得清除已登录会话');

    bindCsrfTestRequest($app, 'POST', [], [], [], 'Login', 'doLogout');
    $loginCtrl = new Login($app);
    $logoutPostResp = $loginCtrl->doLogout()->getData();
    assertCsrfFixSame(true, $logoutPostResp['status'] ?? null, 'POST doLogout 应成功');
    assertCsrfFixSame('注销成功', $logoutPostResp['msg'] ?? null, 'POST doLogout 文案应为「注销成功」');
    assertCsrfFixSame(0, (int) AdminUserService::getInstance()->isLogin(), 'POST doLogout 后应清除会话登录态');

    // ==================== 5. 前端 dashboard/index.php doLogout 调用检查 ====================
    $dashboardView = file_get_contents(dirname(__DIR__) . '/app/admin/view/dashboard/index.php');
    assertCsrfFixTrue($dashboardView !== false, '应能读取 dashboard/index.php');
    $logoutPos = strpos($dashboardView, 'click_logout:');
    assertCsrfFixTrue($logoutPos !== false, 'dashboard/index.php 应包含 click_logout 定义');
    $logoutSnippet = substr($dashboardView, $logoutPos, 400);
    assertCsrfFixTrue(
        strpos($logoutSnippet, "doLogout") !== false
        && strpos($logoutSnippet, "method: 'post'") !== false
        && strpos($logoutSnippet, "method: 'get'") === false,
        'dashboard/index.php 中 click_logout 必须使用 POST 调用 doLogout'
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    setCsrfTestOperator([]);
    restore_error_handler();
    removeTempDirRecursive($tmpCacheDir);
    removeTempDirRecursive($tmpRuntimeDir);
    removeTempDirRecursive($tmpCleanRuntimeDir);
}

echo "admin_csrf_post_only_samesite_test passed\n";
