<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:03.
 */

namespace app\common\controller\message;


use app\common\controller\AdminController;
use app\common\message\units\SimpleMessage;
use app\common\model\message\MessageModel;
use app\common\model\message\MessageSendLogModel;
use app\Request;
use think\facade\View;

class Message extends AdminController
{
    /**
     * 在此执行发送日志记录
     * @param Request $request
     * @return array
     */
    public function handleAgainLog(Request $request)
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
     * @param Request $request
     * @throws \think\db\exception\DbException
     * @return array|string
     */
    public function sendLog(Request $request)
    {
        if ($request->isAjax()) {
            $where = [];
            $messageId = $request->get('message_id', '');
            if ($messageId) {
                $where[] = ['message_id', '=', $messageId];
            }
            $lists = MessageSendLogModel::where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        }

        return View::fetch('sendLog');
    }

    /**
     *  消息列表
     * @return string
     */
    public function index()
    {
        return View::fetch('index');
    }

    /**
     *  强制处理消息
     * @param Request $request
     * @return array
     */
    public function handMessage(Request $request)
    {
        $messageId = $request->post('message_id', '');
        $message = MessageModel::where('id', $messageId)->findOrEmpty();
        if (!$message->isEmpty()) {
            return self::createReturn(true, $message->handMessage(true), 'ok');
        } else {
            return self::createReturn(false, [], '找不到该消息记录');
        }
    }

    /**
     * 获取消息列表
     * @param Request $request
     * @throws \think\db\exception\DbException
     * @return array
     */
    public function getMessageList(Request $request)
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
        return self::createReturn(true, $lists, 'ok');
    }

    /**
     * 创建测试的消息记录
     * @return string
     */
    public function createMessageTest()
    {
        $message = new SimpleMessage(12, 20012222, '新订单', '你有新的订单，请及时处理');
        $message->createMessage();
        return "ok";
    }
}