<?php

use app\common\libs\helper\PaginationHelper;

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

/**
 * 断言两个值严格相等
 *
 * @param mixed $expected 预期值
 * @param mixed $actual 实际值
 * @param string $message 错误信息
 * @return void
 */
function assertPaginationSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

/**
 * 断言条件成立
 *
 * @param bool $condition 断言条件
 * @param string $message 错误信息
 * @return void
 */
function assertPaginationTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

assertPaginationSame(3, PaginationHelper::normalizePage('3'), '数字字符串页码处理错误');
assertPaginationSame(3, PaginationHelper::normalizePage(' 3 '), '带空格的数字字符串页码处理错误');
assertPaginationSame(1, PaginationHelper::normalizePage(0), '零页码应使用默认值');
assertPaginationSame(1, PaginationHelper::normalizePage(-1), '负数页码应使用默认值');
assertPaginationSame(1, PaginationHelper::normalizePage('invalid'), '非数字页码应使用默认值');
assertPaginationSame(1, PaginationHelper::normalizePage([]), '数组页码应使用默认值');

assertPaginationSame(20, PaginationHelper::normalizeLimit('20'), '数字字符串每页记录数处理错误');
assertPaginationSame(20, PaginationHelper::normalizeLimit(0), '零每页记录数应使用默认值');
assertPaginationSame(20, PaginationHelper::normalizeLimit(-1), '负数每页记录数应使用默认值');
assertPaginationSame(20, PaginationHelper::normalizeLimit('invalid'), '非数字每页记录数应使用默认值');
assertPaginationSame(20, PaginationHelper::normalizeLimit([]), '数组每页记录数应使用默认值');
assertPaginationSame(100, PaginationHelper::normalizeLimit(101), '超大每页记录数应限制为 100');
assertPaginationSame(15, PaginationHelper::normalizeLimit(0, 15), '自定义默认每页记录数处理错误');

$safeLimit = PaginationHelper::normalizeLimit(0);
assertPaginationSame(1.0, ceil(1 / $safeLimit), '归一化后的每页记录数应可安全计算总页数');
assertPaginationSame([[['id' => 1]]], array_chunk([['id' => 1]], $safeLimit), '归一化后的每页记录数应可安全分割数组');

$paginationFiles = [
    'app/admin/controller/Module.php' => [1, 1],
    'app/admin/service/AdminMessageService.php' => [1, 1],
    'app/admin/controller/Logs.php' => [2, 2],
    'app/admin/service/UserOperateLogService.php' => [1, 1],
    'app/common/controller/cron/Dashboard.php' => [2, 2],
    'app/common/controller/email/Email.php' => [1, 1],
];

foreach ($paginationFiles as $relativePath => [$pageCount, $limitCount]) {
    $source = file_get_contents(dirname(__DIR__) . '/' . $relativePath);
    assertPaginationTrue($source !== false, $relativePath . ' 读取失败');
    assertPaginationSame($pageCount, substr_count($source, 'PaginationHelper::normalizePage('), $relativePath . ' 页码归一化接入数量错误');
    assertPaginationSame($limitCount, substr_count($source, 'PaginationHelper::normalizeLimit('), $relativePath . ' 每页记录数归一化接入数量错误');
}

$cronSource = file_get_contents(dirname(__DIR__) . '/app/common/controller/cron/Dashboard.php');
assertPaginationTrue(
    strpos($cronSource, '$useTime > 0 ? round(1000 / $useTime, 1) : 0.0') !== false,
    'Cron 吞吐量计算缺少零耗时保护'
);

echo "php83 pagination compatibility tests passed\n";
