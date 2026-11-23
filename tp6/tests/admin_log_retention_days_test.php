<?php

use think\App;
use think\facade\Config;
use think\facade\Db;
use think\Request;
use app\admin\controller\Logs;
use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use app\admin\service\AdminLogService;

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
function assertRetentionTrue(bool $condition, string $message): void
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
function assertRetentionSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 绑定测试请求实例到应用容器
 *
 * @param App $app 应用实例
 * @param string $method 请求方法
 * @param array $get GET 参数
 * @param array $post POST 参数
 * @param array $route 路由参数
 * @return Request
 */
function bindRetentionTestRequest(
    App $app,
    string $method,
    array $get = [],
    array $post = [],
    array $route = []
): Request {
    $request = new Request();
    $request->withServer([
        'REQUEST_METHOD' => strtoupper($method),
        'REMOTE_ADDR' => '127.0.0.1',
    ]);
    $request->setMethod(strtoupper($method));
    $request->withGet($get);
    $request->withPost($post);
    $request->withRoute($route);

    $app->instance('request', $request);

    return $request;
}

/**
 * 重置登录日志与操作日志种子数据（1天前、6天前、8天前、35天前）
 *
 * @param int $now 当前时间戳
 * @return void
 */
function seedAdminLogsForRetentionTest(int $now): void
{
    $daySeconds = 24 * 60 * 60;
    Db::execute('DELETE FROM login_log');
    Db::execute('DELETE FROM operation_log');

    $ages = [1, 6, 8, 35];
    foreach ($ages as $index => $ageDays) {
        $timestamp = $now - $ageDays * $daySeconds;
        Db::name('login_log')->insert([
            'username' => 'user_' . $ageDays . 'd',
            'logintime' => $timestamp,
            'loginip' => '127.0.0.' . ($index + 1),
            'status' => 1,
            'info' => 'age_' . $ageDays . '_days',
        ]);
        Db::name('operation_log')->insert([
            'uid' => 1,
            'time' => $timestamp,
            'ip' => '127.0.0.' . ($index + 1),
            'status' => 1,
            'method' => 'POST',
            'url' => '/admin/Config/index',
            'params' => '{"age":' . $ageDays . '}',
            'response' => '{"status":true}',
        ]);
    }
}

/**
 * 获取日志表中现存记录的标志列表（按时间升序）
 *
 * @param string $table 表名
 * @param string $timeColumn 时间字段
 * @return array
 */
