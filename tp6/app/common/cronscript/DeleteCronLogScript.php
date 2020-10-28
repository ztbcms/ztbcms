<?php

namespace app\common\cronscript;

use app\common\model\cron\CronLogModel;
use app\common\model\cron\CronSchedulingLogModel;
use think\facade\Log;

class DeleteCronLogScript extends CronScript
{
    public function run($cronId)
    {
        $limit_time = time(); //30日前
        $res = CronLogModel::where('start_time', '<=', $limit_time)->delete();
        Log::info('[Cron/DeleteCronLogScript]'.'删除计划任务日志记录数:'.$res);

        $res = CronSchedulingLogModel::where('start_time', '<=', $limit_time)->delete();
        Log::info('[Cron/DeleteCronLogScript]'.'删除调度运行日志记录数:'.$res);
    }
}