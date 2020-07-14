<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/14
 * Time: 11:34
 */

namespace AdminMessage\Controller;

use AdminMessage\Model\AdminMessageModel;
use AdminMessage\Service\AdminMessageService;
use Common\Controller\AdminBase;

/**
 * 后台信息管理
 * Class AdminMessageController
 * @package AdminMessage\Controller
 */
class IndexController extends AdminBase
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
    public function createAdminMessage(){
        $title = '标题';
        $content = '内容';
        $target = '来源';
        $target_type = '来源类型';
        $sender = '发送者';
        $sender_type =  '发送者类型';
        $receiver = 0 ;
        $receiver_type = '接收者类型' ;
        $res = AdminMessageService::createAdminMessage($title,$content,$target,$target_type,$sender,$sender_type,$receiver,$receiver_type);

        $this->ajaxReturn($res);
    }

    /**
     * 获取消息
     */
    public function getAdminMsgList(){
        $page = I('get.page', 1);
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
        $list = AdminMessageService::getAdminMessageList($where,$order,$page,$limit);
        $this->ajaxReturn($list);
    }

    /**
     * 阅读消息
     */
    public function readMsg(){
        $id = I('post.id');
        $read_status = AdminMessageModel::READED;
        // 更新状态为 已读
        $res = AdminMessageService::readAdminMessage($id, $this->uid, $read_status);
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
        $type  = I('type','');
        if($type){
            $where['type'] = $type;
        }
        $res = AdminMessageService::readAdminAllMessage( $where, $read_status);
        $this->ajaxReturn($res);
    }

    /**
     * 更新当前页面
     */
    public function readMsgPage(){
        $page = I('post.page', 1);
        $limit = I('post.limit', 20);
        // 默认接收者
        $where['receiver'] = ['EQ', $this->uid];
        // 是否已读 0 全部 1 已读
        $read_status = I('read_status','');
        if($read_status != '' ){
            $where['read_status'] = ['EQ',$read_status];
        }
        // 消息类型
        $type = I('get.type','');
        if($type){
            $where['type'] = $type;
        }
        $list = AdminMessageService::getAdminMessageList($where,'',$page,$limit);

        // 查询出ids
        $read_status = AdminMessageModel::READED;
        $ids = [];
        if(!empty($list['data']['items'])){
            foreach ($list['data']['items'] as $item){
                $ids[] = $item['id'];
            }
        }
        // 已读本页
        $res = AdminMessageService::readAdminMessageByIds($ids,$this->uid,$read_status);
        $this->ajaxReturn($res);
    }

}