<?php

use app\admin\model\MenuModel;
use app\admin\service\MenuService;
use app\admin\service\RbacService;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\middleware\SessionInit;
use think\Request;

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
function assertCookieAndIconTrue(bool $condition, string $message): void
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
function assertCookieAndIconSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            '断言失败: ' . $message . ' | 预期: ' . var_export($expected, true) . ' | 实际: ' . var_export($actual, true)
        );
    }
}

/**
 * 递归删除临时目录
 *
 * @param string $dir 目录路径
 * @return void
 */
function removeTempDirectoryRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($dir);
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_cookie_httponly_icon_cache_' . getmypid() . '_' . uniqid('', true) . '/';
$tmpRuntimeDir = sys_get_temp_dir() . '/ztbcms_cookie_httponly_icon_runtime_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    $app->http->name('admin');
    $app->setAppPath($app->getBasePath() . 'admin' . DIRECTORY_SEPARATOR);
    $app->setNamespace('app\\admin');
    $app->setRuntimePath($tmpRuntimeDir);

    // 1. 验证 Cookie 配置文件及 SessionInit 中间件下发 HttpOnly 属性
    $cookieFileConfig = require dirname(__DIR__) . '/config/cookie.php';
    assertCookieAndIconSame(
        true,
        $cookieFileConfig['httponly'] ?? null,
        'config/cookie.php 中 httponly 必须设为 true'
    );
    assertCookieAndIconSame(
        true,
        Config::get('cookie.httponly'),
        '运行期 Config::get("cookie.httponly") 必须为 true'
    );

    configureIsolatedTestFileCache($tmpCacheDir, ['prefix' => 'test_']);

    $request = new Request();
    $request->withServer([
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_HOST' => 'localhost',
        'REQUEST_URI' => '/admin/Dashboard/index',
    ]);
    $app->instance('request', $request);

    $sessionInit = new SessionInit($app, $app->session);
    $response = $sessionInit->handle($request, function () {
        return response('ok');
    });
    $sessionInit->end($response);

    $sessionName = (string) Config::get('session.name', 'SID');
    $pendingCookies = $app->cookie->getCookie();
    assertCookieAndIconTrue(
        isset($pendingCookies[$sessionName]) && is_array($pendingCookies[$sessionName]),
        "SessionInit 应下发名为 {$sessionName} 的 Cookie"
    );
    $sessionCookieOption = $pendingCookies[$sessionName][2] ?? [];
    assertCookieAndIconSame(
        true,
        $sessionCookieOption['httponly'] ?? null,
        "SessionInit 下发的 {$sessionName} Cookie 必须包含 httponly => true"
    );

    // 2. 验证 MenuModel::isValidIcon 与 MenuModel::sanitizeIcon
    assertCookieAndIconTrue(MenuModel::isValidIcon(''), '空字符串应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon(null), 'null 应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon('icon-dashboard'), '标准 iconfont 应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon('icon-icon_setting'), '下划线图标应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon('icon-Network-Error'), '大小写字母图标应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon('icon--kefu-xianxing'), '双短横图标应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon('el-icon-s-tools'), 'Element UI 图标应允许');
    assertCookieAndIconTrue(MenuModel::isValidIcon(str_repeat('a', 100)), '100 字符边界值应允许');

    $invalidIcons = [
        '"><img src=x onerror=alert(document.cookie)>',
        '"><svg onload=alert(1)>',
        'icon-dashboard" onmouseover="alert(1)',
        'icon-check hid',
        "icon-check\n",
        "icon-check\r\n",
        "\ticon-check",
        str_repeat('a', 101),
        ['icon-check'],
        true,
        123,
    ];
    foreach ($invalidIcons as $badIcon) {
        assertCookieAndIconTrue(
            !MenuModel::isValidIcon($badIcon),
            'MenuModel::isValidIcon 必须拒绝非法图标: ' . var_export($badIcon, true)
        );
        assertCookieAndIconSame(
            '',
            MenuModel::sanitizeIcon($badIcon),
            'MenuModel::sanitizeIcon 必须将非法图标清洗为空串: ' . var_export($badIcon, true)
        );
    }
    assertCookieAndIconSame('icon-check', MenuModel::sanitizeIcon(' icon-check '), 'sanitizeIcon 应去除首尾普通空格');

    // 3. 初始化内存 SQLite 数据库，验证 MenuService::addEditDetails 校验与落库
    Config::set([
        'default' => 'admin_cookie_icon_xss_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_cookie_icon_xss_test' => [
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
        icon VARCHAR(255) NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(64) NOT NULL,
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE access (
        role_id INTEGER NOT NULL,
        app VARCHAR(64) NOT NULL,
        controller VARCHAR(64) NOT NULL,
        action VARCHAR(64) NOT NULL,
        status INTEGER NOT NULL DEFAULT 1
    )');

    Db::name('role')->insert([
        'id' => 1,
        'name' => '超级管理员',
        'parentid' => 0,
        'status' => 1,
    ]);
    Cache::tag(RbacService::CacheTagName)->clear();

    $validCases = [
        ['label' => '空字符串', 'input' => '', 'expected' => ''],
        ['label' => '未传 icon 键', 'omit' => true, 'expected' => ''],
        ['label' => 'null 值', 'input' => null, 'expected' => ''],
        ['label' => 'icon-dashboard', 'input' => 'icon-dashboard', 'expected' => 'icon-dashboard'],
        ['label' => 'icon-icon_setting', 'input' => 'icon-icon_setting', 'expected' => 'icon-icon_setting'],
        ['label' => 'icon-Network-Error', 'input' => 'icon-Network-Error', 'expected' => 'icon-Network-Error'],
        ['label' => 'icon--kefu-xianxing', 'input' => 'icon--kefu-xianxing', 'expected' => 'icon--kefu-xianxing'],
        ['label' => 'el-icon-s-tools', 'input' => 'el-icon-s-tools', 'expected' => 'el-icon-s-tools'],
        ['label' => '首尾普通空格', 'input' => ' icon-check ', 'expected' => 'icon-check'],
        ['label' => '100 字符边界值', 'input' => str_repeat('a', 100), 'expected' => str_repeat('a', 100)],
    ];

    $basePayload = [
        'parentid' => 0,
        'name' => '测试菜单',
        'app' => 'Admin',
        'controller' => 'Dashboard',
        'action' => 'index',
        'type' => 1,
        'status' => 1,
    ];

    // 3.1 合法图标新增校验
    foreach ($validCases as $index => $case) {
        $payload = $basePayload;
        $payload['name'] = '合法新增-' . $index;
        if (empty($case['omit'])) {
            $payload['icon'] = $case['input'];
        }
        $res = MenuService::addEditDetails($payload);
        assertCookieAndIconTrue(
            ($res['status'] ?? false) === true,
            "新增合法图标 [{$case['label']}] 应成功，实际错误: " . ($res['msg'] ?? '')
        );
        $saved = Db::name('menu')->where('name', $payload['name'])->find();
        assertCookieAndIconSame(
            $case['expected'],
            $saved['icon'] ?? null,
            "新增合法图标 [{$case['label']}] 入库值不符"
        );
    }

    // 3.2 合法图标编辑校验
    $editTargetId = (int) Db::name('menu')->where('name', '合法新增-0')->value('id');
    assertCookieAndIconTrue($editTargetId > 0, '编辑目标菜单应存在');
    foreach ($validCases as $index => $case) {
        $payload = array_merge($basePayload, [
            'id' => $editTargetId,
            'name' => '合法编辑-' . $index,
        ]);
        if (empty($case['omit'])) {
            $payload['icon'] = $case['input'];
        } else {
            // 未传 icon 键时至少保留已存在的合法值，先重置为已知值验证接口不报错
            Db::name('menu')->where('id', $editTargetId)->update(['icon' => 'icon-check']);
        }
        $res = MenuService::addEditDetails($payload);
        assertCookieAndIconTrue(
            ($res['status'] ?? false) === true,
            "编辑合法图标 [{$case['label']}] 应成功，实际错误: " . ($res['msg'] ?? '')
        );
        if (empty($case['omit'])) {
            $saved = Db::name('menu')->where('id', $editTargetId)->find();
            assertCookieAndIconSame(
                $case['expected'],
                $saved['icon'] ?? null,
                "编辑合法图标 [{$case['label']}] 入库值不符"
            );
        }
    }

    // 3.3 非法图标新增与编辑拦截校验
    $countBeforeInvalidAdd = (int) Db::name('menu')->count();
    Db::name('menu')->where('id', $editTargetId)->update(['icon' => 'icon-safe']);

    foreach ($invalidIcons as $index => $badIcon) {
        // 新增拦截
        $addPayload = array_merge($basePayload, [
            'name' => '非法新增-' . $index,
            'icon' => $badIcon,
        ]);
        $addRes = MenuService::addEditDetails($addPayload);
        assertCookieAndIconSame(
            false,
            $addRes['status'] ?? null,
            '新增非法图标必须被拒绝: ' . var_export($badIcon, true)
        );
        assertCookieAndIconSame(
            $countBeforeInvalidAdd,
            (int) Db::name('menu')->count(),
            '新增非法图标被拒绝后不得写入数据库'
        );

        // 编辑拦截
        $editPayload = array_merge($basePayload, [
            'id' => $editTargetId,
            'name' => '非法编辑-' . $index,
            'icon' => $badIcon,
        ]);
        $editRes = MenuService::addEditDetails($editPayload);
        assertCookieAndIconSame(
            false,
            $editRes['status'] ?? null,
            '编辑非法图标必须被拒绝: ' . var_export($badIcon, true)
        );
        assertCookieAndIconSame(
            'icon-safe',
            Db::name('menu')->where('id', $editTargetId)->value('icon'),
            '编辑非法图标被拒绝后不得篡改原有记录'
        );
    }

    // 4. 验证 MenuModel::getAdminUserMenuTree 与 installModuleMenu 输出/安装兜底清洗
    Db::execute('DELETE FROM menu');
    Db::name('menu')->insertAll([
        [
            'id' => 101,
            'name' => '合法图标1',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Index',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 1,
            'icon' => 'icon-icon_setting',
        ],
        [
            'id' => 102,
            'name' => '合法图标2',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Role',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 2,
            'icon' => 'el-icon-setting',
        ],
        [
            'id' => 103,
            'name' => '合法空图标',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Logs',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 3,
            'icon' => '',
        ],
        [
            'id' => 104,
            'name' => '脏数据XSS',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Menu',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 4,
            'icon' => '"><img src=x onerror=alert(1)>',
        ],
        [
            'id' => 105,
            'name' => '脏数据空格类名',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Cache',
            'action' => 'cache',
            'type' => 1,
            'status' => 1,
            'listorder' => 5,
            'icon' => 'icon-a hid',
        ],
        [
            'id' => 106,
            'name' => '脏数据换行符',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Main',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 6,
            'icon' => "icon-a\n",
        ],
        [
            'id' => 107,
            'name' => '脏数据超长',
            'parentid' => 0,
            'app' => 'Admin',
            'controller' => 'Config',
            'action' => 'index',
            'type' => 1,
            'status' => 1,
            'listorder' => 7,
            'icon' => str_repeat('a', 101),
        ],
    ]);

    $menuModel = new MenuModel();
    $menuTree = $menuModel->getAdminUserMenuTree(1);
    $iconByName = [];
    foreach ($menuTree as $item) {
        $iconByName[$item['name']] = $item['icon'];
    }

    assertCookieAndIconSame('icon-icon_setting', $iconByName['合法图标1'] ?? null, '合法图标1应原样保留');
    assertCookieAndIconSame('el-icon-setting', $iconByName['合法图标2'] ?? null, '合法图标2应原样保留');
    assertCookieAndIconSame('', $iconByName['合法空图标'] ?? null, '空图标应为空字符串');
    assertCookieAndIconSame('', $iconByName['脏数据XSS'] ?? null, '存储型 XSS 脏数据必须被清洗为空字符串');
    assertCookieAndIconSame('', $iconByName['脏数据空格类名'] ?? null, '含空格的多类名脏数据必须被清洗为空字符串');
    assertCookieAndIconSame('', $iconByName['脏数据换行符'] ?? null, '含换行符脏数据必须被清洗为空字符串');
    assertCookieAndIconSame('', $iconByName['脏数据超长'] ?? null, '超长脏数据必须被清洗为空字符串');

    // 验证 installModuleMenu 同样清洗非法图标
    $installRes = $menuModel->installModuleMenu([
        [
            'name' => '模块恶意菜单',
            'route' => 'Admin/Demo/bad',
            'parentid' => 0,
            'status' => 1,
            'icon' => '"><img src=x onerror=alert(1)>',
        ],
        [
            'name' => '模块正常菜单',
            'route' => 'Admin/Demo/good',
            'parentid' => 0,
            'status' => 1,
            'icon' => 'icon-check',
        ],
    ], ['module' => 'Admin']);
    assertCookieAndIconTrue(($installRes['status'] ?? false) === true, 'installModuleMenu 应执行成功');
    assertCookieAndIconSame(
        '',
        Db::name('menu')->where('name', '模块恶意菜单')->value('icon'),
        'installModuleMenu 应将非法图标清洗为空串'
    );
    assertCookieAndIconSame(
        'icon-check',
        Db::name('menu')->where('name', '模块正常菜单')->value('icon'),
        'installModuleMenu 应保留合法图标'
    );

    // 5. 验证前端视图文件及整个 admin 视图目录已消除 v-html
    $dashboardViewPath = dirname(__DIR__) . '/app/admin/view/dashboard/index.php';
    $dashboardView = file_get_contents($dashboardViewPath);
    assertCookieAndIconTrue($dashboardView !== false, '应能读取 dashboard/index.php');
    assertCookieAndIconTrue(
        strpos($dashboardView, 'v-html') === false,
        'dashboard/index.php 中不得再包含 v-html'
    );
    assertCookieAndIconTrue(
        strpos($dashboardView, 'icon_html') === false,
        'dashboard/index.php 中不得再包含 icon_html'
    );
    assertCookieAndIconTrue(
        strpos($dashboardView, ':class="[item.icon]"') !== false,
        'dashboard/index.php 应使用 :class="[item.icon]" 安全绑定图标类名'
    );

    $loginLogViewPath = dirname(__DIR__) . '/app/admin/view/logs/loginLogList.php';
    $loginLogView = file_get_contents($loginLogViewPath);
    assertCookieAndIconTrue($loginLogView !== false, '应能读取 logs/loginLogList.php');
    assertCookieAndIconTrue(
        strpos($loginLogView, 'v-html') === false,
        'logs/loginLogList.php 中不得再包含 v-html'
    );

    $adminViewDir = dirname(__DIR__) . '/app/admin/view';
    $viewIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($adminViewDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($viewIterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $ext = strtolower($fileInfo->getExtension());
        if (!in_array($ext, ['php', 'html'], true)) {
            continue;
        }
        $content = file_get_contents($fileInfo->getPathname());
        assertCookieAndIconTrue(
            $content !== false && strpos($content, 'v-html') === false,
            'admin 视图目录不应再使用 v-html: ' . $fileInfo->getPathname()
        );
    }
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
} finally {
    restore_error_handler();
    removeTempDirectoryRecursive($tmpCacheDir);
    removeTempDirectoryRecursive($tmpRuntimeDir);
}

echo "admin_cookie_httponly_menu_icon_xss_test passed\n";
