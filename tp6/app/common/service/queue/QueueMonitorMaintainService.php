<?php
/**
 * Author: jayinton
 */

namespace app\common\service\queue;

use app\common\model\queue\QueueMonitorModel;
use app\common\service\BaseService;
use think\facade\Db;

/**
 * 队列监控-数据维护（清理、丢失兜底）
 */
class QueueMonitorMaintainService extends BaseService
{
    /**
     * 每批处理数量
     */
    const BATCH_SIZE = 1000;

    /**
     * 删除超过保留天数的执行记录
     *
     * @param int|null $nowMs 当前毫秒（测试用）
     * @return int 删除数量
     */
    public function cleanExpired(?int $nowMs = null): int
    {
        $keepDays = max(1, intval(QueueMonitorService::config('keep_days', 7)));
        $nowMs = $nowMs ?? QueueMonitorService::nowMs();
        $cutoffMs = $nowMs - $keepDays * 86400 * 1000;
        $total = 0;
        while (true) {
            $ids = Db::name(QueueMonitorService::table())
                ->where('started_at', '<', $cutoffMs)
                ->order('id', 'asc')
                ->limit(self::BATCH_SIZE)
                ->column('id');
            if (!$ids) {
                break;
            }
            $total += intval(Db::name(QueueMonitorService::table())->whereIn('id', $ids)->delete());
            if (count($ids) < self::BATCH_SIZE) {
                break;
            }
        }

        return $total;
    }

    /**
     * 将长时间处于执行中的记录标记为丢失
     *
     * @param int|null $nowMs 当前毫秒（测试用）
     * @return int 标记数量
     */
    public function markLost(?int $nowMs = null): int
    {
        $lostAfter = max(60, intval(QueueMonitorService::config('lost_after_seconds', 3600)));
        $nowMs = $nowMs ?? QueueMonitorService::nowMs();

        return intval(Db::name(QueueMonitorService::table())
            ->where('status', QueueMonitorModel::STATUS_RUNNING)
            ->where('started_at', '<', $nowMs - $lostAfter * 1000)
            ->update([
                'status'            => QueueMonitorModel::STATUS_LOST,
                'finished_at'       => $nowMs,
                'exception_message' => '执行超过 ' . $lostAfter . ' 秒未结束，判定为丢失（worker 可能被外部终止）',
            ]));
    }
}
