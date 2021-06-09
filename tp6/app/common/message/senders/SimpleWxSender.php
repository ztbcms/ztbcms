<?php

namespace app\common\message\senders;


use app\common\libs\message\SenderUnit;
use app\common\model\message\MessageModel;

class SimpleWxSender extends SenderUnit
{
    function doSend(MessageModel $message): bool
    {
        return true;
    }
}