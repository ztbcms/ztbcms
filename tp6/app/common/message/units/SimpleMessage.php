<?php

namespace app\common\message\units;


use app\common\libs\message\MessageUnit;
use app\common\message\senders\SimpleSmsSender;
use app\common\message\senders\SimpleWxSender;

class SimpleMessage extends MessageUnit
{
    function getSenders(): array
    {
        return [
            new SimpleSmsSender(),
            new SimpleWxSender(),
        ];
    }
}