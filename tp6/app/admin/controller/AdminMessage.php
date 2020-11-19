<?php
/**
 * User: jayinton
 * Date: 2020/9/19
 */

namespace app\admin\controller;

use app\admin\service\AdminMessageService;
use app\common\controller\AdminController;
use think\facade\Request;
use think\facade\View;

/**
 * 后台消息
 *
 * @package app\admin\controller
 */
class AdminMessage extends AdminController
{
    protected $noNeedPermission = ['getAdminMsgList', 'readMsg', 'readMsgAll'];

    /**
     * 所有通知
     */
    public function index()
    {
        return View::fetch('index');
    }

    /**
     * 未读通知
     */
    public function noRead()
    {
        return View::fetch('noRead');
    }

    /**
     * 系统通知
     */
    public function system()
    {
        return View::fetch('system');
    }


    /**
     * 获取消息列表
     */
    public function getAdminMsgList()
    {
        $page = Request::param('page', 1);
        $limit = Request::param('limit', 15);
        // 默认接收者
        $where = [
            ['receiver', '=', $this->user->id]
        ];
        // 阅读状态 0 未读 1 已读
        $read_status = Request::param('read_status', '');
        if ($read_status !== '') {
            $where[] = ['read_status', '=', $read_status];
        }
        // 消息类型
        $type = Request::param('type', '');
        if ($type) {
            $where[] = ['target_type', '=', $type];
        }

        $order = 'read_status ASC,create_time DESC';
        $res = AdminMessageService::getAdminMessageList($where, $order, $page, $limit);
        return json($res);
    }

    /**
     * 阅读消息
     */
    public function readMsg()
    {
        // 更新状态为 已读
        $ids = Request::param('ids', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        if (empty($ids)) {
            return self::makeJsonReturn(true, null, '操作完成');
        }

        $res = AdminMessageService::readAdminMessage($ids, $this->user->id);
        return json($res);
    }

    /**
     * 阅读所有消息
     */
    public function readMsgAll()
    {
        // 消息类型
        $type = Request::param('type', '');
        if ($type) {
            $where['type'] = $type;
        }
        $res = AdminMessageService::readAllAdminMessage($this->user->id, $type);
        return json($res);
    }

    /**
     * 创建后台消息
     */
    public function sendMessage(){
        $action = input('_action', '', 'trim');
        $post = input('post.');
        if($action == 'createMessage') {
            AdminMessageService::createMessage(
                $post['title'],$post['content'],
                $post['receiver'],$post['sender'],
                $post['sender_type'],$post['target'],
                $post['target_type'], $post['receiver_type']
            );
            return json(self::createReturn(true,'','发送成功'));
        }
        return View::fetch('sendMessage');
    }
}