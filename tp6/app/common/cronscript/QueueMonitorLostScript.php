<?php
/**
 * Author: jayinton
 */

namespace app\common\cronscript;

use app\common\service\queue\QueueMonitorMaintainService;

/**
 * 队列监控-标记丢失的执行记录
 * 执行中超过 monitor.lost_after_seconds 仍未结束的记录（如 worker 被外部 kill）标记为丢失，建议每 10 分钟执行
 */
class QueueMonitorLostScript extends CronScript
{
    /**
     * 执行标记
     *
     * @param string $cronId 计划任务ID
     * @return array
     */
    public function run($cronId)
    {
        $marked = (new QueueMonitorMaintainService())->markLost();

        return self::createReturn(true, ['marked' => $marked], '标记丢失的队列执行记录 ' . $marked . ' 条');
    }
}
