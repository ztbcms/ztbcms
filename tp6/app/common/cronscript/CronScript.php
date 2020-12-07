<?php

namespace app\common\cronscript;

use app\BaseController;

abstract class CronScript
{
    /**
     * 执行任务回调
     *
     * @param string $cronId
     */
    abstract public function run($cronId);

    /**
     * 统一返回的结构
     * @param $status
     * @param array $data
     * @param string $msg
     * @param null $code
     * @param string $url
     * @return array
     */
    static function createReturn($status, $data = [], $msg = '', $code = null, $url = ''){
        return BaseController::createReturn($status,$data,$msg,$code,$url);
    }
}