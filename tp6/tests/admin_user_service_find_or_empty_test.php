<?php

use app\admin\service\AdminUserService;
use app\common\libs\helper\PasswordHelper;
use think\App;
use think\facade\Config;
use think\facade\Db;

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
function assertAdminUserFindOrEmpty(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    Config::set([
        'default' => 'admin_user_service_find_or_empty_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_user_service_find_or_empty_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL,
        nickname VARCHAR(64) NOT NULL,
        password VARCHAR(32) NOT NULL,
        verify VARCHAR(32) NOT NULL,
        email VARCHAR(128) NOT NULL,
        role_id INTEGER NOT NULL,
        info VARCHAR(255) NOT NULL DEFAULT "",
        avatar VARCHAR(255) NOT NULL DEFAULT "",
        phone VARCHAR(32) NOT NULL DEFAULT "",
        status INTEGER NOT NULL,
        remark VARCHAR(255) NOT NULL DEFAULT ""
    )');

    $verify = 'salt12';
    Db::name('user')->insert([
        'id' => 1,
        'username' => 'admin',
        'nickname' => '超级管理员',
        'password' => PasswordHelper::hashPassword('secret123', $verify),
        'verify' => $verify,
        'email' => 'admin@example.com',
        'role_id' => 1,
        'info' => '',
        'avatar' => '',
        'phone' => '',
        'status' => 1,
        'remark' => 'test',
    ]);

    $service = new AdminUserService();

    // 1. checkUserPassword：用户不存在（传非空密码）
    $notFoundWithPwd = $service->checkUserPassword('nonexistent_user', '123456');
    assertAdminUserFindOrEmpty(
        ($notFoundWithPwd['status'] ?? true) === false && ($notFoundWithPwd['msg'] ?? '') === '用户未注册',
        '不存在的用户名传入密码时应返回「用户未注册」，实际: ' . ($notFoundWithPwd['msg'] ?? '')
    );

    // 2. checkUserPassword：用户不存在（传空密码）
    $notFoundEmptyPwd = $service->checkUserPassword('nonexistent_user', '');
    assertAdminUserFindOrEmpty(
        ($notFoundEmptyPwd['status'] ?? true) === false && ($notFoundEmptyPwd['msg'] ?? '') === '用户未注册',
        '不存在的用户名且不传密码时应返回「用户未注册」'
    );

    // 3. checkUserPassword：用户存在，密码错误与正确
    $wrongPwd = $service->checkUserPassword('admin', 'wrong_password');
    assertAdminUserFindOrEmpty(
        ($wrongPwd['status'] ?? true) === false && ($wrongPwd['msg'] ?? '') === '密码不正确',
        '存在用户传入错误密码应返回「密码不正确」'
    );

    $validPwd = $service->checkUserPassword('admin', 'secret123');
    assertAdminUserFindOrEmpty(
        ($validPwd['status'] ?? false) === true && ($validPwd['data']['username'] ?? '') === 'admin',
        '存在用户传入正确密码应校验成功'
    );

    $emptyPwdBypass = $service->checkUserPassword('admin', '');
    assertAdminUserFindOrEmpty(
        ($emptyPwdBypass['status'] ?? false) === true && (int) ($emptyPwdBypass['data']['id'] ?? 0) === 1,
        '存在用户且密码参数为空时应按原契约直接返回用户信息'
    );

    // 4. getAdminUserInfoById：不存在与存在
    $missingUser = $service->getAdminUserInfoById(9999);
    assertAdminUserFindOrEmpty(
        ($missingUser['status'] ?? true) === false
        && array_key_exists('data', $missingUser)
        && $missingUser['data'] === null
        && ($missingUser['msg'] ?? '') === '找不到信息',
        '查询不存在的用户 ID 应返回「找不到信息」'
    );

    $existingUser = $service->getAdminUserInfoById(1);
    assertAdminUserFindOrEmpty(
        ($existingUser['status'] ?? false) === true && ($existingUser['data']['username'] ?? '') === 'admin',
        '查询存在的用户 ID 应返回成功及用户资料'
    );

    // 5. changePassword：用户不存在时应正确走入「该用户不存在」分支
    $changeMissing = $service->changePassword(9999, 'new_secret_123');
    assertAdminUserFindOrEmpty(
        ($changeMissing['status'] ?? true) === false && ($changeMissing['msg'] ?? '') === '该用户不存在',
        '修改不存在用户的密码应返回「该用户不存在」'
    );
} finally {
    restore_error_handler();
}

echo "admin_user_service_find_or_empty_test passed\n";
