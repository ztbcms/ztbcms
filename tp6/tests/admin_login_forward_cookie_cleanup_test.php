<?php

use app\admin\controller\Login;
use app\admin\service\AdminUserService;
use app\common\libs\helper\PasswordHelper;
use think\App;
use think\facade\Config;
use think\facade\Cookie;
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
function assertLoginForwardCookieCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_login_forward_cleanup_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证源码不再读取或清除 forward cookie
    $loginSource = file_get_contents(dirname(__DIR__) . '/app/admin/controller/Login.php');
    assertLoginForwardCookieCleanup(
        $loginSource !== false
        && strpos($loginSource, 'cookie("forward")') === false
        && strpos($loginSource, "cookie('forward')") === false,
        'Login.php 中不应再包含 forward cookie 读取或清除逻辑'
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
        'default' => 'admin_login_forward_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_login_forward_cleanup_test' => [
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
        'varname' => 'checkcode_type',
        'info' => '验证码类型',
        'groupid' => 1,
        'value' => '1',
    ]);

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

    $verifySalt = 'abc123';
    $plainPassword = 'SecretPassword123';
    Db::name('user')->insert([
        'id' => 1,
        'username' => 'admin',
        'nickname' => '超级管理员',
        'verify' => $verifySalt,
        'password' => PasswordHelper::hashPassword($plainPassword, $verifySalt),
        'role_id' => 1,
        'status' => 1,
    ]);

    // 2. 注入恶意 forward cookie 并构造真实登录请求
    Session::set(md5('verify'), 'ABCD');

    /** @var Request $request */
    $request = $app->request;
    $request
        ->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'])
        ->withCookie(['forward' => 'https://evil.example.com'])
        ->withPost([
            'form' => [
                'username' => base64_encode(urlencode('admin')),
                'password' => base64_encode(urlencode($plainPassword)),
                'code' => 'abcd',
            ],
        ]);

    $controller = new class($app) extends Login {
        public function __construct(App $app)
        {
        }

        public function _vertify($code, $type = 'verify')
        {
            return true;
        }
    };

    $res = $controller->doLogin()->getData();
    $expectedForward = build_url('/admin/Dashboard/index');

    assertLoginForwardCookieCleanup(
        ($res['status'] ?? false) === true,
        'doLogin 应登录成功，实际返回：' . json_encode($res)
    );
    assertLoginForwardCookieCleanup(
        ($res['data']['forward'] ?? '') === $expectedForward,
        '即使注入 forward cookie，返回跳转地址也必须固定为后台首页 ' . $expectedForward . '，实际为 ' . ($res['data']['forward'] ?? 'null')
    );

    // 验证响应待发送 Cookie 队列中未对 forward 执行清除写操作
    $pendingCookies = Cookie::getCookie();
    assertLoginForwardCookieCleanup(
        !array_key_exists('forward', $pendingCookies),
        'doLogin 不应再操作或清除 forward cookie'
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    Cookie::delete('forward');
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

echo "admin_login_forward_cookie_cleanup_test passed\n";
