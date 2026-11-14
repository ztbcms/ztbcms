<?php

use think\App;
use think\facade\Config;
use think\Template;

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
function assertRemoveUnusedTaglib(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tempRoot = sys_get_temp_dir() . '/ztbcms_remove_unused_taglib_test_' . uniqid('', true);
$tempViewDir = $tempRoot . '/view';
$tempCacheDir = $tempRoot . '/cache';
mkdir($tempViewDir, 0777, true);
mkdir($tempCacheDir, 0777, true);

try {
    // 1. 验证冗余标签库文件已彻底移除且不再可自动加载
    $templateFile = dirname(__DIR__) . '/app/common/taglib/Template.php';
    $ztbcmsTaglibFile = dirname(__DIR__) . '/app/common/taglib/Ztbcms.php';
    assertRemoveUnusedTaglib(!file_exists($templateFile), 'taglib/Template.php 应已删除');
    assertRemoveUnusedTaglib(!file_exists($ztbcmsTaglibFile), 'taglib/Ztbcms.php 应已删除');
    assertRemoveUnusedTaglib(
        !class_exists('app\\common\\taglib\\Template'),
        'app\\common\\taglib\\Template 类不应再存在'
    );
    assertRemoveUnusedTaglib(
        !class_exists('app\\common\\taglib\\Ztbcms'),
        'app\\common\\taglib\\Ztbcms 类不应再存在'
    );

    // 2. 验证 config/view.php 已移除 taglib_pre_load 配置
    $viewConfig = require dirname(__DIR__) . '/config/view.php';
    assertRemoveUnusedTaglib(is_array($viewConfig), 'config/view.php 必须返回数组');
    assertRemoveUnusedTaglib(
        !array_key_exists('taglib_pre_load', $viewConfig),
        'config/view.php 应已移除 taglib_pre_load 配置'
    );
    $runtimeViewConfig = Config::get('view');
    assertRemoveUnusedTaglib(
        empty($runtimeViewConfig['taglib_pre_load']),
        '运行时 view 配置中 taglib_pre_load 应为空或不存在'
    );

    // 3. 验证全项目 tp6/app 目录下不再使用 {ztbcms:include 或 app\common\taglib
    $appIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__) . '/app', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($appIterator as $fileInfo) {
        if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($fileInfo->getPathname());
        assertRemoveUnusedTaglib(is_string($content), '读取文件失败: ' . $fileInfo->getPathname());
        assertRemoveUnusedTaglib(
            strpos($content, 'ztbcms:include') === false,
            '项目源码中不应存在 ztbcms:include 标签: ' . $fileInfo->getPathname()
        );
        assertRemoveUnusedTaglib(
            strpos($content, 'app\\common\\taglib') === false,
            '项目源码中不应存在 app\\common\\taglib 引用: ' . $fileInfo->getPathname()
        );
    }

    // 4. 验证使用 config/view.php 初始化 think\Template 后模板编译与渲染正常
    file_put_contents($tempViewDir . '/partial.php', '<span>{$subTitle}</span>');
    file_put_contents(
        $tempViewDir . '/index.php',
        '<div>{include file="partial" /}{if $enabled}OK{/if}</div>'
    );

    $testViewConfig = array_merge($viewConfig, [
        'view_path' => $tempViewDir . DIRECTORY_SEPARATOR,
        'cache_path' => $tempCacheDir . DIRECTORY_SEPARATOR,
        'layout_on' => false,
    ]);
    $template = new Template($testViewConfig);

    ob_start();
    $template->fetch('index', [
        'subTitle' => 'hello-ztbcms',
        'enabled' => true,
    ]);
    $rendered = (string) ob_get_clean();

    assertRemoveUnusedTaglib(
        $rendered === '<div><span>hello-ztbcms</span>OK</div>',
        '使用 config/view.php 渲染标准模板失败，实际输出: ' . $rendered
    );

    echo "remove_unused_taglib_test passed\n";
} finally {
    restore_error_handler();
    if (is_dir($tempRoot)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tempRoot);
    }
}
