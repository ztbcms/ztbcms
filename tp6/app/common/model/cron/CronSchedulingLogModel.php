<?php

namespace app\common\model\cron;

use think\Model;

class CronSchedulingLogModel extends Model
{
    protected $name = 'tp6_cron_scheduling_log';
    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp'
    ];
}