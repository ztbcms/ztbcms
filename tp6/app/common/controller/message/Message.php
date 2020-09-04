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
use app\Request;
use think\facade\View;

class Message extends AdminController
{

    public function index()
    {
        return View::fetch('index');
    }

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

    public function createMessageTest()
    {
        $message = new SimpleMessage(12, 20012222, '新订单', '你有新的订单，请及时处理');
        $message->createMessage();
        return "ok";
    }
}