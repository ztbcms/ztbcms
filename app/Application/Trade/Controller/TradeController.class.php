<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// +----------------------------------------------------------------------

namespace Trade\Controller;
use Common\Controller\AdminBase;
class TradeController extends AdminBase {
    public function index() {
        if (IS_POST) {
			$this->redirect('index', $_POST);
		}
        $where=array();
        $search = I('get.search');
       if (!empty($search)) {
			$this->assign("search", $search);
			//添加开始时间
			$start_time = I('get.start_time');
			if (!empty($start_time)) {
				$start_time = strtotime($start_time);
				$where['create_time'] = array("EGT", $start_time);
				$this->assign('start_time', $start_time);
			}
			//添加结束时间
			$end_time = I('get.end_time');
			if (!empty($end_time)) {
				$end_time = strtotime($end_time);
				$where['create_time'] = array("ELT", $end_time);
				$this->assign('end_time', $end_time);
			}
			if ($end_time > 0 && $start_time > 0) {
				$where['create_time'] = array(array('EGT', $start_time), array('ELT', $end_time));
			}

            if(I('get.status')!==''){
                $where['status']=I('get.status');
				$this->assign('status', I('get.status'));
            }

			 if(I('get.type')!==''){
                $where['type']=I('get.type');
				$this->assign('type', I('get.type'));
            }

		}
		$count = M("Trade")->where($where)->count();
	 	$page = $this->page($count, 20);
		$trade_type=M('Trade')->field('type')->distinct('type')->select();
        $trade=M('Trade')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
        $this->assign('trades',$trade);
        $this->assign('trade_type',$trade_type)->assign('Page', $page->show());
        $this->display('index');
    }
}