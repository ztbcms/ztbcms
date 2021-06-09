<?php

namespace app\common\model\message;

use think\Model;

class MessageSendLogModel extends Model
{
    protected $name = 'tp6_message_send_log';

    const STATUS_SUCCESSS = 1;
    const STATUS_FAIL = 0;

    /**
     * 重新调用该发送器
     */
    function redoMessageSender(): array
    {
        $messageId = $this->message_id;
        $message = MessageModel::where('id', $messageId)->findOrEmpty();
        if (!$message->isEmpty()) {
            try {
                $sender = new $this->sender();
                $res = $sender->doSend($message);
                if ($res) {
                    return createReturn(true, null, '执行完成');
                } else {
                    return createReturn(true, null, '执行异常');
                }
            } catch (\Exception $exception) {
                return createReturn(false, null, '执行异常：'.$exception->getMessage());
            }
        }

        return createReturn(true, null, '找不到消息');
    }
}