<?php

namespace app\common\cronscript;


use app\common\model\cron\CronLogModel;
use app\common\model\cron\CronSchedulingLogModel;
use think\facade\Config;

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
        $limit_time = time() - $delete_log_days * 86400;
        $res1 = CronLogModel::where('start_time', '<=', $limit_time)->delete();

        $res2 = CronSchedulingLogModel::where('start_time', '<=', $limit_time)->delete();

        return self::createReturn(true, [
            'delete_cron_log_amount'            => $res1, // 删除计划任务日志记录数
            'delete_cron_scheduling_log_amount' => $res2, // 删除调度运行日志记录数
        ], '操作完成');
    }
}