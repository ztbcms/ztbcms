<?php

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

function assertOpLogXss(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

$opLogView = file_get_contents(dirname(__DIR__) . '/app/admin/view/logs/adminOperationLogList.php');
assertOpLogXss(is_string($opLogView), '读取 adminOperationLogList.php 失败');
assertOpLogXss(
    strpos($opLogView, "var escapedContent = \$('<div>').text(content).html();") !== false,
    'adminOperationLogList.php previewContent 必须对 content 做 HTML 实体转义'
);
assertOpLogXss(
    strpos($opLogView, "content: '<pre style=\"padding: 4px 8px\">' + escapedContent + '</pre>'") !== false,
    'adminOperationLogList.php layer.open 必须使用转义后的 escapedContent'
);
assertOpLogXss(
    strpos($opLogView, "'+ content +'") === false && strpos($opLogView, "' + content + '") === false,
    'adminOperationLogList.php 不得直接拼接未转义的 content'
);

$loginLogView = file_get_contents(dirname(__DIR__) . '/app/admin/view/logs/loginLogList.php');
assertOpLogXss(is_string($loginLogView), '读取 loginLogList.php 失败');
assertOpLogXss(
    strpos($loginLogView, 'v-html') === false,
    'loginLogList.php 不应存在 v-html 注入点'
);
assertOpLogXss(
    strpos($loginLogView, '<span>{{ scope.row.info }}</span>') !== false,
    'loginLogList.php 应使用安全文本插值渲染 info'
);

$cronLogView = file_get_contents(dirname(__DIR__) . '/app/common/view/cron/dashboard/cronLog.php');
assertOpLogXss(is_string($cronLogView), '读取 cronLog.php 失败');
assertOpLogXss(
    strpos($cronLogView, "var escapedDetail = \$('<div>').text(detail).html();") !== false,
    'cronLog.php openDetail 必须对 detail 做 HTML 实体转义'
);

echo "admin_operation_log_xss_test passed\n";
