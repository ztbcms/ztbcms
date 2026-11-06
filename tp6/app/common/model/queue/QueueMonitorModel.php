<?php
/**
 * Author: jayinton
 */

namespace app\common\model\queue;

use think\Model;

/**
 * 队列执行记录
 */
class QueueMonitorModel extends Model
{
    protected $name = 'queue_monitor';

    /**
     * 状态：执行中
     */
    const STATUS_RUNNING = 'running';
    /**
     * 状态：成功（任务已删除）
     */
    const STATUS_SUCCEEDED = 'succeeded';
    /**
     * 状态：重新入队（任务内部 release）
     */
    const STATUS_RELEASED = 'released';
    /**
     * 状态：最终失败（已写入失败任务表）
     */
    const STATUS_FAILED = 'failed';
    /**
     * 状态：执行抛出异常（未达最大次数，将重试）
     */
    const STATUS_EXCEPTION = 'exception';
    /**
     * 状态：执行超时被 worker 强制终止
     */
    const STATUS_TIMEOUT = 'timeout';
    /**
     * 状态：执行中 worker 退出
     */
    const STATUS_INTERRUPTED = 'interrupted';
    /**
     * 状态：执行结束但未删除也未重新入队
     */
    const STATUS_UNACKED = 'unacked';
    /**
     * 状态：长时间执行中，判定为丢失（cron 兜底）
     */
    const STATUS_LOST = 'lost';

    /**
     * 状态名称映射
     */
    const STATUS_TEXT = [
        self::STATUS_RUNNING     => '执行中',
        self::STATUS_SUCCEEDED   => '成功',
        self::STATUS_RELEASED    => '重新入队',
        self::STATUS_FAILED      => '失败',
        self::STATUS_EXCEPTION   => '异常',
        self::STATUS_TIMEOUT     => '超时终止',
        self::STATUS_INTERRUPTED => '中断',
        self::STATUS_UNACKED     => '未确认',
        self::STATUS_LOST        => '丢失',
    ];

    /**
     * 视为执行出错的状态
     */
    const ERROR_STATUSES = [
        self::STATUS_FAILED,
        self::STATUS_EXCEPTION,
        self::STATUS_TIMEOUT,
        self::STATUS_INTERRUPTED,
        self::STATUS_LOST,
    ];
}
