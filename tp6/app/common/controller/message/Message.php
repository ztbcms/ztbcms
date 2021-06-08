<?php

namespace app\common\controller\message;


use app\common\controller\AdminController;
use app\common\model\message\MessageModel;
use app\common\model\message\MessageSendLogModel;
use app\Request;
use think\facade\View;

// 消息组件
class Message extends AdminController
{
    /**
     * 在此执行发送日志记录
     *
     * @param  Request  $request
     *
     * @return array
     */
    private function handleAgainLog(Request $request)
    {
        $logId = $request->post('log_id');
        $messageLog = MessageSendLogModel::where('id', $logId)->findOrEmpty();
        if (!$messageLog->isEmpty()) {
            return self::createReturn(true, $messageLog->sendMessage(), '操作成功');
        }
        return self::createReturn(false, [], '找不到该记录');
    }

    /**
     * 发送日志
     *
     * @param  Request  $request
     *
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    function sendLog(Request $request)
    {
        $_action = input('_action');
        if ($_action == 'getSendLogList') {
            $where = [];
            $messageId = $request->get('message_id', '');
            if ($messageId) {
                $where[] = ['message_id', '=', $messageId];
            }
            $lists = MessageSendLogModel::where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists);
        } else {
            if ($_action == 'handleAgainLog') {
                return $this->handleAgainLog($request);
            }
        }
        return View::fetch('sendLog');
    }

    /**
     * 消息列表
     *
     * @param  Request  $request
     *
     * @return array|string
     */
    function index(Request $request)
    {
        $_action = input('_action');
        if ($_action == 'getMessageList') {
            return $this->getMessageList($request);
        } else {
            if ($_action == 'handMessage') {
                return $this->handMessage($request);
            }
        }
        return View::fetch('index');
    }

    /**
     * 获取消息列表
     *
     * @param  Request  $request
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function getMessageList(Request $request)
    {
        $datetime = $request->get('datetime', '');
        $searchMessage = $request->get('search_message', []);
        $where = [];

        if (is_array($searchMessage) && $searchMessage) {
            foreach ($searchMessage as $key => $value) {
                if ($value) {
                    $where[] = [$key, 'like', "%{$value}%"];
                }
            }
        }
        if ($datetime) {
            foreach ($datetime as &$time) {
                $time = strtotime($time);
            }
            $where[] = ['create_time', 'between', $datetime];
        }

        $lists = MessageModel::where($where)->order('id', 'DESC')->paginate(20);
        return self::createReturn(true, $lists);
    }

    /**
     *  强制处理消息
     *
     * @param  Request  $request
     *
     * @return array
     */
    private function handMessage(Request $request)
    {
        $messageId = $request->post('message_id', '');
        $message = MessageModel::where('id', $messageId)->findOrEmpty();
        if (!$message->isEmpty()) {
            return self::createReturn(true, $message->handMessage(true), 'ok');
        } else {
            return self::createReturn(false, [], '找不到该消息记录');
        }
    }

}