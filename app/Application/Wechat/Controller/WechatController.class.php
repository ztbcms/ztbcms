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

    /**
    * 配置设置页面
    */
    public function setting(){
        if(IS_POST){
            $post=I('post.');
            foreach($post as $key=>$value){
                $is_exsit=D('Config')->where("varname='%s'",$key)->find();
                if($is_exsit){
                    $data=array('varname'=>$key,'value'=>$value);
                    D('Config')->where("id='%d'",$is_exsit['id'])->save($data);
                }else{
                    $data=array('varname'=>$key,'value'=>$value);
                    D('Config')->add($data);
                }
            }
            $this->success('设置成功');
        }else{
            $memeber_models=D('Model')->where('type=2')->select();
            $this->assign('config',cache('Config'));
            $this->assign('memeber_models',$memeber_models);
            $this->display('setting');
        }
    }
}