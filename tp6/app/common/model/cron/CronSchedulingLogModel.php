<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 13:41.
 */

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