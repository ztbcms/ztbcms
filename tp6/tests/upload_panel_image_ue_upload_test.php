<?php

use app\common\controller\upload\Panel;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
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
function assertUploadPanelImageUEUpload(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证 UE 上传整条冗余链路已从控制器、服务、模型与视图中彻底移除
    $panelRef = new ReflectionClass(Panel::class);
    assertUploadPanelImageUEUpload(
        !$panelRef->hasMethod('imageUEUpload'),
        'Panel::imageUEUpload 方法应已移除'
    );

    $uploadServiceRef = new ReflectionClass(UploadService::class);
    assertUploadPanelImageUEUpload(
        !$uploadServiceRef->hasMethod('uploadUEImage'),
        'UploadService::uploadUEImage 方法应已移除'
    );

    $attachmentRef = new ReflectionClass(AttachmentModel::class);
    assertUploadPanelImageUEUpload(
        !$attachmentRef->hasConstant('MODULE_UE_IMAGE'),
        'AttachmentModel::MODULE_UE_IMAGE 常量应已移除'
    );

    $viewFile = dirname(__DIR__) . '/app/common/view/upload/panel/imageUEUpload.php';
    assertUploadPanelImageUEUpload(
        !file_exists($viewFile),
        'view/upload/panel/imageUEUpload.php 视图文件应已删除'
    );

    // 2. 验证 Panel.php 不再保留冗余的 AdminUserService 导入及残留标识符
    $panelSource = file_get_contents(dirname(__DIR__) . '/app/common/controller/upload/Panel.php');
    assertUploadPanelImageUEUpload(
        is_string($panelSource) && strpos($panelSource, 'AdminUserService') === false,
        'Panel.php 应移除仅被 imageUEUpload 使用的 AdminUserService 导入'
    );

    $appIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__) . '/app', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($appIterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $content = file_get_contents($fileInfo->getPathname());
        assertUploadPanelImageUEUpload(is_string($content), '读取文件失败: ' . $fileInfo->getPathname());
        foreach (['imageUEUpload', 'uploadUEImage', 'MODULE_UE_IMAGE', 'ZTBCMS_UPLOAD_UE_IMAGE'] as $needle) {
            assertUploadPanelImageUEUpload(
                stripos($content, $needle) === false,
                "tp6/app 下不应再残留 {$needle} 引用: " . $fileInfo->getPathname()
            );
        }
    }

    // 3. 验证统一上传面板核心能力正常，非法或废弃 module（含 ue_image）会回退到 image
    /** @var Panel $controller */
    $controller = $panelRef->newInstanceWithoutConstructor();
    $normalizeMethod = $panelRef->getMethod('normalizeModule');
    $normalizeMethod->setAccessible(true);

    assertUploadPanelImageUEUpload(
        $normalizeMethod->invoke($controller, 'image') === AttachmentModel::MODULE_IMAGE,
        'normalizeModule(image) 应返回 image'
    );
    assertUploadPanelImageUEUpload(
        $normalizeMethod->invoke($controller, 'VIDEO') === AttachmentModel::MODULE_VIDEO,
        'normalizeModule(VIDEO) 应返回 video'
    );
    assertUploadPanelImageUEUpload(
        $normalizeMethod->invoke($controller, 'file') === AttachmentModel::MODULE_FILE,
        'normalizeModule(file) 应返回 file'
    );
    assertUploadPanelImageUEUpload(
        $normalizeMethod->invoke($controller, 'ue_image') === AttachmentModel::MODULE_IMAGE,
        '废弃模块 ue_image 应安全回退为 image'
    );

    // 4. 验证 getFilesByGroupIdList 在内存 SQLite 下正常查询列表
    Config::set([
        'default' => 'upload_panel_ue_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'upload_panel_ue_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE attachment (
        aid INTEGER PRIMARY KEY AUTOINCREMENT,
        module VARCHAR(32) NOT NULL DEFAULT "",
        filename VARCHAR(255) NOT NULL DEFAULT "",
        filepath VARCHAR(255) NOT NULL DEFAULT "",
        fileurl VARCHAR(255) NOT NULL DEFAULT "",
        filethumb VARCHAR(255) NOT NULL DEFAULT "",
        filesize INTEGER NOT NULL DEFAULT 0,
        user_type VARCHAR(32) NOT NULL DEFAULT "",
        group_id INTEGER NOT NULL DEFAULT 0,
        is_private INTEGER NOT NULL DEFAULT 0,
        driver VARCHAR(32) NOT NULL DEFAULT "Local",
        create_time INTEGER NOT NULL DEFAULT 0,
        delete_time INTEGER NOT NULL DEFAULT 0
    )');
    Db::name('attachment')->insert([
        'module' => AttachmentModel::MODULE_IMAGE,
        'filename' => 'demo.png',
        'filepath' => 'image/demo.png',
        'fileurl' => '/d/file/image/demo.png',
        'filethumb' => '/d/file/image/demo.png',
        'filesize' => 1024,
        'user_type' => AttachmentModel::USER_TYPE_ADMIN,
        'group_id' => 0,
        'create_time' => 1700000000,
        'delete_time' => 0,
    ]);

    $listRequest = new Request();
    $listRequest->withGet(['module' => 'ue_image', 'group_id' => 'all', 'limit' => 10]);
    $listRes = $controller->getFilesByGroupIdList($listRequest);
    assertUploadPanelImageUEUpload(
        ($listRes['status'] ?? false) === true
        && ($listRes['data']['setting']['module'] ?? '') === AttachmentModel::MODULE_IMAGE
        && intval($listRes['data']['file_list']->total()) === 1,
        'getFilesByGroupIdList 应正常返回统一图片列表并回退模块为 image'
    );

    echo "upload_panel_image_ue_upload_test passed\n";
} finally {
    restore_error_handler();
}
