<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:29.
 */

namespace app\common\message\units;


use app\common\libs\message\MessageUnit;
use app\common\message\senders\SimpleSmsSender;
use app\common\message\senders\SimpleWxSender;

class SimpleMessage extends MessageUnit
{
    public function __construct($receiver, $target, $title, $content = '')
    {
        $this->setReceiver($receiver);
        $this->setReceiverType("user");
        $this->setTarget($target);
        $this->setTargetType("order_no");
        $this->setTitle($title);
        $this->setContent($content);
    }

    static function getSenders()
    {
        // 消息发送器
        return [
            new SimpleSmsSender(),
            new SimpleWxSender()
        ];
    }
}