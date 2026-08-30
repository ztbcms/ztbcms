<?php

namespace app\common\cronscript;

/**
 * 计划任务基类
 *
 * @package app\common\cronscript
 */
abstract class CronScript
{
    /**
     * 执行任务回调
     *
     * @param  string  $cronId
     *
     * @return array 返回格式请参考 slef::createReturn
     */
    abstract public function run($cronId);

    /**
     * 统一返回的结构
     *
     * @param $status
     * @param  array  $data
     * @param  string  $msg
     * @param  null  $code
     * @param  string  $url
     *
     * @return array
     */
    static function createReturn($status, $data = [], $msg = '', $code = null)
    {
        return createReturn($status, $data, $msg, $code);
    }
}