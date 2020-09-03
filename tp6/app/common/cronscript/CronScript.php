<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-03
 * Time: 21:47.
 */

namespace app\common\cronscript;

abstract class CronScript
{

    /**
     * 执行任务回调
     *
     * @param string $cronId
     */
    abstract public function run($cronId);
}