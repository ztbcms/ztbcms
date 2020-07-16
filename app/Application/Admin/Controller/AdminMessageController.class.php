<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/14
 * Time: 11:34
 */

namespace Admin\Controller;

use Admin\Model\AdminMessageModel;
use Admin\Service\AdminMessageService;
use Common\Controller\AdminBase;

/**
 * 后台消息管理
 * Class AdminMessageController
 * @package AdminMessage\Controller
 */
class AdminMessageController extends AdminBase
{
    /**
     * 所有通知
     */
    public function index(){
        $this->display();
    }

    /**
     * 未读通知
     */
    public function noRead(){
        $this->display();
    }

    /**
     * 系统通知
     */
    public function system(){
        $this->display();
    }

    /**
     * 创建消息
     */
    public function createMessage(){
        $title = '标题';
        $content = '内容';
        $receiver = $this->uid ;  // 接收者Id
        $res = AdminMessageService::createMessage($title, $content, $receiver);
        $this->ajaxReturn($res);
    }

    /**
     * 创建群发消息
     */
    public function createGroupMessage(){
        $title = '群发标题';
        $content = '群发内容';
        $res = AdminMessageService::createGroupMessage($title, $content);
        $this->ajaxReturn($res);
    }

    /**
     * 创建系统消息
     */
    public function createSystemMessage(){
        $title = '系统公告';
        $content = '这是系统通知的内容';
        $res = AdminMessageService::createSystemMessage($title, $content);
        $this->ajaxReturn($res);
    }

    /**
     * 获取消息列表
     */
    public function getAdminMsgList(){
        $page  = I('get.page', 1);
        $limit = I('get.limit', 20);
        // 默认接收者
        $where['receiver'] = ['EQ', $this->uid];
        // 阅读状态 0 未读 1 已读
        $read_status = I('read_status','');
        if($read_status != '' ){
            $where['read_status'] = ['EQ',$read_status];
        }
        // 消息类型
        $type = I('get.type','');
        if($type){
            $where['type'] = $type;
        }

        $order = 'read_status ASC,create_time DESC';
        $list = AdminMessageService::getAdminMessageList($where, $order, $page, $limit);
        $this->ajaxReturn($list);
    }

    /**
     * 阅读消息
     */
    public function readMsg(){
        // 更新状态为 已读
        $ids = I('post.ids');
        if(!is_array($ids)){
            $ids = [$ids];
        }
        $res = AdminMessageService::readAdminMessage($ids, $this->uid, AdminMessageModel::READED);
        $this->ajaxReturn($res);
    }

    /**
     * 阅读所有消息
     */
    public function readMsgAll(){
        $read_status = AdminMessageModel::READED;
        // 接收者
        $where['receiver'] = $this->uid;
        // 消息类型
        $type = I('type','');
        if($type){
            $where['type'] = $type;
        }
        $res = AdminMessageService::readAllAdminMessage($where, $read_status);
        $this->ajaxReturn($res);
    }

}