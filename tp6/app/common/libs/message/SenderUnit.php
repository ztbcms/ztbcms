<?php

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

    /**
     * 发送功能实现
     *
     * @param  MessageModel  $message
     *
     * @return bool 发送结果，true，正常，false异常，会截断下一个发送器
     */
    abstract function doSend(MessageModel $message): bool;
}