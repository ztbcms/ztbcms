<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// +----------------------------------------------------------------------

namespace Wechat\Controller;
use Common\Controller\AdminBase;

class WechatController extends AdminBase {
    public function index(){
        if (IS_POST) {
			$this->redirect('index', $_POST);
		}
        $where=array();
        $search = I('get.search');
        if (!empty($search)) {
            if(I('get.nickname')!==''){
                $where['nickname']=array("like","%".I('get.nickname')."%");
				$this->assign('nickname', I('get.nickname'));
            }
             if(I('get.openid')!==''){
                $where['openid']=I('get.openid');
				$this->assign('openid', I('get.openid'));
            }
        }
        $count = M("Wechat")->where($where)->count();
	 	$page = $this->page($count, 20);
        $wx_users=M("Wechat")->limit($page->firstRow . ',' . $page->listRows)->where($where)->select();
        $this->assign('wx_users',$wx_users)->assign('Page', $page->show());
        $this->display('index');
    }
}