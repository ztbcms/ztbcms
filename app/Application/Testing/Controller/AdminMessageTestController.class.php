<?php
/**
 * User: FHYI
 * Date: 2020/7/17
 * Time: 10:32
 */

namespace Testing\Controller;

use Admin\Service\AdminMessageService;
use Common\Controller\AdminBase;

/**
 * 测试消息模块
 * Class AdminMessageTestController
 * @package Test\Controller|
 */
class AdminMessageTestController extends AdminBase
{
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
}