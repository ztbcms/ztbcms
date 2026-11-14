<?php

use app\admin\controller\AdminManager;
use think\App;
use think\facade\Config;
use think\facade\Db;
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
function assertAdminManagerGetDetail(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

Config::set([
    'default' => 'admin_manager_get_detail_test',
    'auto_timestamp' => false,
    'datetime_format' => 'Y-m-d H:i:s',
    'connections' => [
        'admin_manager_get_detail_test' => [
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
    status INTEGER NOT NULL,
    remark VARCHAR(255) NOT NULL DEFAULT "",
    create_time INTEGER NOT NULL DEFAULT 0,
    update_time INTEGER NOT NULL DEFAULT 0
)');

Db::name('user')->insert([
    'id' => 1,
    'username' => 'admin',
    'nickname' => '超级管理员',
    'password' => 'hashed-secret',
    'verify' => 'salt12',
    'email' => 'admin@example.com',
    'role_id' => 1,
    'status' => 1,
    'remark' => 'test',
    'create_time' => 1700000000,
    'update_time' => 1700000001,
]);

/** @var AdminManager $controller */
$controller = (new ReflectionClass(AdminManager::class))->newInstanceWithoutConstructor();

// 1. 查询存在的管理员详情
$existingReq = new Request();
$existingReq->withServer(['REQUEST_METHOD' => 'GET'])
    ->withGet(['_action' => 'getDetail', 'id' => 1]);
$app->instance('request', $existingReq);

$existingResp = $controller->managerEdit()->getData();
assertAdminManagerGetDetail(($existingResp['status'] ?? false) === true, '查询存在管理员应返回成功');
$detail = is_object($existingResp['data']) ? $existingResp['data']->toArray() : $existingResp['data'];
assertAdminManagerGetDetail((int) ($detail['id'] ?? 0) === 1, '返回的管理员 ID 不匹配');
assertAdminManagerGetDetail(($detail['status'] ?? null) === '1', '返回的 status 应强转为字符串');
foreach (['password', 'verify', 'create_time', 'update_time'] as $hiddenField) {
    assertAdminManagerGetDetail(!array_key_exists($hiddenField, $detail), '返回详情不应包含敏感或排除字段: ' . $hiddenField);
}

// 2. 查询不存在的管理员详情
$missingReq = new Request();
$missingReq->withServer(['REQUEST_METHOD' => 'GET'])
    ->withGet(['_action' => 'getDetail', 'id' => 9999]);
$app->instance('request', $missingReq);

$missingResp = $controller->managerEdit()->getData();
assertAdminManagerGetDetail(($missingResp['status'] ?? true) === false, '管理员不存在时 status 应为 false');
assertAdminManagerGetDetail(array_key_exists('data', $missingResp) && $missingResp['data'] === null, '管理员不存在时 data 应为 null');
assertAdminManagerGetDetail(($missingResp['msg'] ?? '') === '该管理员不存在', '管理员不存在时提示信息不匹配');

restore_error_handler();

echo "admin_manager_get_detail_test passed\n";
