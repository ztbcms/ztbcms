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
function assertMenuUpdateTableCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证 Menu::updateTable 已移除
    $ref = new ReflectionClass(Menu::class);
    assertMenuUpdateTableCleanup(
        !$ref->hasMethod('updateTable'),
        'Menu::updateTable 应已移除'
    );
    foreach (['index', 'menuAdd', 'menuEdit', 'menuDelete'] as $method) {
        assertMenuUpdateTableCleanup(
            $ref->hasMethod($method),
            "Menu::{$method} 应保留"
        );
    }

    Config::set([
        'default' => 'admin_menu_update_table_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_menu_update_table_cleanup_test' => [
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

    Db::name('menu')->insert([
        'id' => 1,
        'name' => '菜单管理',
        'parentid' => 0,
        'app' => 'Admin',
        'controller' => 'Menu',
        'action' => 'index',
        'listorder' => 0,
    ]);

    /** @var Menu $controller */
    $controller = $ref->newInstanceWithoutConstructor();

    // 2. 验证正常更新排序
    $reqSuccess = new Request();
    $reqSuccess->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
        '_action' => 'updateSort',
        'id' => 1,
        'listorder' => 88,
    ]);
    $app->instance('request', $reqSuccess);

    $resSuccess = $controller->index()->getData();
    assertMenuUpdateTableCleanup(
        ($resSuccess['status'] ?? false) === true,
        'Menu::index POST _action=updateSort 应返回成功'
    );
    $updatedOrder = Db::name('menu')->where('id', 1)->value('listorder');
    assertMenuUpdateTableCleanup(
        (int) $updatedOrder === 88,
        '菜单排序值应已更新为 88，实际为 ' . var_export($updatedOrder, true)
    );

    // 3. 验证非法排序参数拦截
    $reqInvalid = new Request();
    $reqInvalid->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
        '_action' => 'updateSort',
        'id' => 1,
        'listorder' => -1,
    ]);
    $app->instance('request', $reqInvalid);

    $resInvalid = $controller->index()->getData();
    assertMenuUpdateTableCleanup(
        ($resInvalid['status'] ?? true) === false && ($resInvalid['msg'] ?? '') === '参数异常',
        'listorder < 0 时应拦截并返回「参数异常」'
    );
} finally {
    restore_error_handler();
}

echo "admin_menu_update_table_cleanup_test passed\n";
