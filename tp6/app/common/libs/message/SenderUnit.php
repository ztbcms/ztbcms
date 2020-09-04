<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:35.
 */

namespace app\common\libs\message;


abstract class SenderUnit
{
    abstract function doSend();
}