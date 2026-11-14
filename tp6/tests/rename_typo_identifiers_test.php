<?php

use app\common\controller\upload\Panel;
use app\common\job\downloader\ImplementDownloaderTaskJob;
use app\common\libs\queue\BaseQueueJob;
use app\common\model\upload\AttachmentModel;
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
function assertRenameTypoIdentifiers(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$originalDbConfig = Config::get('database');

try {
    // 1. 验证队列任务文件与类名已修正，旧文件及旧类已移除
    $oldJobFile = dirname(__DIR__) . '/app/common/job/downloader/ImplementDownloaderTaskJop.php';
    $newJobFile = dirname(__DIR__) . '/app/common/job/downloader/ImplementDownloaderTaskJob.php';
    assertRenameTypoIdentifiers(!file_exists($oldJobFile), '旧任务文件 ImplementDownloaderTaskJop.php 应已移除');
    assertRenameTypoIdentifiers(file_exists($newJobFile), '新任务文件 ImplementDownloaderTaskJob.php 应存在');
    assertRenameTypoIdentifiers(
        !class_exists('app\\common\\job\\downloader\\ImplementDownloaderTaskJop'),
        '旧任务类 ImplementDownloaderTaskJop 不应再存在'
    );
    assertRenameTypoIdentifiers(
        class_exists(ImplementDownloaderTaskJob::class)
        && is_subclass_of(ImplementDownloaderTaskJob::class, BaseQueueJob::class),
        'ImplementDownloaderTaskJob 应存在并继承 BaseQueueJob'
    );

    // 2. 验证上传面板控制器方法拼写及前端模板路由同步更新
    $panelRef = new ReflectionClass(Panel::class);
    assertRenameTypoIdentifiers(!$panelRef->hasMethod('moveGralleryGroup'), 'Panel::moveGralleryGroup 旧方法应已移除');
    assertRenameTypoIdentifiers($panelRef->hasMethod('moveGalleryGroup'), 'Panel::moveGalleryGroup 方法应存在');

    $panelViewPath = dirname(__DIR__) . '/app/common/view/upload/panel/index.php';
    $panelViewSource = file_get_contents($panelViewPath);
    assertRenameTypoIdentifiers(
        is_string($panelViewSource)
        && strpos($panelViewSource, "common/upload.panel/moveGalleryGroup") !== false
        && stripos($panelViewSource, 'moveGralleryGroup') === false,
        'upload/panel/index.php 应同步改为调用 common/upload.panel/moveGalleryGroup'
    );

    // 3. 在内存 SQLite 下验证 Panel::moveGalleryGroup 可正确更新附件分组
    Config::set([
        'default' => 'rename_typo_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'rename_typo_test' => [
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
        update_time INTEGER NOT NULL DEFAULT 0,
        delete_time INTEGER NOT NULL DEFAULT 0
    )');
    $aid1 = Db::name('attachment')->insertGetId([
        'module' => AttachmentModel::MODULE_IMAGE,
        'filename' => 'a.png',
        'filepath' => 'image/a.png',
        'fileurl' => '/d/file/image/a.png',
        'filethumb' => '/d/file/image/a.png',
        'filesize' => 100,
        'user_type' => AttachmentModel::USER_TYPE_ADMIN,
        'group_id' => 0,
        'create_time' => 1700000000,
        'update_time' => 0,
        'delete_time' => 0,
    ]);
    $aid2 = Db::name('attachment')->insertGetId([
        'module' => AttachmentModel::MODULE_IMAGE,
        'filename' => 'b.png',
        'filepath' => 'image/b.png',
        'fileurl' => '/d/file/image/b.png',
        'filethumb' => '/d/file/image/b.png',
        'filesize' => 200,
        'user_type' => AttachmentModel::USER_TYPE_ADMIN,
        'group_id' => 1,
        'create_time' => 1700000000,
        'update_time' => 0,
        'delete_time' => 0,
    ]);

    /** @var Panel $panelController */
    $panelController = $panelRef->newInstanceWithoutConstructor();
    $request = new Request();
    $request->withPost([
        'files' => [
            ['aid' => $aid1],
            ['aid' => $aid2],
        ],
        'group_id' => 5,
    ]);
    $moveRes = $panelController->moveGalleryGroup($request);
    assertRenameTypoIdentifiers(
        ($moveRes['status'] ?? false) === true && ($moveRes['msg'] ?? '') === '移动成功',
        'moveGalleryGroup 应返回移动成功，实际: ' . json_encode($moveRes, JSON_UNESCAPED_UNICODE)
    );
    assertRenameTypoIdentifiers(
        intval(Db::name('attachment')->where('aid', $aid1)->value('group_id')) === 5
        && intval(Db::name('attachment')->where('aid', $aid2)->value('group_id')) === 5,
        'moveGalleryGroup 应将选中附件的 group_id 更新为 5'
    );

    // 4. 全仓检查 tp6/app 与 docs 目录，确保无旧拼写残留
    $scanDirs = [
        dirname(__DIR__) . '/app',
        dirname(__DIR__, 2) . '/docs',
    ];
    foreach ($scanDirs as $dir) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $content = file_get_contents($fileInfo->getPathname());
            assertRenameTypoIdentifiers(is_string($content), '读取文件失败: ' . $fileInfo->getPathname());
            foreach (['ImplementDownloaderTaskJop', 'moveGralleryGroup', 'Grallery'] as $typo) {
                assertRenameTypoIdentifiers(
                    stripos($content, $typo) === false,
                    "文件不应再包含拼写错误 {$typo}: " . $fileInfo->getPathname()
                );
            }
        }
    }

    echo "rename_typo_identifiers_test passed\n";
} finally {
    if (is_array($originalDbConfig)) {
        Config::set($originalDbConfig, 'database');
    }
    restore_error_handler();
}
