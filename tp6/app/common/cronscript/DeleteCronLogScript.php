<?php

namespace app\common\cronscript;


use app\common\model\cron\CronLogModel;
use app\common\model\cron\CronSchedulingLogModel;
use think\facade\Log;
use think\facade\Config;


class DeleteCronLogScript extends CronScript
{
    public function run($cronId)
    {
        $delete_log_days = Config::get('cron.delete_log_days', 30);
        $limit_time = time() - $delete_log_days * 86400;
        $res = CronLogModel::where('start_time', '<=', $limit_time)->delete();
        Log::info('[Cron/DeleteCronLogScript]'.'删除计划任务日志记录数:'.$res);

        $res = CronSchedulingLogModel::where('start_time', '<=', $limit_time)->delete();
        Log::info('[Cron/DeleteCronLogScript]'.'删除调度运行日志记录数:'.$res);

        return self::createReturn(true,[
            'start_time' => $limit_time
        ],'删除计划任务日志成功');
    }
}