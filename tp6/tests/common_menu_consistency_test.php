<?php

use app\admin\model\MenuModel;
use think\App;
use think\facade\Config;
use think\facade\Db;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/database/migrations/20261105001000_insert_queue_monitor_menu.php';

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
function assertCommonMenuConsistency(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 将 Menu.php 路由解析为控制器类与方法名
 *
 * @param string $route 路由字符串
 * @return array
 */
function resolveCommonMenuTarget(string $route): array
{
    $parts = explode('/', $route, 3);
    assertCommonMenuConsistency(count($parts) === 3, '路由格式非法: ' . $route);

    list($module, $ctrlPath, $action) = $parts;
    $ctrlParts = explode('.', $ctrlPath);
    $className = ucfirst((string) array_pop($ctrlParts));
    $subNs = implode('\\', $ctrlParts);
    $controllerClass = 'app\\' . $module . '\\controller\\' . ($subNs !== '' ? $subNs . '\\' : '') . $className;

    return [$controllerClass, $action];
}

try {
    $menuTree = require dirname(__DIR__) . '/app/common/install/Menu.php';
    assertCommonMenuConsistency(is_array($menuTree) && !empty($menuTree), 'common/install/Menu.php 必须返回非空数组');

    $allRoutes = [];
    $validateTree = function (array $items, string $parentScope) use (&$validateTree, &$allRoutes): void {
        $siblingKeys = [];
        foreach ($items as $item) {
            $route = (string) ($item['route'] ?? '');
            $name = (string) ($item['name'] ?? '');
            $type = intval($item['type'] ?? 1);
            assertCommonMenuConsistency($route !== '', '菜单节点缺少 route: ' . $name);
            assertCommonMenuConsistency($name !== '', '菜单节点缺少 name: ' . $route);

            $allRoutes[] = $route;
            list($controllerClass, $action) = resolveCommonMenuTarget($route);
            assertCommonMenuConsistency(
                class_exists($controllerClass),
                "菜单路由 {$route} 指向的控制器不存在: {$controllerClass}"
            );
            $refClass = new ReflectionClass($controllerClass);
            assertCommonMenuConsistency(
                $refClass->hasMethod($action),
                "菜单路由 {$route} 指向的方法不存在: {$controllerClass}::{$action}"
            );
            $refMethod = $refClass->getMethod($action);
            assertCommonMenuConsistency(
                $refMethod->isPublic() && !$refMethod->isStatic(),
                "菜单路由 {$route} 指向的方法必须为非静态 public 方法"
            );

            $dupKey = strtolower($route) . '|' . $type . '|' . $name;
            assertCommonMenuConsistency(
                !isset($siblingKeys[$dupKey]),
                "同一父级下存在重复菜单节点: {$parentScope} -> {$name} ({$route})"
            );
            $siblingKeys[$dupKey] = true;

            if (!empty($item['child']) && is_array($item['child'])) {
                $validateTree($item['child'], $parentScope . '/' . $name);
            }
        }
    };

    $validateTree($menuTree, 'root');

    // 1. 失效路由不得再出现
    foreach ([
        'common/index/index',
        'common/cron.dashboard/createCron',
        'common/email.Email/index',
    ] as $invalidRoute) {
        assertCommonMenuConsistency(
            !in_array($invalidRoute, $allRoutes, true),
            'common/install/Menu.php 不应再包含不存在的路由: ' . $invalidRoute
        );
    }

    // 2. 必须登记的权限路由
    foreach ([
        'common/cron.dashboard/addOrEditCron',
        'common/cron.dashboard/runAction',
        'common/cron.dashboard/deleteCron',
        'common/upload.upload/getUploadToken',
        'common/upload.upload/directUploadDemo',
        'common/email.Email/config',
    ] as $requiredRoute) {
        assertCommonMenuConsistency(
            in_array($requiredRoute, $allRoutes, true),
            'common/install/Menu.php 必须包含路由: ' . $requiredRoute
        );
    }

    // 3. 验证 MenuModel::installModuleMenu 与队列迁移脚本兼容性
    Config::set([
        'default' => 'common_menu_consistency_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'common_menu_consistency_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) NOT NULL DEFAULT "",
        parentid INTEGER NOT NULL DEFAULT 0,
        app VARCHAR(30) NOT NULL DEFAULT "",
        controller VARCHAR(50) NOT NULL DEFAULT "",
        action VARCHAR(50) NOT NULL DEFAULT "",
        parameter VARCHAR(255) NOT NULL DEFAULT "",
        type INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 0,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        icon VARCHAR(255) NOT NULL DEFAULT ""
    )');

    $menuModel = new MenuModel();
    $installRes = $menuModel->installModuleMenu($menuTree, ['module' => 'common']);
    assertCommonMenuConsistency(($installRes['status'] ?? false) === true, 'installModuleMenu 导入应成功');

    // 验证新菜单下 InsertQueueMonitorMenu 可正确找到“系统管理”根菜单
    /** @var InsertQueueMonitorMenu $migration */
    $migration = (new ReflectionClass(InsertQueueMonitorMenu::class))->newInstanceWithoutConstructor();
    $migration->up();
    $rootId = Db::name('menu')
        ->where('parentid', 0)
        ->where('app', 'common')
        ->where('name', '系统管理')
        ->value('id');
    assertCommonMenuConsistency(intval($rootId) > 0, '新菜单下应能定位系统管理根菜单');

    // 验证旧菜单结构（common/index/index）下 InsertQueueMonitorMenu 仍保持兼容
    Db::name('menu')->delete(true);
    $legacyRootId = Db::name('menu')->insertGetId([
        'name' => '系统管理',
        'parentid' => 0,
        'app' => 'common',
        'controller' => 'index',
        'action' => 'index',
        'type' => 0,
        'status' => 1,
    ]);
    $migration->up();
    $queueGroupParentId = Db::name('menu')
        ->where('app', 'common')
        ->where('controller', 'queue.dashboard')
        ->where('action', 'index')
        ->where('type', 0)
        ->value('parentid');
    assertCommonMenuConsistency(
        intval($queueGroupParentId) === intval($legacyRootId),
        '旧菜单下应继续挂接到原 common/index/index 根菜单'
    );

    echo "common_menu_consistency_test passed\n";
} finally {
    restore_error_handler();
}
