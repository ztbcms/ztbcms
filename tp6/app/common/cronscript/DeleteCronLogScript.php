<?php

namespace app\common\cronscript;


use app\common\model\cron\CronLogModel;
use app\common\model\cron\CronSchedulingLogModel;
use think\facade\Config;
use think\facade\Db;

/**
 * 删除日志
 *
 * @package app\common\cronscript
 */
class DeleteCronLogScript extends CronScript
{
    public function run($cronId)
    {
        $delete_log_days = Config::get('cron.delete_log_days', 30);
        $limit_time = strtotime(date('Y-m-d')) - $delete_log_days * 86400 - 1;
        $limit = 1000;
        $res1 = $this->deleteCronLog($limit_time, $limit);
        $res2 = $this->deleteCronSchedulingLog($limit_time, $limit);
        return self::createReturn(true, [
            'delete_cron_log_amount' => $res1,
            'delete_cron_scheduling_log_amount' => $res2,
        ]);
    }

    function deleteCronLog($limit_time, $limit)
    {
        $res = Db::query('select MIN(id) as id from ' . CronLogModel::getTable());
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['start_time', '<=', $limit_time],
            ];
            $delete_amount = CronLogModel::where($where)->delete();
            $total += $delete_amount;
            $start_id += $limit;
            $running = $delete_amount > 0;
        }
        return $total;
    }

    function deleteCronSchedulingLog($limit_time, $limit)
    {
        $res = Db::query('select MIN(id) as id from ' . CronSchedulingLogModel::getTable());
        if (empty($res[0]['id'])) return 0;
        $start_id = $res[0]['id'];
        $total = 0;
        $running = true;
        while ($running) {
            $where = [
                ['id', '>=', $start_id],
                ['id', '<', $start_id + $limit],
                ['start_time', '<=', $limit_time],
            ];
            $delete_amount = CronSchedulingLogModel::where($where)->delete();
            $total += $delete_amount;
            $start_id += $limit;
            $running = $delete_amount > 0;
        }
        return $total;
    }
}