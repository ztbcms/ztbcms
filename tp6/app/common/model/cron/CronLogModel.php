<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-03
 * Time: 16:22.
 */

namespace app\common\model\cron;


use think\Model;

class CronLogModel extends Model
{

    protected $name = 'tp6_cron_log';
    /**
     * 运行结果：待执行
     */
    const RESULT_WAITING = 0;
    /**
     * 运行结果：成功
     */
    const RESULT_SUCCESS = 1;
    /**
     * 运行结果：失败
     */
    const RESULT_FAIL = 2;
    /**
     * 运行结果：处理中
     */
    const RESULT_PROCESSING = 3;
}