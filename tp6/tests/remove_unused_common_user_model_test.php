<?php

use app\admin\model\AdminUserModel;
use think\App;

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
function assertRemoveUnusedCommonUserModel(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证文件已删除且类不可加载
    $userModelFile = dirname(__DIR__) . '/app/common/model/UserModel.php';
    assertRemoveUnusedCommonUserModel(!file_exists($userModelFile), 'app/common/model/UserModel.php 应已删除');
    assertRemoveUnusedCommonUserModel(
        !class_exists('app\\common\\model\\UserModel'),
        'app\\common\\model\\UserModel 类不应再存在'
    );

    // 2. 验证后台用户模型 AdminUserModel 仍可正常加载
    assertRemoveUnusedCommonUserModel(
        class_exists(AdminUserModel::class),
        'app\\admin\\model\\AdminUserModel 应正常存在'
    );

    // 3. 验证 tp6/app 下无任何对 app\common\model\UserModel 的残留引用
    $appIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__) . '/app', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($appIterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $content = file_get_contents($fileInfo->getPathname());
        assertRemoveUnusedCommonUserModel(is_string($content), '读取文件失败: ' . $fileInfo->getPathname());
        assertRemoveUnusedCommonUserModel(
            strpos($content, 'common\\model\\UserModel') === false,
            '源码中不应存在 common\\model\\UserModel 引用: ' . $fileInfo->getPathname()
        );
    }

    echo "remove_unused_common_user_model_test passed\n";
} finally {
    restore_error_handler();
}
