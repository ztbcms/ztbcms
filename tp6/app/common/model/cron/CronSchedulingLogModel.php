<?php

namespace app\common\model\cron;

use think\Model;

class CronSchedulingLogModel extends Model
{
    protected $name = 'cron_scheduling_log';
    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp'
    ];
}