function getExistingLogAges(string $table, string $timeColumn, int $now): array
{
    $rows = Db::name($table)->order($timeColumn, 'asc')->select()->toArray();
    $daySeconds = 24 * 60 * 60;
    $ages = [];
    foreach ($rows as $row) {
        $ages[] = (int) round(($now - (int) $row[$timeColumn]) / $daySeconds);
    }

    return $ages;
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_admin_log_retention_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // ==================== 1. AdminLogService 边界与格式解析测试 ====================
    assertRetentionSame(30, AdminLogService::DEFAULT_RETENTION_DAYS, '默认保留天数应为 30');
    assertRetentionSame(7, AdminLogService::MIN_RETENTION_DAYS, '最少保留天数应为 7');
    assertRetentionSame(3650, AdminLogService::MAX_RETENTION_DAYS, '最大保留天数应为 3650');

    $validCases = [
        [7, 7],
        [30, 30],
        [3650, 3650],
        ['7', 7],
        [' 30 ', 30],
        ['+30', 30],
        ['030', 30],
        ['3650', 3650],
        ['000030', 30],
    ];
    foreach ($validCases as [$input, $expected]) {
        assertRetentionSame(
            $expected,
            AdminLogService::parseRetentionDays($input),
            '合法保留天数应解析通过: ' . var_export($input, true)
        );
    }

    $invalidCases = [
        -99999,
        PHP_INT_MIN,
        -30,
        -1,
        0,
        1,
        6,
        3651,
        PHP_INT_MAX,
        '-1',
        '-0',
        '0',
        '6',
        '3651',
        '99999999999999999999999999',
        '',
        '   ',
        'abc',
        '30abc',
        '30.0',
        '30.5',
        '1e2',
        "30\nabc",
        null,
        true,
        false,
        30.0,
        30.5,
        [30],
        new stdClass(),
    ];
    foreach ($invalidCases as $invalidInput) {
        assertRetentionSame(
            null,
            AdminLogService::parseRetentionDays($invalidInput),
            '非法或越界保留天数应返回 null: ' . var_export($invalidInput, true)
        );
    }

    assertRetentionSame(30, AdminLogService::resolveRetentionDaysFromParams([]), '未传 day 应返回默认 30');
    assertRetentionSame(30, AdminLogService::resolveRetentionDaysFromParams(null), '非数组参数应返回默认 30');
    assertRetentionSame(15, AdminLogService::resolveRetentionDaysFromParams(['day' => '15']), '合法参数应解析成功');
    foreach ([null, '', 0, -1, 6, 3651, [30], '30abc'] as $explicitInvalid) {
        assertRetentionSame(
            null,
            AdminLogService::resolveRetentionDaysFromParams(['day' => $explicitInvalid]),
            '显式传入非法 day 不得静默回退默认 30: ' . var_export($explicitInvalid, true)
        );
    }

    // ==================== 2. 初始化 SQLite 内存库与临时缓存 ====================
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
        'default' => 'admin_log_retention_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_log_retention_test' => [
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
        'info' => '后台操作日志开关',
        'groupid' => 1,
        'value' => '0',
    ]);

    Db::execute('CREATE TABLE login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(32) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE operation_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        time INTEGER NOT NULL DEFAULT 0,
        ip VARCHAR(32) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        method VARCHAR(32) NOT NULL DEFAULT "",
        url VARCHAR(512) NOT NULL DEFAULT "",
        params TEXT NOT NULL,
        response TEXT NOT NULL
    )');

    $now = time();
    $modelInvalidInputs = [-1, -365, 0, 1, 6, 3651, 'abc', '', null, false, true, 30.5, [30]];

    // ==================== 3. 模型层纵深防御验证 ====================
    seedAdminLogsForRetentionTest($now);
    $loginModel = new LoginlogModel();
    $opModel = new OperationlogModel();

    foreach ($modelInvalidInputs as $invalidDay) {
        assertRetentionSame(
            false,
            $loginModel->deleteLoginLog($invalidDay),
            'LoginlogModel::deleteLoginLog 传入非法参数应返回 false: ' . var_export($invalidDay, true)
        );
        assertRetentionSame(
            [35, 8, 6, 1],
            getExistingLogAges('login_log', 'logintime', $now),
            'LoginlogModel::deleteLoginLog 拒绝非法参数时不得删除任何登录日志'
        );

        assertRetentionSame(
            false,
            OperationlogModel::deleteOperationLog($invalidDay),
            'OperationlogModel::deleteOperationLog 传入非法参数应返回 false: ' . var_export($invalidDay, true)
        );
        assertRetentionSame(
            [35, 8, 6, 1],
            getExistingLogAges('operation_log', 'time', $now),
            'OperationlogModel::deleteOperationLog 拒绝非法参数时不得删除任何操作日志'
        );
    }

    // 默认调用（30天）：仅删除 35 天前记录
    assertRetentionSame(true, $loginModel->deleteLoginLog(), 'LoginlogModel::deleteLoginLog 默认调用应返回 true');
    assertRetentionSame([8, 6, 1], getExistingLogAges('login_log', 'logintime', $now), '登录日志默认应仅删除 30 日前记录');

    assertRetentionSame(true, OperationlogModel::deleteOperationLog(), 'OperationlogModel::deleteOperationLog 默认调用应返回 true');
    assertRetentionSame([8, 6, 1], getExistingLogAges('operation_log', 'time', $now), '操作日志默认应仅删除 30 日前记录');

    // 下限边界 7 天与字符串 '7'：仅删除 8 天前记录，保留近 7 天（6天、1天前）
    assertRetentionSame(true, $loginModel->deleteLoginLog(7), 'LoginlogModel::deleteLoginLog(7) 应返回 true');
    assertRetentionSame([6, 1], getExistingLogAges('login_log', 'logintime', $now), '登录日志应保留近 7 天记录');
    assertRetentionSame(true, $loginModel->deleteLoginLog('7'), 'LoginlogModel::deleteLoginLog("7") 无可删记录也应返回 true');
    assertRetentionSame([6, 1], getExistingLogAges('login_log', 'logintime', $now), '重复清理近 7 天登录日志仍应保留');

    assertRetentionSame(true, $opModel->deleteOperationLog(7), 'OperationlogModel::deleteOperationLog(7) 应返回 true');
    assertRetentionSame([6, 1], getExistingLogAges('operation_log', 'time', $now), '操作日志应保留近 7 天记录');
    assertRetentionSame(true, OperationlogModel::deleteOperationLog('7'), 'OperationlogModel::deleteOperationLog("7") 无可删记录也应返回 true');
    assertRetentionSame([6, 1], getExistingLogAges('operation_log', 'time', $now), '重复清理近 7 天操作日志仍应保留');

    // ==================== 4. 控制器层入口验证 ====================
    $expectedErrMsg = AdminLogService::getRetentionDaysErrorMessage();
    $controllerInvalidRequests = [
        ['POST', [], ['day' => -1], []],
        ['POST', [], ['day' => -30], []],
        ['POST', [], ['day' => 0], []],
        ['POST', [], ['day' => 6], []],
        ['POST', [], ['day' => 3651], []],
        ['POST', [], ['day' => ''], []],
        ['POST', [], ['day' => 'abc'], []],
        ['POST', [], ['day' => '30abc'], []],
        ['POST', [], ['day' => '99999999999999999999999999'], []],
        ['POST', [], ['day' => null], []],
        ['POST', [], ['day' => [30]], []],
        ['POST', [], [], ['day' => '0']],
    ];

    // 4.1 非 POST 请求拦截验证（无论是否传 day 或参数是否合法，一律返回 405 且不删除任何日志）
    $nonPostRequests = [
        ['GET', [], [], []],
        ['GET', ['day' => '30'], [], []],
        ['GET', ['day' => '-1'], [], []],
        ['GET', [], [], ['day' => '-1']],
        ['HEAD', ['day' => '30'], [], []],
    ];
    seedAdminLogsForRetentionTest($now);
    foreach ($nonPostRequests as [$method, $get, $post, $route]) {
        bindRetentionTestRequest($app, $method, $get, $post, $route);
        $controller = new Logs($app);
        $loginResponse = $controller->deleteLoginLog();
        $loginResp = $loginResponse->getData();
        assertRetentionSame(405, $loginResponse->getCode(), 'deleteLoginLog 非 POST HTTP 状态码应为 405: ' . var_export([$method, $get, $post, $route], true));
        assertRetentionSame(false, $loginResp['status'], 'deleteLoginLog 非 POST 请求应失败');
        assertRetentionSame(405, $loginResp['code'], 'deleteLoginLog 非 POST 业务 code 应为 405');
        assertRetentionSame('仅支持 POST 请求', $loginResp['msg'], 'deleteLoginLog 非 POST 错误提示不匹配');
        assertRetentionSame([35, 8, 6, 1], getExistingLogAges('login_log', 'logintime', $now), 'deleteLoginLog 拦截非 POST 后不得删除任何登录日志');

        bindRetentionTestRequest($app, $method, $get, $post, $route);
        $controller = new Logs($app);
        $opResponse = $controller->deleteAdminOperationLog();
        $opResp = $opResponse->getData();
        assertRetentionSame(405, $opResponse->getCode(), 'deleteAdminOperationLog 非 POST HTTP 状态码应为 405: ' . var_export([$method, $get, $post, $route], true));
        assertRetentionSame(false, $opResp['status'], 'deleteAdminOperationLog 非 POST 请求应失败');
        assertRetentionSame(405, $opResp['code'], 'deleteAdminOperationLog 非 POST 业务 code 应为 405');
        assertRetentionSame('仅支持 POST 请求', $opResp['msg'], 'deleteAdminOperationLog 非 POST 错误提示不匹配');
        assertRetentionSame([35, 8, 6, 1], getExistingLogAges('operation_log', 'time', $now), 'deleteAdminOperationLog 拦截非 POST 后不得删除任何操作日志');
    }

    // 4.2 POST 非法参数验证
    seedAdminLogsForRetentionTest($now);
    foreach ($controllerInvalidRequests as [$method, $get, $post, $route]) {
        bindRetentionTestRequest($app, $method, $get, $post, $route);
        $controller = new Logs($app);
        $loginResp = $controller->deleteLoginLog()->getData();
        assertRetentionSame(false, $loginResp['status'], 'deleteLoginLog 非法参数应失败: ' . var_export([$method, $get, $post, $route], true));
        assertRetentionSame(400, $loginResp['code'], 'deleteLoginLog 非法参数 code 应为 400');
        assertRetentionSame($expectedErrMsg, $loginResp['msg'], 'deleteLoginLog 错误提示不匹配');
        assertRetentionSame([35, 8, 6, 1], getExistingLogAges('login_log', 'logintime', $now), 'deleteLoginLog 拦截后不得删除任何登录日志');

        bindRetentionTestRequest($app, $method, $get, $post, $route);
        $controller = new Logs($app);
        $opResp = $controller->deleteAdminOperationLog()->getData();
        assertRetentionSame(false, $opResp['status'], 'deleteAdminOperationLog 非法参数应失败: ' . var_export([$method, $get, $post, $route], true));
        assertRetentionSame(400, $opResp['code'], 'deleteAdminOperationLog 非法参数 code 应为 400');
        assertRetentionSame($expectedErrMsg, $opResp['msg'], 'deleteAdminOperationLog 错误提示不匹配');
        assertRetentionSame([35, 8, 6, 1], getExistingLogAges('operation_log', 'time', $now), 'deleteAdminOperationLog 拦截后不得删除任何操作日志');
    }

    // 未传 day 参数（POST）：按默认 30 天清理
    seedAdminLogsForRetentionTest($now);
    bindRetentionTestRequest($app, 'POST');
    $controller = new Logs($app);
    $defaultLoginResp = $controller->deleteLoginLog()->getData();
    assertRetentionSame(true, $defaultLoginResp['status'], 'deleteLoginLog 未传 day 应按默认 30 天成功');
    assertRetentionSame(200, $defaultLoginResp['code'], 'deleteLoginLog 成功 code 应为 200');
    assertRetentionSame('删除成功', $defaultLoginResp['msg'], 'deleteLoginLog 成功文案不匹配');
    assertRetentionSame([8, 6, 1], getExistingLogAges('login_log', 'logintime', $now), '未传 day 应清理 30 日前登录日志');

    bindRetentionTestRequest($app, 'POST');
    $controller = new Logs($app);
    $defaultOpResp = $controller->deleteAdminOperationLog()->getData();
    assertRetentionSame(true, $defaultOpResp['status'], 'deleteAdminOperationLog 未传 day 应按默认 30 天成功');
    assertRetentionSame(200, $defaultOpResp['code'], 'deleteAdminOperationLog 成功 code 应为 200');
    assertRetentionSame('删除成功', $defaultOpResp['msg'], 'deleteAdminOperationLog 成功文案不匹配');
    assertRetentionSame([8, 6, 1], getExistingLogAges('operation_log', 'time', $now), '未传 day 应清理 30 日前操作日志');

    // 显式传 ' 30 ' 与 '7'
    seedAdminLogsForRetentionTest($now);
    bindRetentionTestRequest($app, 'POST', [], ['day' => ' 30 ']);
    $controller = new Logs($app);
    $resp30Login = $controller->deleteLoginLog()->getData();
    assertRetentionSame(true, $resp30Login['status'], 'deleteLoginLog 传入 " 30 " 应成功');
    assertRetentionSame([8, 6, 1], getExistingLogAges('login_log', 'logintime', $now), '传入 " 30 " 应删除 35 天前登录日志');

    bindRetentionTestRequest($app, 'POST', [], ['day' => ' 30 ']);
    $controller = new Logs($app);
    $resp30Op = $controller->deleteAdminOperationLog()->getData();
    assertRetentionSame(true, $resp30Op['status'], 'deleteAdminOperationLog 传入 " 30 " 应成功');
    assertRetentionSame([8, 6, 1], getExistingLogAges('operation_log', 'time', $now), '传入 " 30 " 应删除 35 天前操作日志');

    bindRetentionTestRequest($app, 'POST', [], ['day' => 7]);
    $controller = new Logs($app);
    $resp7Login = $controller->deleteLoginLog()->getData();
    assertRetentionSame(true, $resp7Login['status'], 'deleteLoginLog 传入 7 应成功');
    assertRetentionSame([6, 1], getExistingLogAges('login_log', 'logintime', $now), '传入 7 应保留近 7 天登录日志');

    bindRetentionTestRequest($app, 'POST', [], ['day' => '7']);
    $controller = new Logs($app);
    $resp7Op = $controller->deleteAdminOperationLog()->getData();
    assertRetentionSame(true, $resp7Op['status'], 'deleteAdminOperationLog 传入 "7" 应成功');
    assertRetentionSame([6, 1], getExistingLogAges('operation_log', 'time', $now), '传入 "7" 应保留近 7 天操作日志');
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    restore_error_handler();
    exit(1);
} finally {
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

echo "admin_log_retention_days_test passed\n";
