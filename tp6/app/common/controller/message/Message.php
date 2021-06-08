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
        $_action = input('_action');
        if($_action == 'getSendLogList'){
            $where = [];
            $messageId = $request->get('message_id', '');
            if ($messageId) {
                $where[] = ['message_id', '=', $messageId];
            }
            $lists = MessageSendLogModel::where($where)->order('id', 'DESC')->paginate(20);
            return self::createReturn(true, $lists, 'ok');
        } else if($_action == 'handleAgainLog'){
            return $this->handleAgainLog($request);
        }
        return View::fetch('sendLog');
    }

    /**
     * 消息列表
     * @param Request $request
     * @return array|string
     */
    public function index(Request $request)
    {
        $_action = input('_action');
        if($_action == 'getMessageList'){
            return $this->getMessageList($request);
        } else if($_action == 'handMessage') {
            return $this->handMessage($request);
        }
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

    /**
     * 新建消息记录
     * @param  Request  $request
     * @return string|\think\response\Json
     */
    public function addMessage(Request $request)
    {
        $_action = input('_action');
        if ($_action == 'addMessage') {
            $data['title'] = $request->post('title', '');
            $data['content'] = $request->post('content', '');
            $data['target'] = $request->post('target', '');
            $data['target_type'] = $request->post('target_type', '');
            $data['sender'] = $request->post('sender', '');
            $data['sender_type'] = $request->post('sender_type', '');
            $data['receiver'] = $request->post('receiver', '');
            $data['receiver_type'] = $request->post('receiver_type', '');
            $data['type'] = $request->post('type', '');
            $data['class'] = $request->post('newClass', '');

            if (empty($data['title'])) {
                return json(self::createReturn(false, null, '请输入消息标题'));
            }
            if (empty($data['content'])) {
                return json(self::createReturn(false, null, '请输入消息内容'));
            }
            if (empty($data['target'])) {
                return json(self::createReturn(false, null, '请输入消息源'));
            }
            if (empty($data['target_type'])) {
                return json(self::createReturn(false, null, '请输入消息源类型'));
            }
            if (empty($data['receiver'])) {
                return json(self::createReturn(false, null, '请输入接收者'));
            }
            if (empty($data['receiver_type'])) {
                return json(self::createReturn(false, null, '请输入接收者类型'));
            }
            if (empty($data['type'])) {
                return json(self::createReturn(false, null, '请输入消息类型'));
            }
            if (function_exists($data['class'])) {
                return json(self::createReturn(false, null, '请输入正确的实例化的类名'));
            }

            $MessageModel = new MessageModel();
            $res = $MessageModel->createMessage($data);
            return json($res);
        }
        return view::fetch('addMessage');
    }
}