<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:35.
 */

namespace app\common\libs\message;


use app\common\model\message\MessageModel;

abstract class SenderUnit
{
    protected $error;

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }

    abstract function doSend(MessageModel $message): bool;
}