<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-05
 * Time: 09:41.
 */

namespace app\common\model\message;

use think\Model;

class MessageSendLogModel extends Model
{
    protected $name = 'tp6_message_send_log';

    const STATUS_SUCCESSS = 1;
    const STATUS_FAIL = 0;

    /**
     * 基于发送日志调用消息处理
     * @return bool
     */
    function sendMessage(): bool
    {
        $messageId = $this->message_id;
        $message = MessageModel::where('id', $messageId)->findOrEmpty();
        if (!$message->isEmpty()) {
            try {
                $sender = new $this->sender();
                return $message->sendMessage($sender, true, $this);
            } catch (\Exception $exception) {
                return false;
            }
        } else {
            return false;
        }
    }
}