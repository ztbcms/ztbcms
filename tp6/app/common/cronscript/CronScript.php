<?php

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