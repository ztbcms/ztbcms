<?php

use app\admin\controller\Menu;
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
function assertMenuGetMenuListCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 初始化测试内存数据库并执行单个 action 的 _action=getMenuList 断言
 *
 * @param App $app 应用实例
 * @param string $actionName 控制器方法名
 * @return void
 */
function runSingleMenuActionCheck(App $app, string $actionName): void
{
    Config::set([
        'default' => 'admin_menu_get_menu_list_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_menu_get_menu_list_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

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
        ['id' => 1, 'name' => '系统设置', 'parentid' => 0, 'app' => 'Admin', 'controller' => 'Setting', 'action' => 'index', 'listorder' => 1],
        ['id' => 2, 'name' => '菜单管理', 'parentid' => 1, 'app' => 'Admin', 'controller' => 'Menu', 'action' => 'index', 'listorder' => 1],
    ]);

    $ref = new ReflectionClass(Menu::class);
    /** @var Menu $controller */
    $controller = $ref->newInstanceWithoutConstructor();

    $req = new Request();
    $req->withServer(['REQUEST_METHOD' => 'GET'])->withGet(['_action' => 'getMenuList']);
    $app->instance('request', $req);

    $res = $controller->{$actionName}()->getData();
    assertMenuGetMenuListCleanup(
        ($res['status'] ?? false) === true && is_array($res['data']) && count($res['data']) === 2,
        "Menu::{$actionName} GET _action=getMenuList 应返回 2 条菜单数据，实际为 " . json_encode($res)
    );
}

try {
    $options = getopt('', ['action:']);
    if (!empty($options['action'])) {
        runSingleMenuActionCheck($app, (string) $options['action']);
        exit(0);
    }

    // 1. 验证独立 action Menu::getMenuList 已移除
    $ref = new ReflectionClass(Menu::class);
    assertMenuGetMenuListCleanup(
        !$ref->hasMethod('getMenuList'),
        'Menu::getMenuList action 应已移除'
    );
    foreach (['index', 'menuAdd', 'menuEdit', 'menuDelete'] as $method) {
        assertMenuGetMenuListCleanup(
            $ref->hasMethod($method),
            "Menu::{$method} 应保留"
        );
    }

    // 2. 在独立 PHP 进程中分别验证 index / menuAdd / menuEdit（避免 TreeHelper 静态缓存跨调用累加）
    foreach (['index', 'menuAdd', 'menuEdit'] as $actionName) {
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --action=' . escapeshellarg($actionName) . ' 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        assertMenuGetMenuListCleanup(
            $exitCode === 0,
            "Menu::{$actionName} 子进程校验失败（exit={$exitCode}）: " . implode("\n", $output)
        );
    }
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    restore_error_handler();
}

echo "admin_menu_get_menu_list_cleanup_test passed\n";
