<?php

namespace app\common\message\senders;

use app\common\libs\message\SenderUnit;
use app\common\model\message\MessageModel;

class SimpleSmsSender extends SenderUnit
{
    /**
     * @param MessageModel $message
     * @return bool
     */
    function doSend(MessageModel $message): bool
    {
        return true;
    }
}