<?php

use app\admin\service\AdminUserService;
use app\common\controller\upload\Panel;
use think\App;
use think\facade\Cache;
use think\facade\Config;
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
function assertUploadPanelImageUEUpload(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tempCacheDir = sys_get_temp_dir() . '/ztbcms_panel_ue_test_' . uniqid('', true);
if (!is_dir($tempCacheDir)) {
    mkdir($tempCacheDir, 0777, true);
}

try {
    // 1. 源码导入检查
    $panelSource = file_get_contents(dirname(__DIR__) . '/app/common/controller/upload/Panel.php');
    assertUploadPanelImageUEUpload(
        is_string($panelSource) && strpos($panelSource, 'use app\admin\service\AdminUserService;') !== false,
        'Panel.php 必须导入 app\admin\service\AdminUserService'
    );

    // 2. 配置独立 SQLite 内存库与缓存目录
    Config::set([
        'default' => 'upload_panel_ue_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'upload_panel_ue_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $tempCacheDir . DIRECTORY_SEPARATOR,
                'prefix' => '',
                'expire' => 0,
            ],
        ],
    ], 'cache');
    Cache::clear();

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        value TEXT NOT NULL
    )');
    Db::name('config')->insertAll([
        ['varname' => 'attachment_driver', 'value' => 'Local'],
        ['varname' => 'attachment_local_domain', 'value' => 'http://127.0.0.1'],
        ['varname' => 'uploadmaxsize', 'value' => '2048'],
        ['varname' => 'uploadallowext', 'value' => 'jpg|png'],
        ['varname' => 'qtuploadmaxsize', 'value' => '2048'],
        ['varname' => 'qtuploadallowext', 'value' => 'jpg|png'],
    ]);

    /** @var Panel $controller */
    $controller = (new ReflectionClass(Panel::class))->newInstanceWithoutConstructor();

    // 3. 测试未登录状态下调用 imageUEUpload POST 分支不会因缺少 use 或 null 访问报错
    Session::delete(AdminUserService::KEY_USER_INFO);
    $userInfoProp = new ReflectionProperty(AdminUserService::class, 'userInfo');
    $userInfoProp->setAccessible(true);
    $userInfoProp->setValue(null, []);

    $guestRequest = new Request();
    $guestRequest->withServer(['REQUEST_METHOD' => 'POST'])
        ->withPost(['group_id' => 'all']);
    $app->instance('request', $guestRequest);

    $guestResp = $controller->imageUEUpload($guestRequest);
    assertUploadPanelImageUEUpload(
        $guestResp instanceof \think\response\Json,
        'POST 调用 imageUEUpload 应返回 Json 响应'
    );
    $guestData = $guestResp->getData();
    assertUploadPanelImageUEUpload(
        ($guestData['status'] ?? true) === false,
        '未上传文件时 status 应为 false'
    );
    assertUploadPanelImageUEUpload(
        ($guestData['msg'] ?? '') === '未选择文件或文件过大',
        '未上传文件时应正常走入 UploadService 错误返回，实际为: ' . ($guestData['msg'] ?? '')
    );

    // 4. 测试已登录管理员状态下调用 imageUEUpload POST 分支正常解析 AdminUserService
    $userInfoProp->setValue(null, ['id' => 101, 'username' => 'admin']);
    $adminRequest = new Request();
    $adminRequest->withServer(['REQUEST_METHOD' => 'POST'])
        ->withPost(['group_id' => '3']);
    $app->instance('request', $adminRequest);

    $adminResp = $controller->imageUEUpload($adminRequest);
    $adminData = $adminResp->getData();
    assertUploadPanelImageUEUpload(
        ($adminData['status'] ?? true) === false && ($adminData['msg'] ?? '') === '未选择文件或文件过大',
        '已登录状态下调用 imageUEUpload 应正常走到 UploadService 校验'
    );

    $userInfoProp->setValue(null, []);
    echo "upload_panel_image_ue_upload_test passed\n";
} finally {
    restore_error_handler();
    if (is_dir($tempCacheDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempCacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tempCacheDir);
    }
}
