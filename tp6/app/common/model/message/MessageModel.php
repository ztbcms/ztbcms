<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:19.
 */

namespace app\common\model\message;

use app\common\libs\message\SenderUnit;
use app\common\service\BaseService;
use think\facade\Log;
use think\Model;

class MessageModel extends Model
{
    protected $name = 'tp6_message_msg';


    //=== 消息类型
    /**
     * 消息类型：私信
     */
    const TYPE_MESSAGE = 'message';
    /**
     * 消息类型：提醒
     */
    const TYPE_REMIND = 'remind';
    /**
     * 消息类型：公告
     */
    const TYPE_ANNOUNCE = 'announce';
    /**
     * 消息类型：
     */
    const TYPE_NOTICE = 'notice';

    //=== 处理状态
    /**
     * 处理状态：未处理
     */
    const PROCESS_STATUS_UNPROCESS = 0;
    /**
     * 处理状态：已处理
     */
    const PROCESS_STATUS_PROCESSED = 1;
    /**
     * 处理状态：处理中
     */
    const PROCESS_STATUS_PROCESSING = 2;

    //=== 阅读状态
    /**
     * 阅读状态：未阅读
     */
    const READ_STATUS_UNREAD = 0;
    /**
     * 阅读状态：已阅读
     */
    const READ_STATUS_READ = 1;

    // === 最大处理次数
    const MAX_PROCESS_NUMBER = 3;

    /**
     * 发送消息
     * @param SenderUnit $sender 消息发送器
     * @param bool $force 是否强制执行
     * @param MessageSendLogModel|null $sendLog 发送日志、如果有传代表更新日志
     * @return bool
     */
    function sendMessage(SenderUnit $sender, $force = false, MessageSendLogModel $sendLog = null): bool
    {
        $messageId = $this->id;
        $sendClass = get_class($sender);
        if (!$force) {
            $isProcess = MessageSendLogModel::where('message_id', $messageId)
                ->where('sender', $sendClass)
                ->where('status', MessageSendLogModel::STATUS_SUCCESSS)->count();
            if ($isProcess > 0) {
                return true;
            }
        }
        $sendLog = $sendLog ? $sendLog : new MessageSendLogModel();
        $sendLog->message_id = $messageId;
        $sendLog->sender = $sendClass;
        try {
            $res = $sender->doSend($this);
            if ($res) {
                $sendLog->status = MessageSendLogModel::STATUS_SUCCESSS;
                $sendLog->result_msg = "SUCCESS";
            } else {
                $sendLog->status = MessageSendLogModel::STATUS_FAIL;
                $sendLog->result_msg = $sender->getError();
            }
        } catch (\Exception $exception) {
            $res = false;
            $sendLog->status = MessageSendLogModel::STATUS_FAIL;
            $sendLog->result_msg = $exception->getMessage();
        }
        $sendLog->save();
        return $res;
    }

    /**
     * 消息处理
     * @param bool $force 是否强制执行，强制执行忽略消息是否已经执行
     */
    function handMessage($force = false)
    {
        $processNum = $this->process_num + 1;
        $processStatus = self::PROCESS_STATUS_PROCESSED;
        $sendTime = time();
        if (class_exists($this->class)) {
            $msg = new $this->class;
            if (!empty($msg->getSenders())) {
                $senders = $msg->getSenders();
                foreach ($senders as $sender) {
                    $res = $this->sendMessage($sender, $force);
                    if (!$res) {
                        $sendTime = 0;
                        $processStatus = self::PROCESS_STATUS_UNPROCESS;
                    }
                }
            }
        } else {
            // 消息体不存在
            $processStatus = self::PROCESS_STATUS_UNPROCESS;
            $err_mmg = 'Class '.$this->class. ' 不存在';
            Log::error($err_mmg);
            return createReturn(false, null, $err_mmg);
        }
        self::where('id', $this->id)->update([
            'process_num'    => $processNum,
            'process_status' => $processStatus,
            'send_time'      => $sendTime
        ]);

        return createReturn(true, null, '执行完成');
    }

    /**
     * 新建消息记录
     * @param $messageData
     * @return array
     */
    public function createMessage($messageData)
    {
        $data = [
            'title'         => $messageData['title'],
            'content'       => $messageData['content'],
            'target'        => $messageData['target'],
            'target_type'   => $messageData['target_type'],
            'sender'        => $messageData['sender'],
            'sender_type'   => $messageData['sender_type'],
            'receiver'      => $messageData['receiver'],
            'receiver_type' => $messageData['receiver_type'],
            'type'          => $messageData['type'],
            'class'         => $messageData['class'],
            'create_time'   => time()
        ];
        $res = self::insert($data);
        $BaseService = new BaseService();
        if (!$res) {
            return $BaseService::createReturn(false, null, '操作失败');
        }
        return $BaseService::createReturn(true, null, '操作成功');
    }
}