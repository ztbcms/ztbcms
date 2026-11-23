<?php

namespace app\admin\service;

use app\common\service\BaseService;

/**
 * 后台日志服务
 *
 * @package app\admin\service
 */
class AdminLogService extends BaseService
{
    /**
     * 默认日志保留天数
     */
    const DEFAULT_RETENTION_DAYS = 30;

    /**
     * 日志清理最少保留天数
     */
    const MIN_RETENTION_DAYS = 7;

    /**
     * 日志清理最大保留天数
     */
    const MAX_RETENTION_DAYS = 3650;

    /**
     * 严格解析并校验日志保留天数
     *
     * 仅接受整型或纯整数格式字符串，且必须在允许的保留天数范围内；
     * 负数、0、超范围数值、浮点数、布尔值、null、数组及其他非法格式均返回 null。
     *
     * @param mixed $day 原始保留天数参数
     *
     * @return int|null
     */
    public static function parseRetentionDays($day): ?int
    {
        if (is_int($day)) {
            $days = $day;
        } elseif (is_string($day)) {
            $trimmed = trim($day);
            if (preg_match('/^[+-]?\d+$/D', $trimmed) !== 1) {
                return null;
            }
            if ($trimmed[0] === '-') {
                return null;
            }
            $unsigned = ltrim($trimmed, '+');
            $normalized = ltrim($unsigned, '0');
            if ($normalized === '' || strlen($normalized) > strlen((string) self::MAX_RETENTION_DAYS)) {
                return null;
            }
            $days = (int) $normalized;
        } else {
            return null;
        }

        if ($days < self::MIN_RETENTION_DAYS || $days > self::MAX_RETENTION_DAYS) {
            return null;
        }

        return $days;
    }

    /**
     * 从请求参数数组中解析日志保留天数
     *
     * 未提供 day 参数时回退默认保留天数；只要显式提供 day 参数，则严格校验并在非法时返回 null。
     *
     * @param mixed $params 原始请求参数数组
     *
     * @return int|null
     */
    public static function resolveRetentionDaysFromParams($params): ?int
    {
        if (!is_array($params) || !array_key_exists('day', $params)) {
            return self::DEFAULT_RETENTION_DAYS;
        }

        return self::parseRetentionDays($params['day']);
    }

    /**
     * 获取保留天数校验失败提示文案
     *
     * @return string
     */
    public static function getRetentionDaysErrorMessage(): string
    {
        return '日志保留天数须为 ' . self::MIN_RETENTION_DAYS . ' 至 ' . self::MAX_RETENTION_DAYS . ' 之间的整数';
    }
}
