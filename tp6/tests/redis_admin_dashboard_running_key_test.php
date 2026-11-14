<?php

use app\common\controller\redis\Admin;
use app\common\libs\redis\RedisFactory;
use think\App;
use think\facade\Config;
use think\Request;
use think\response\Json;

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
function assertRedisAdminRunningKey(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$originalRedisConfig = Config::get('redis');
$instanceProp = new ReflectionProperty(RedisFactory::class, 'instance');
$instanceProp->setAccessible(true);
$originalInstances = $instanceProp->getValue(null);

try {
    // 1. 源码拼写检查：确保连接失败分支不再使用 'runing'
    $adminSource = file_get_contents(dirname(__DIR__) . '/app/common/controller/redis/Admin.php');
    assertRedisAdminRunningKey(
        is_string($adminSource) && strpos($adminSource, "'runing'") === false,
        "Admin.php 不应再包含拼写错误的 'runing' 键名"
    );
    assertRedisAdminRunningKey(
        is_string($adminSource) && strpos($adminSource, "'running' => false") !== false,
        "Admin.php 连接失败分支应包含 'running' => false"
    );

    // 2. 分配一个立即可确认未监听的本地端口，确保必然触发 ConnectionException
    $unreachablePort = 65432;
    $tmpServer = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    if (is_resource($tmpServer)) {
        $socketName = stream_socket_get_name($tmpServer, false);
        fclose($tmpServer);
        if (is_string($socketName) && strpos($socketName, ':') !== false) {
            $parts = explode(':', $socketName);
            $unreachablePort = (int) end($parts);
        }
    }

    // 3. 覆盖 Redis 连接配置并清空静态连接池
    $instanceProp->setValue(null, []);
    Config::set([
        'default' => 'unreachable_test',
        'connections' => [
            'unreachable_test' => [
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => $unreachablePort,
                'password' => '',
                'database' => 0,
                'timeout' => 0.2,
                'persistent' => false,
            ],
        ],
    ], 'redis');

    /** @var Admin $controller */
    $controller = (new ReflectionClass(Admin::class))->newInstanceWithoutConstructor();
    $request = new Request();
    $request->withServer(['REQUEST_METHOD' => 'GET'])
        ->withGet(['_action' => 'getDashboard']);
    $app->instance('request', $request);

    $response = $controller->dashboard($request);
    assertRedisAdminRunningKey(
        $response instanceof Json,
        'dashboard GET _action=getDashboard 应返回 Json 响应'
    );

    $payload = $response->getData();
    assertRedisAdminRunningKey(
        ($payload['status'] ?? false) === true && is_array($payload['data'] ?? null),
        'dashboard 响应结构应为成功状态且 data 为数组'
    );
    assertRedisAdminRunningKey(
        count($payload['data']) === 1,
        'dashboard 应返回 1 条连接记录'
    );

    $row = $payload['data'][0];
    assertRedisAdminRunningKey(
        ($row['connection'] ?? '') === 'unreachable_test',
        '连接名称应为 unreachable_test'
    );
    assertRedisAdminRunningKey(
        array_key_exists('running', $row) && $row['running'] === false,
        "连接失败分支必须返回 'running' => false"
    );
    assertRedisAdminRunningKey(
        !array_key_exists('runing', $row),
        "连接失败分支不应返回拼写错误的 'runing' 键"
    );
    assertRedisAdminRunningKey(
        ($row['version'] ?? null) === '-' && $row['info'] === [],
        '连接失败分支 version 应为 "-" 且 info 为空数组'
    );

    echo "redis_admin_dashboard_running_key_test passed\n";
} finally {
    $instanceProp->setValue(null, $originalInstances);
    if (is_array($originalRedisConfig)) {
        Config::set($originalRedisConfig, 'redis');
    }
    restore_error_handler();
}
