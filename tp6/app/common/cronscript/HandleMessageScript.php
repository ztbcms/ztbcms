<?php

namespace app\common\cronscript;


use app\common\model\message\MessageModel;

/**
 * 消息处理
 *
 * @package app\common\cronscript
 */
class HandleMessageScript extends CronScript
{
    public function run($cronId)
    {
        //获取未处理且小于最大处理次数的消息列表
        $messages = MessageModel::where('process_status', MessageModel::PROCESS_STATUS_UNPROCESS)
            ->where('process_num', '<', MessageModel::MAX_PROCESS_NUMBER)
            ->select();
        foreach ($messages as $message) {
            $message->handMessage();
        }

        return self::createReturn(true, [
            'amount'    => count($messages)
        ], '操作完成');
    }
}