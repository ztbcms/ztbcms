<?php

namespace app\common\libs\helper;

/**
 * 分页参数处理助手
 */
class PaginationHelper
{
    /** 单页最大记录数 */
    const MAX_LIMIT = 100;

    /**
     * 归一化页码
     *
     * 接受整数或去除首尾空格后的整数格式字符串
     * 正整数原样返回，0、负数、非数字字符串、数组及其他类型返回默认页码
     *
     * @param mixed $value 原始页码
     * @param int $default 默认页码
     * @return int
     */
    static function normalizePage($value, int $default = 1): int
    {
        return self::normalizePositiveInteger($value, $default);
    }

    /**
     * 归一化每页记录数
     *
     * 接受整数或去除首尾空格后的整数格式字符串
     * 0、负数、非数字字符串、数组及其他类型先返回默认记录数
     * 有效记录数或默认记录数超过最大值时返回最大值
     *
     * @param mixed $value 原始每页记录数
     * @param int $default 默认每页记录数
     * @param int $max 单页最大记录数
     * @return int
     */
    static function normalizeLimit($value, int $default = 20, int $max = self::MAX_LIMIT): int
    {
        return min(self::normalizePositiveInteger($value, $default), $max);
    }

    /**
     * 归一化正整数
     *
     * 整数和整数格式字符串转换为整数后必须大于 0
     * 浮点数、布尔值、null、数组、对象及非整数格式字符串均返回默认值
     *
     * @param mixed $value 原始值
     * @param int $default 默认值
     * @return int
     */
    private static function normalizePositiveInteger($value, int $default): int
    {
        if (is_int($value)) {
            $number = $value;
        } elseif (is_string($value) && preg_match('/^[+-]?\d+$/D', trim($value)) === 1) {
            $number = (int) trim($value);
        } else {
            return $default;
        }

        return $number > 0 ? $number : $default;
    }
}
