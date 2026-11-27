<?php

use app\admin\controller\Login;
use app\admin\model\AdminUserModel;
use app\common\libs\helper\ClientIpHelper;
use app\common\libs\helper\PasswordHelper;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\Request;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/TestCacheHelper.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

set_error_handler(
    function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

function assertBruteforce(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

function bindLoginRequest(App $app, string $remoteAddr, array $extraServer, string $username, string $password): void
{
    $req = new Request();
    $server = array_merge([
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR'    => $remoteAddr,
        'HTTP_HOST'      => 'localhost',
        'REQUEST_URI'    => '/admin/Login/doLogin',
    ], $extraServer);
    $req->withServer($server);
    $req->setMethod('POST');
    $req->setController('Login');
    $req->setAction('doLogin');
    $req->withPost([
        'form' => [
            'username' => base64_encode(urlencode($username)),
            'password' => base64_encode(urlencode($password)),
            'code'     => '1234',
        ],
    ]);
    $app->instance('request', $req);
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_login_bruteforce_cache_' . uniqid('', true);
mkdir($tmpCacheDir, 0777, true);
$originalAdminConfig = Config::get('admin');

try {
    Config::set([
        'default' => 'login_bruteforce_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'login_bruteforce_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    configureIsolatedTestFileCache($tmpCacheDir);
    Cache::clear();

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'checkcode_type',
        'value'   => '0',
    ]);

    Db::execute('CREATE TABLE user (
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

    Db::execute('CREATE TABLE login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');

    $verify = 'salt99';
    Db::name('user')->insert([
        'id'       => 1,
        'username' => 'admin',
        'nickname' => 'Admin',
        'verify'   => $verify,
        'password' => PasswordHelper::hashPassword('ValidPass!123', $verify),
        'role_id'  => 1,
        'status'   => AdminUserModel::STATUS_ENABLE,
    ]);

    $loginCtrl = new class($app) extends Login {
        public function _vertify($code, $type = 'verify')
        {
            return $code === '1234';
        }
    };

    // 1. 未配置受信代理时，伪造 X-Forwarded-For / X-Real-IP 不得生效
    Config::set(array_merge(is_array($originalAdminConfig) ? $originalAdminConfig : [], [
        'proxy_server_ips'        => [],
        'proxy_client_ip_headers' => ['HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'],
    ]), 'admin');

    $spoofReq = new Request();
    $spoofReq->withServer([
        'REMOTE_ADDR'          => '203.0.113.10',
        'HTTP_X_REAL_IP'       => '198.51.100.2',
        'HTTP_X_FORWARDED_FOR' => '198.51.100.3',
    ]);
    assertBruteforce(
        ClientIpHelper::getClientIp($spoofReq) === '203.0.113.10',
        '未配置受信代理时，必须忽略 X-Forwarded-For / X-Real-IP'
    );

    // 2. 配置受信代理 CIDR 后，应从右向左剔除受信代理，拒绝左侧伪造段
    Config::set(array_merge(is_array($originalAdminConfig) ? $originalAdminConfig : [], [
        'proxy_server_ips'        => ['10.0.0.0/8', '172.16.0.1'],
        'proxy_client_ip_headers' => ['HTTP_X_FORWARDED_FOR'],
    ]), 'admin');

    $proxyReq = new Request();
    $proxyReq->withServer([
        'REMOTE_ADDR'          => '172.16.0.1',
        'HTTP_X_FORWARDED_FOR' => '9.9.9.9, 198.51.100.88, 10.0.0.5',
    ]);
    assertBruteforce(
        ClientIpHelper::getClientIp($proxyReq) === '198.51.100.88',
        '受信代理下应从右向左取第一个非代理 IP（防左侧伪造）'
    );

    $untrustedHopReq = new Request();
    $untrustedHopReq->withServer([
        'REMOTE_ADDR'          => '203.0.113.99',
        'HTTP_X_FORWARDED_FOR' => '198.51.100.88',
    ]);
    assertBruteforce(
        ClientIpHelper::getClientIp($untrustedHopReq) === '203.0.113.99',
        '直连来源不在受信代理列表时不得信任转发头'
    );

    // 3. 分布式 IP 轮换针对同一账号连续爆破 5 次后，第 6 次即使换全新 IP 且密码正确也应锁定账号
    Config::set(array_merge(is_array($originalAdminConfig) ? $originalAdminConfig : [], [
        'proxy_server_ips'                  => [],
        'login_faild_time_interval'         => 5,
        'login_max_faild'                   => 5,
        'ban_login_time'                    => 30,
        'login_account_faild_time_interval' => 15,
        'login_account_max_faild'           => 5,
        'login_account_ban_time'            => 15,
    ]), 'admin');

    for ($i = 1; $i <= 5; $i++) {
        bindLoginRequest($app, '198.51.100.' . $i, ['HTTP_X_FORWARDED_FOR' => '192.0.2.' . $i], 'admin', 'WrongPass' . $i);
        $resp = $loginCtrl->doLogin()->getData();
        assertBruteforce(($resp['status'] ?? true) === false, "第 {$i} 次错误密码应失败");
        assertBruteforce(($resp['msg'] ?? '') === '用户名或者密码错误', "前 5 次失败应返回普通密码错误提示");
    }

    // 第 6 次切换新 IP 并使用正确密码也必须被账号维度锁定拦截
    bindLoginRequest($app, '198.51.100.200', [], 'admin', 'ValidPass!123');
    $lockedResp = $loginCtrl->doLogin()->getData();
    assertBruteforce(($lockedResp['status'] ?? true) === false, '连续 5 次失败后应锁定账号');
    assertBruteforce(
        strpos((string) ($lockedResp['msg'] ?? ''), '15分钟后再试') !== false,
        '账号锁定应提示 15 分钟后再试，实际: ' . ($lockedResp['msg'] ?? '')
    );

    // 4. 其他账号不受 admin 锁定影响；且历史成功登录会重置连续失败计数
    Db::name('login_log')->delete(true);
    $now = time();
    Db::name('login_log')->insertAll([
        ['username' => 'admin', 'logintime' => $now - 300, 'loginip' => '198.51.100.1', 'status' => 0, 'password' => '', 'info' => ''],
        ['username' => 'admin', 'logintime' => $now - 240, 'loginip' => '198.51.100.2', 'status' => 0, 'password' => '', 'info' => ''],
        ['username' => 'admin', 'logintime' => $now - 180, 'loginip' => '198.51.100.3', 'status' => 1, 'password' => '', 'info' => ''],
        ['username' => 'admin', 'logintime' => $now - 120, 'loginip' => '198.51.100.4', 'status' => 0, 'password' => '', 'info' => ''],
        ['username' => 'admin', 'logintime' => $now - 60, 'loginip' => '198.51.100.5', 'status' => 0, 'password' => '', 'info' => ''],
    ]);
    bindLoginRequest($app, '198.51.100.201', [], 'admin', 'ValidPass!123');
    $afterResetResp = $loginCtrl->doLogin()->getData();
    assertBruteforce(($afterResetResp['status'] ?? false) === true, '中间有成功登录记录时连续失败仅 2 次，应允许正常登录');

    // 5. 同 IP 伪造 X-Forwarded-For 多次失败后仍按真实 REMOTE_ADDR 限速拦截
    Db::name('login_log')->delete(true);
    for ($i = 1; $i <= 5; $i++) {
        bindLoginRequest(
            $app,
            '203.0.113.55',
            ['HTTP_X_FORWARDED_FOR' => '198.51.100.' . ($i + 10)],
            'user_' . $i,
            'WrongPass'
        );
        $loginCtrl->doLogin();
    }
    bindLoginRequest($app, '203.0.113.55', ['HTTP_X_FORWARDED_FOR' => '198.51.100.99'], 'fresh_user', 'WrongPass');
    $ipBlockedResp = $loginCtrl->doLogin()->getData();
    assertBruteforce(($ipBlockedResp['status'] ?? true) === false, '同 IP 多次失败即使更换 X-Forwarded-For 和用户名也应拦截');
    assertBruteforce(
        strpos((string) ($ipBlockedResp['msg'] ?? ''), '30分钟后再试') !== false,
        'IP 锁定应提示 30 分钟后再试，实际: ' . ($ipBlockedResp['msg'] ?? '')
    );

    echo "admin_login_bruteforce_test passed\n";
} finally {
    restore_error_handler();
    if (is_array($originalAdminConfig)) {
        Config::set($originalAdminConfig, 'admin');
    }
    if (is_dir($tmpCacheDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpCacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tmpCacheDir);
    }
}
