<?php

use app\admin\controller\Logs;
use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use app\admin\service\AdminUserService;
use app\common\libs\helper\PasswordHelper;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
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
function assertLogSanitizationTrue(bool $condition, string $message): void
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
function assertLogSanitizationSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 绑定模拟请求实例到应用容器
 *
 * @param App $app 应用实例
 * @param string $method 请求方法
 * @param array $options 请求选项（controller、action、base_url、url、pathinfo、get、post）
 * @return Request
 */
function bindLogTestRequest(App $app, string $method, array $options = []): Request
{
    $request = new Request();
    $server = [
        'REQUEST_METHOD' => strtoupper($method),
        'REMOTE_ADDR' => '127.0.0.1',
    ];
    if (!empty($options['url'])) {
        $server['REQUEST_URI'] = $options['url'];
    }
    if (!empty($options['pathinfo'])) {
        $server['PATH_INFO'] = $options['pathinfo'];
    }

    $request->withServer($server);
    $request->setMethod(strtoupper($method));

    if (isset($options['controller'])) {
        $request->setController($options['controller']);
    }
    if (isset($options['action'])) {
        $request->setAction($options['action']);
    }
    if (isset($options['base_url'])) {
        $request->setBaseUrl($options['base_url']);
    }
    if (isset($options['get'])) {
        $request->withGet($options['get']);
    }
    if (isset($options['post'])) {
        $request->withPost($options['post']);
    }

    $app->instance('request', $request);

    return $request;
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_admin_log_sanitization_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // ==================== 1. 源码与视图静态检查 ====================
    $loginLogView = file_get_contents(dirname(__DIR__) . '/app/admin/view/logs/loginLogList.php');
    assertLogSanitizationTrue(
        $loginLogView !== false
        && strpos($loginLogView, 'prop="password"') === false
        && strpos($loginLogView, "prop='password'") === false,
        'loginLogList.php 视图不应再展示 password 列'
    );

    $adminUserServiceSource = file_get_contents(dirname(__DIR__) . '/app/admin/service/AdminUserService.php');
    assertLogSanitizationTrue(
        $adminUserServiceSource !== false
        && strpos($adminUserServiceSource, '密码保密') === false,
        'AdminUserService.php 不应再包含“密码保密”占位逻辑'
    );

    configureIsolatedTestFileCache($tmpCacheDir, ['prefix' => 'test_']);

    Config::set([
        'default' => 'admin_log_sanitization_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_log_sanitization_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'admin_operation_switch',
        'info' => '是否启用后台操作日志',
        'groupid' => 1,
        'value' => '1',
    ]);

    Db::execute('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        nickname VARCHAR(64) NOT NULL DEFAULT "",
        password VARCHAR(64) NOT NULL DEFAULT "",
        verify VARCHAR(32) NOT NULL DEFAULT "",
        email VARCHAR(128) NOT NULL DEFAULT "",
        avatar VARCHAR(255) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT "",
        role_id INTEGER NOT NULL DEFAULT 1,
        info VARCHAR(255) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        last_login_ip VARCHAR(64) NOT NULL DEFAULT "",
        last_login_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE operation_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        time INTEGER NOT NULL DEFAULT 0,
        ip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        method VARCHAR(32) NOT NULL DEFAULT "",
        url VARCHAR(512) NOT NULL DEFAULT "",
        params TEXT NOT NULL DEFAULT "",
        response TEXT NOT NULL DEFAULT ""
    )');

    $verifySalt = 'abc123';
    $correctPassword = 'CorrectSecretPass!99';
    Db::name('user')->insert([
        'id' => 1,
        'username' => 'admin',
        'nickname' => '超级管理员',
        'verify' => $verifySalt,
        'password' => PasswordHelper::hashPassword($correctPassword, $verifySalt),
        'email' => 'admin@test.com',
        'role_id' => 1,
        'status' => 1,
    ]);

    // ==================== 2. 问题 3：登录日志不再记录密码 ====================
    bindLogTestRequest($app, 'POST', ['url' => '/admin/Login/doLogin']);
    $wrongPassword = 'WrongPlainPassword!';
    $failResult = AdminUserService::getInstance()->login('admin', $wrongPassword);
    assertLogSanitizationSame(false, $failResult, '错误密码登录应返回 false');

    $okResult = AdminUserService::getInstance()->login('admin', $correctPassword);
    assertLogSanitizationSame(true, $okResult, '正确密码登录应返回 true');

    $loginLogs = Db::name('login_log')->order('id', 'asc')->select()->toArray();
    assertLogSanitizationSame(2, count($loginLogs), '应生成 2 条登录日志');
    assertLogSanitizationSame(0, (int) $loginLogs[0]['status'], '第 1 条登录日志应为失败状态');
    assertLogSanitizationSame(1, (int) $loginLogs[1]['status'], '第 2 条登录日志应为成功状态');
    foreach ($loginLogs as $idx => $row) {
        assertLogSanitizationSame('', (string) $row['password'], "第 {$idx} 条登录日志 password 列必须为空");
        $rowJson = (string) json_encode($row, JSON_UNESCAPED_UNICODE);
        assertLogSanitizationTrue(strpos($rowJson, $wrongPassword) === false, '登录日志不应包含错误明文密码');
        assertLogSanitizationTrue(strpos($rowJson, $correctPassword) === false, '登录日志不应包含正确明文密码');
        assertLogSanitizationTrue(strpos($rowJson, '密码保密') === false, '登录日志不应包含“密码保密”字样');
    }

    // 直接调用 addLoginLog 传入 password 也必须被剥离
    $loginLogModel = new LoginlogModel();
    $added = $loginLogModel->addLoginLog([
        'username' => 'admin',
        'status' => 0,
        'password' => 'ShouldBeStripped',
        'info' => 'direct call test',
    ]);
    assertLogSanitizationSame(true, $added, 'addLoginLog 直接调用应成功');
    $lastLoginLog = Db::name('login_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('', (string) ($lastLoginLog['password'] ?? ''), 'addLoginLog 不得写入传入的 password 字段');

    // 通过反射调用 record（分别验证 2 参数和兼容旧版 3 参数调用）
    $serviceInstance = AdminUserService::getInstance();
    $recordMethod = new ReflectionMethod(AdminUserService::class, 'record');
    $recordMethod->setAccessible(true);
    $recordMethod->invoke($serviceInstance, 'admin_2arg', 0);
    $recordMethod->invoke($serviceInstance, 'admin_3arg', 'LegacySecret', 1);

    $twoArgRow = Db::name('login_log')->where('username', 'admin_2arg')->find();
    assertLogSanitizationSame(0, (int) $twoArgRow['status'], '2 参数调用 record 应记录 status=0');
    assertLogSanitizationSame('', (string) $twoArgRow['password'], '2 参数调用 record 不得写入密码');

    $threeArgRow = Db::name('login_log')->where('username', 'admin_3arg')->find();
    assertLogSanitizationSame(1, (int) $threeArgRow['status'], '3 参数兼容调用 record 应正确记录第 3 参数 status=1');
    assertLogSanitizationSame('', (string) $threeArgRow['password'], '3 参数兼容调用 record 不得写入密码');

    // ==================== 3. 问题 4：登录接口不记录到操作日志 ====================
    $okResponse = Response::create(json_encode(['status' => true, 'code' => 200, 'msg' => '登录成功']), 'html', 200);
    $loginPayload = [
        'form' => [
            'username' => base64_encode('admin'),
            'password' => base64_encode($correctPassword),
            'code' => '1234',
        ],
    ];

    // 3.1 通过 controller/action 识别 Login::doLogin
    bindLogTestRequest($app, 'POST', [
        'controller' => 'Login',
        'action' => 'doLogin',
        'url' => '/admin/Login/doLogin',
        'post' => $loginPayload,
    ]);
    assertLogSanitizationSame(false, OperationlogModel::record($okResponse), 'controller/action 为 Login/doLogin 时应跳过操作日志记录');

    // 3.2 仅通过 URL 路径识别 /admin/Login/doLogin（即使未注入 controller/action）
    bindLogTestRequest($app, 'POST', [
        'base_url' => '/admin/Login/doLogin',
        'url' => '/admin/Login/doLogin',
        'post' => $loginPayload,
    ]);
    assertLogSanitizationSame(false, OperationlogModel::record($okResponse), 'URL 为 /admin/Login/doLogin 时应跳过操作日志记录');

    // 3.3 兼容 index.php?s=/admin/login/dologin 路由形式
    bindLogTestRequest($app, 'POST', [
        'url' => '/index.php?s=/admin/login/dologin',
        'post' => $loginPayload,
    ]);
    assertLogSanitizationSame(false, OperationlogModel::record($okResponse), '兼容 s=/admin/login/dologin 路由时应跳过操作日志记录');

    assertLogSanitizationSame(0, (int) Db::name('operation_log')->count(), '登录接口调用后 operation_log 应保持为空');

    // 3.4 普通业务 POST 携带登录跳转查询参数时，必须正常记录操作日志
    bindLogTestRequest($app, 'POST', [
        'controller' => 'Config',
        'action' => 'index',
        'base_url' => '/admin/Config/index',
        'url' => '/admin/Config/index?redirect=/admin/Login/doLogin&next=/admin/login/dologin',
        'post' => ['sitename' => 'demo'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '已解析非登录控制器时携带 login/doLogin 跳转参数仍应记录操作日志');

    bindLogTestRequest($app, 'POST', [
        'base_url' => '/admin/Config/index',
        'url' => '/admin/Config/index?redirect=/admin/Login/doLogin',
        'post' => ['sitename' => 'demo2'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '未解析控制器但路径为 /admin/Config/index 且查询参数含 login/doLogin 时仍应记录操作日志');

    bindLogTestRequest($app, 'POST', [
        'url' => '/index.php?s=/admin/Config/index&redirect=/admin/Login/doLogin',
        'post' => ['sitename' => 'demo3'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '未解析控制器但 s 路由为 /admin/Config/index 且查询参数含 login/doLogin 时仍应记录操作日志');

    bindLogTestRequest($app, 'POST', [
        'url' => '/admin/Config/index/redirect/login/dologin',
        'post' => ['sitename' => 'demo4'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '未解析控制器但普通路径额外参数含 login/dologin 时仍应记录操作日志');

    assertLogSanitizationSame(4, (int) Db::name('operation_log')->count(), '4 次携带登录跳转参数的普通 POST 均应写入 operation_log');
    Db::execute('DELETE FROM operation_log');

    // ==================== 4. 问题 4：操作日志敏感参数递归脱敏 ====================
    $sensitiveCases = [
        [
            'name' => 'changePassword',
            'url' => '/admin/AdminManager/changePassword',
            'controller' => 'AdminManager',
            'action' => 'changePassword',
            'post' => [
                'password' => 'OldPass_Secret_1',
                'new_password' => 'NewPass_Secret_2',
                'new_pwdconfirm' => 'NewPass_Secret_2',
            ],
            'secrets' => ['OldPass_Secret_1', 'NewPass_Secret_2'],
            'keep' => [],
        ],
        [
            'name' => 'managerAdd',
            'url' => '/admin/AdminManager/managerAdd',
            'controller' => 'AdminManager',
            'action' => 'managerAdd',
            'post' => [
                'username' => 'editor01',
                'nickname' => '内容编辑',
                'password' => 'ManagerAddPass!1',
                'pwdconfirm' => 'ManagerAddPass!1',
                'role_id' => '2',
            ],
            'secrets' => ['ManagerAddPass!1'],
            'keep' => ['username' => 'editor01', 'nickname' => '内容编辑', 'role_id' => '2'],
        ],
        [
            'name' => 'managerEdit',
            'url' => '/admin/AdminManager/managerEdit',
            'controller' => 'AdminManager',
            'action' => 'managerEdit',
            'post' => [
                'id' => '2',
                'username' => 'editor01',
                'password' => 'ManagerEditPass!2',
                'pwdconfirm' => 'ManagerEditPass!2',
            ],
            'secrets' => ['ManagerEditPass!2'],
            'keep' => ['id' => '2', 'username' => 'editor01'],
        ],
        [
            'name' => 'emailConfig',
            'url' => '/common/email.Email/config',
            'controller' => 'email.Email',
            'action' => 'config',
            'post' => [
                'mail_server' => 'smtp.example.com',
                'mail_user' => 'noreply@example.com',
                'mail_password' => 'SmtpPass!Secret',
            ],
            'secrets' => ['SmtpPass!Secret'],
            'keep' => ['mail_server' => 'smtp.example.com', 'mail_user' => 'noreply@example.com'],
        ],
        [
            'name' => 'uploadSetting',
            'url' => '/common/upload.Upload/setting',
            'controller' => 'upload.Upload',
            'action' => 'setting',
            'post' => [
                'attachment_driver' => 'Aliyun',
                'attachment_aliyun_key_id' => 'LTAI5tDemoKeyId',
                'attachment_aliyun_key_secret' => 'AliyunSecretValue!',
                'attachment_qiniu_secret_key' => 'QiniuSecretValue!',
            ],
            'secrets' => ['AliyunSecretValue!', 'QiniuSecretValue!'],
            'keep' => ['attachment_driver' => 'Aliyun', 'attachment_aliyun_key_id' => 'LTAI5tDemoKeyId'],
        ],
        [
            'name' => 'nestedArrayAndJson',
            'url' => '/admin/Config/extend',
            'controller' => 'Config',
            'action' => 'extend',
            'post' => [
                'sitename' => 'ZTBCMS站点',
                'extra' => [
                    'user_pwd' => 'NestedPwd!123',
                    'repassword' => 'NestedPwd!123',
                    'note' => '普通备注',
                ],
                'json_payload' => json_encode([
                    'access_token' => 'InnerAccessToken!999',
                    'app_secret' => 'InnerAppSecret!888',
                    'title' => '保留标题',
                ], JSON_UNESCAPED_UNICODE),
            ],
            'secrets' => ['NestedPwd!123', 'InnerAccessToken!999', 'InnerAppSecret!888'],
            'keep' => ['sitename' => 'ZTBCMS站点'],
        ],
    ];

    foreach ($sensitiveCases as $case) {
        bindLogTestRequest($app, 'POST', [
            'controller' => $case['controller'],
            'action' => $case['action'],
            'base_url' => $case['url'],
            'url' => $case['url'],
            'post' => $case['post'],
        ]);
        $recorded = OperationlogModel::record($okResponse);
        assertLogSanitizationSame(true, $recorded, $case['name'] . ' 应写入操作日志');

        $latestOpLog = Db::name('operation_log')->order('id', 'desc')->find();
        $paramsRaw = (string) ($latestOpLog['params'] ?? '');
        foreach ($case['secrets'] as $secret) {
            assertLogSanitizationTrue(
                strpos($paramsRaw, $secret) === false,
                $case['name'] . " 操作日志 params 不应包含原始明文密码 {$secret}"
            );
        }

        $decoded = json_decode($paramsRaw, true);
        assertLogSanitizationTrue(is_array($decoded), $case['name'] . ' params 应为合法 JSON');
        foreach ($case['keep'] as $keepKey => $keepVal) {
            assertLogSanitizationSame($keepVal, $decoded[$keepKey] ?? null, $case['name'] . " 普通字段 {$keepKey} 应保持原值");
        }
    }

    // 针对特定字段检查脱敏占位符 ******
    $changePassRow = Db::name('operation_log')->where('url', '/admin/AdminManager/changePassword')->find();
    $changePassParams = json_decode((string) $changePassRow['params'], true);
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $changePassParams['password'] ?? '', 'password 应脱敏为 ******');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $changePassParams['new_password'] ?? '', 'new_password 应脱敏为 ******');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $changePassParams['new_pwdconfirm'] ?? '', 'new_pwdconfirm 应脱敏为 ******');

    $nestedRow = Db::name('operation_log')->where('url', '/admin/Config/extend')->find();
    $nestedParams = json_decode((string) $nestedRow['params'], true);
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $nestedParams['extra']['user_pwd'] ?? '', '嵌套数组 user_pwd 应脱敏');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $nestedParams['extra']['repassword'] ?? '', '嵌套数组 repassword 应脱敏');
    assertLogSanitizationSame('普通备注', $nestedParams['extra']['note'] ?? '', '嵌套数组普通字段 note 应保留');
    $innerJsonDecoded = json_decode((string) ($nestedParams['json_payload'] ?? ''), true);
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $innerJsonDecoded['access_token'] ?? '', '内嵌 JSON 的 access_token 应脱敏');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $innerJsonDecoded['app_secret'] ?? '', '内嵌 JSON 的 app_secret 应脱敏');
    assertLogSanitizationSame('保留标题', $innerJsonDecoded['title'] ?? '', '内嵌 JSON 的 title 应保留');

    // 4.1 URL 查询参数携带密码时，记录时应去除敏感查询参数并保留 s 路由标识
    bindLogTestRequest($app, 'POST', [
        'controller' => 'AdminManager',
        'action' => 'changePassword',
        'base_url' => '/admin/AdminManager/changePassword',
        'url' => '/admin/AdminManager/changePassword?password=UrlQueryOldPass!&new_password=UrlQueryNewPass!',
        'post' => ['new_pwdconfirm' => 'UrlQueryNewPass!'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '带查询参数密码的 changePassword 应记录脱敏日志');
    $urlSanitizedRow = Db::name('operation_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('/admin/AdminManager/changePassword', (string) ($urlSanitizedRow['url'] ?? ''), '普通路由记录的 url 应去除查询字符串');
    $urlSanitizedRowJson = (string) json_encode($urlSanitizedRow, JSON_UNESCAPED_UNICODE);
    assertLogSanitizationTrue(strpos($urlSanitizedRowJson, 'UrlQueryOldPass!') === false, '日志整行不应包含 URL 中的 UrlQueryOldPass!');
    assertLogSanitizationTrue(strpos($urlSanitizedRowJson, 'UrlQueryNewPass!') === false, '日志整行不应包含 URL 中的 UrlQueryNewPass!');

    bindLogTestRequest($app, 'POST', [
        'controller' => 'AdminManager',
        'action' => 'changePassword',
        'url' => '/index.php?s=/admin/AdminManager/changePassword&password=UrlQueryOldPass2!&new_password=UrlQueryNewPass2!',
        'post' => ['new_pwdconfirm' => 'UrlQueryNewPass2!'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), 's 路由带查询参数密码的 changePassword 应记录脱敏日志');
    $sRouteSanitizedRow = Db::name('operation_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('/index.php?s=/admin/AdminManager/changePassword', (string) ($sRouteSanitizedRow['url'] ?? ''), 's 路由记录的 url 应仅保留 s 路由路径标识');
    $sRouteSanitizedRowJson = (string) json_encode($sRouteSanitizedRow, JSON_UNESCAPED_UNICODE);
    assertLogSanitizationTrue(strpos($sRouteSanitizedRowJson, 'UrlQueryOldPass2!') === false, 's 路由日志整行不应包含 URL 中的 UrlQueryOldPass2!');
    assertLogSanitizationTrue(strpos($sRouteSanitizedRowJson, 'UrlQueryNewPass2!') === false, 's 路由日志整行不应包含 URL 中的 UrlQueryNewPass2!');

    // 4.2 URL 路径（含普通路径及 s 兼容模式）携带额外路由参数密码时，仅保留应用/控制器/操作标识
    bindLogTestRequest($app, 'POST', [
        'controller' => 'AdminManager',
        'action' => 'changePassword',
        'base_url' => '/admin/AdminManager/changePassword/password/PathOldPass1!/new_password/PathNewPass1!/new_pwdconfirm/PathNewPass1!',
        'url' => '/admin/AdminManager/changePassword/password/PathOldPass1!/new_password/PathNewPass1!/new_pwdconfirm/PathNewPass1!',
        'post' => ['new_pwdconfirm' => 'PathNewPass1!'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '路径路由参数带密码的 changePassword 应记录脱敏日志');
    $pathParamRow = Db::name('operation_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('/admin/AdminManager/changePassword', (string) ($pathParamRow['url'] ?? ''), '普通路径应剥离操作名之后的额外路由参数');
    $pathParamRowJson = (string) json_encode($pathParamRow, JSON_UNESCAPED_UNICODE);
    assertLogSanitizationTrue(strpos($pathParamRowJson, 'PathOldPass1!') === false, '日志整行不应包含路径路由参数中的 PathOldPass1!');
    assertLogSanitizationTrue(strpos($pathParamRowJson, 'PathNewPass1!') === false, '日志整行不应包含路径路由参数中的 PathNewPass1!');

    bindLogTestRequest($app, 'POST', [
        'controller' => 'AdminManager',
        'action' => 'changePassword',
        'url' => '/index.php?s=/admin/AdminManager/changePassword/password/PathOldPass2!/new_password/PathNewPass2!',
        'post' => ['new_pwdconfirm' => 'PathNewPass2!'],
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), 's 路由路径参数带密码的 changePassword 应记录脱敏日志');
    $sPathParamRow = Db::name('operation_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('/index.php?s=/admin/AdminManager/changePassword', (string) ($sPathParamRow['url'] ?? ''), 's 路由应剥离操作名之后的额外路由参数并保留 s 路由标识');
    $sPathParamRowJson = (string) json_encode($sPathParamRow, JSON_UNESCAPED_UNICODE);
    assertLogSanitizationTrue(strpos($sPathParamRowJson, 'PathOldPass2!') === false, 's 路由日志整行不应包含路径路由参数中的 PathOldPass2!');
    assertLogSanitizationTrue(strpos($sPathParamRowJson, 'PathNewPass2!') === false, 's 路由日志整行不应包含路径路由参数中的 PathNewPass2!');

    // 无 request 兜底时：多应用动作名 changePassword 不得被误截，单应用路径敏感参数段应被截断
    assertLogSanitizationSame('/admin/AdminManager/changePassword', OperationlogModel::sanitizeUrl('/admin/AdminManager/changePassword'), '无 request 时多应用 action changePassword 不应被误截断');
    assertLogSanitizationSame('/AdminManager/changePassword', OperationlogModel::sanitizeUrl('/AdminManager/changePassword/password/Leak123'), '无 request 时单应用路径敏感参数段应被截断');

    bindLogTestRequest($app, 'POST', [
        'controller' => 'Config',
        'action' => 'index',
        'url' => '/admin/Config/index%7Cid%2F123',
    ]);
    assertLogSanitizationSame(true, OperationlogModel::record($okResponse), '使用编码兼容分隔符的普通 POST 应写入操作日志');
    $compatRouteRow = Db::name('operation_log')->order('id', 'desc')->find();
    assertLogSanitizationSame('/admin/Config/index', (string) ($compatRouteRow['url'] ?? ''), '新日志应剥离编码兼容分隔符后的路由参数');

    // ==================== 5. 列表接口读取兜底脱敏测试 ====================
    Db::execute('DELETE FROM login_log');
    Db::execute('DELETE FROM operation_log');

    $now = time();
    Db::name('login_log')->insertAll([
        [
            'username' => 'admin',
            'logintime' => $now - 100,
            'loginip' => '127.0.0.1',
            'status' => 0,
            'password' => 'OldLeakPass123',
            'info' => '用户名登录',
        ],
        [
            'username' => 'admin',
            'logintime' => $now - 50,
            'loginip' => '127.0.0.1',
            'status' => 1,
            'password' => '密码保密',
            'info' => '用户名登录',
        ],
    ]);

    Db::name('operation_log')->insertAll([
        [
            'uid' => 0,
            'time' => $now - 100,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/admin/Login/doLogin',
            'params' => json_encode(['form' => ['username' => 'YWRtaW4=', 'password' => 'bGVha2VkX3Bhc3M=']]),
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 90,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/admin/AdminManager/changePassword?password=HistUrlPass123',
            'params' => json_encode(['password' => 'old_hist_pass', 'new_password' => 'new_hist_pass', 'new_pwdconfirm' => 'new_hist_pass']),
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 85,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/index.php?s=/admin/AdminManager/changePassword&password=HistUrlPass456',
            'params' => json_encode(['password' => 'old_hist_pass2']),
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 83,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/admin/AdminManager/changePassword/password/HistPathPass789/new_password/HistPathPass789',
            'params' => json_encode(['password' => 'old_hist_pass3']),
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 82,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/index.php?s=/admin/AdminManager/changePassword/password/HistSPathPass999',
            'params' => json_encode(['password' => 'old_hist_pass4']),
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 80,
            'ip' => '127.0.0.1',
            'status' => 1,
            'method' => 'POST',
            'url' => '/index.php?s=/admin/Config/index|id|123',
            'params' => '{"sitename":"ztbcms"}',
            'response' => '{"status":true}',
        ],
        [
            'uid' => 1,
            'time' => $now - 70,
            'ip' => '127.0.0.1',
            'status' => 0,
            'method' => 'POST',
            'url' => '/admin/AdminManager/managerAdd',
            'params' => 'broken_raw_payload_password=raw_secret_leak',
            'response' => '{"status":false}',
        ],
    ]);

    $getLoginListReq = bindLogTestRequest($app, 'GET', [
        'get' => ['_action' => 'getList', 'page' => 1, 'limit' => 20],
    ]);
    $logsController = new Logs($app);
    $loginListResp = $logsController->loginLogList($getLoginListReq)->getData();
    assertLogSanitizationSame(true, $loginListResp['status'], 'loginLogList 应查询成功');
    $loginItems = $loginListResp['data']['items']->toArray();
    assertLogSanitizationSame(2, count($loginItems), 'loginLogList 应返回 2 条数据');
    foreach ($loginItems as $item) {
        assertLogSanitizationTrue(!array_key_exists('password', $item), 'loginLogList 接口返回条目不得包含 password 字段');
        $encodedItem = (string) json_encode($item, JSON_UNESCAPED_UNICODE);
        assertLogSanitizationTrue(strpos($encodedItem, 'OldLeakPass123') === false, 'loginLogList 接口返回不得包含历史明文密码');
    }

    $getOpListReq = bindLogTestRequest($app, 'GET', [
        'get' => ['_action' => 'getList', 'page' => 1, 'limit' => 20],
    ]);
    $logsController = new Logs($app);
    $opListResp = $logsController->adminOperationLogList($getOpListReq)->getData();
    assertLogSanitizationSame(true, $opListResp['status'], 'adminOperationLogList 应查询成功');
    $opItems = $opListResp['data']['items']->toArray();
    assertLogSanitizationSame(7, count($opItems), 'adminOperationLogList 应返回 7 条数据');
    $opItemsJson = (string) json_encode($opItems, JSON_UNESCAPED_UNICODE);
    assertLogSanitizationTrue(strpos($opItemsJson, 'bGVha2VkX3Bhc3M=') === false, 'adminOperationLogList 不应暴露历史 doLogin 编码密码');
    assertLogSanitizationTrue(strpos($opItemsJson, 'old_hist_pass') === false, 'adminOperationLogList 不应暴露历史 old_hist_pass');
    assertLogSanitizationTrue(strpos($opItemsJson, 'new_hist_pass') === false, 'adminOperationLogList 不应暴露历史 new_hist_pass');
    assertLogSanitizationTrue(strpos($opItemsJson, 'raw_secret_leak') === false, 'adminOperationLogList 不应暴露损坏日志中的 raw_secret_leak');
    assertLogSanitizationTrue(strpos($opItemsJson, 'HistUrlPass123') === false, 'adminOperationLogList 不应暴露 URL 查询参数中的 HistUrlPass123');
    assertLogSanitizationTrue(strpos($opItemsJson, 'HistUrlPass456') === false, 'adminOperationLogList 不应暴露 URL 查询参数中的 HistUrlPass456');
    assertLogSanitizationTrue(strpos($opItemsJson, 'HistPathPass789') === false, 'adminOperationLogList 不应暴露路径路由参数中的 HistPathPass789');
    assertLogSanitizationTrue(strpos($opItemsJson, 'HistSPathPass999') === false, 'adminOperationLogList 不应暴露 s 路由路径参数中的 HistSPathPass999');

    // 验证列表接口按 id desc 返回的条目脱敏与保留结果
    $opItemsByTime = [];
    foreach ($opItems as $item) {
        $opItemsByTime[(int) $item['time']] = $item;
    }
    $listLoginDecoded = json_decode((string) $opItemsByTime[$now - 100]['params'], true);
    assertLogSanitizationSame('YWRtaW4=', $listLoginDecoded['form']['username'] ?? '', '列表返回 doLogin 日志 username 保留');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $listLoginDecoded['form']['password'] ?? '', '列表返回 doLogin 日志 password 应脱敏');

    assertLogSanitizationSame('/admin/AdminManager/changePassword', (string) $opItemsByTime[$now - 90]['url'], '列表返回普通 URL 应去除查询字符串');
    $listChangePassDecoded = json_decode((string) $opItemsByTime[$now - 90]['params'], true);
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $listChangePassDecoded['password'] ?? '', '列表返回 changePassword 日志 password 应脱敏');
    assertLogSanitizationSame(OperationlogModel::SENSITIVE_MASK, $listChangePassDecoded['new_password'] ?? '', '列表返回 changePassword 日志 new_password 应脱敏');

    assertLogSanitizationSame('/index.php?s=/admin/AdminManager/changePassword', (string) $opItemsByTime[$now - 85]['url'], '列表返回 s 路由 URL 应保留 s 路由标识并去除密码查询参数');
    assertLogSanitizationSame('/admin/AdminManager/changePassword', (string) $opItemsByTime[$now - 83]['url'], '列表返回带额外路径参数的普通 URL 应仅保留应用/控制器/操作标识');
    assertLogSanitizationSame('/index.php?s=/admin/AdminManager/changePassword', (string) $opItemsByTime[$now - 82]['url'], '列表返回带额外路径参数的 s 路由 URL 应仅保留应用/控制器/操作标识');
    assertLogSanitizationSame('/index.php?s=/admin/Config/index', (string) $opItemsByTime[$now - 80]['url'], '历史日志应剥离兼容分隔符后的 s 路由参数');
    assertLogSanitizationSame('{"sitename":"ztbcms"}', (string) $opItemsByTime[$now - 80]['params'], '列表返回普通日志参数应保持原样');
    assertLogSanitizationSame('{}', (string) $opItemsByTime[$now - 70]['params'], '列表返回含敏感词损坏日志应脱敏为 {}');
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    AdminUserService::getInstance()->logout();
    Session::delete(AdminUserService::KEY_USER_ID);
    Session::delete(AdminUserService::KEY_USER_INFO);
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

echo "admin_log_password_sanitization_test passed\n";
