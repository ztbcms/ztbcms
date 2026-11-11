<?php
/**
 * Author: jayinton
 */

namespace app\common\cronscript;

use app\common\service\queue\QueueMonitorMaintainService;

/**
 * 队列监控-清理过期执行记录
 * 建议每天执行一次，保留天数见 config/queue.php 的 monitor.keep_days
 */
class QueueMonitorCleanScript extends CronScript
{
    /**
     * 执行清理
     *
     * @param string $cronId 计划任务ID
     * @return array
     */
    public function run($cronId)
    {
        $deleted = (new QueueMonitorMaintainService())->cleanExpired();

        return self::createReturn(true, ['deleted' => $deleted], '清理过期队列执行记录 ' . $deleted . ' 条');
    }
}
