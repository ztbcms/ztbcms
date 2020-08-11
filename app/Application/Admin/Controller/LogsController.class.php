<?php

// +----------------------------------------------------------------------
// |  网站后台日志管理
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;

class LogsController extends AdminBase {

    //后台登录日志
    public function loginlog() {
        if (IS_POST) {
            $where = array();
            $username = I('username');
            $start_time = I('start_time');
            $end_time = I('end_time');
            $loginip = I('loginip');
            $status = I('status');
            if (!empty($username)) {
                $where['username'] = array('like', '%' . $username . '%');
            }
            if (!empty($start_time) && !empty($end_time)) {
                $start_time = strtotime($start_time);
                $end_time = strtotime($end_time) + 86399;
                $where['logintime'] = array(array('GT', $start_time), array('LT', $end_time), 'AND');
            }
            $_limit = 20;
            if (!empty($loginip)) {
                $where['loginip '] = array('like', "%{$loginip}%");
            }
            if ($status != '') {
                $where['status'] = $status;
            }
            $model = D("Admin/Loginlog");
            $count = $model->where($where)->count();
            $page = $this->page($count, $_limit, I('get.page', 1));
            $data = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => 'DESC'))->select();
            $res = self::createReturnList(true,$data, $page, $_limit, $count,ceil($count / $_limit));
            $this->ajaxReturn($res);
        } else {
            $where = array();
            $username = I('username');
            $start_time = I('start_time');
            $end_time = I('end_time');
            $loginip = I('loginip');
            $status = I('status');
            if (!empty($username)) {
                $where['username'] = array('like', '%' . $username . '%');
            }
            if (!empty($start_time) && !empty($end_time)) {
                $start_time = strtotime($start_time);
                $end_time = strtotime($end_time) + 86399;
                $where['logintime'] = array(array('GT', $start_time), array('LT', $end_time), 'AND');
            }
            if (!empty($loginip)) {
                $where['loginip '] = array('like', "%{$loginip}%");
            }
            if ($status != '') {
                $where['status'] = $status;
            }
            $model = D("Admin/Loginlog");
            $count = $model->where($where)->count();
            $page = $this->page($count, 20, I('get.page', 1));
            $data = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => 'DESC'))->select();
            $this->assign("Page", $page->show())
                ->assign("data", $data)
                ->assign('where', $where)
                ->display();
        }
    }

    //删除一个月前的登录日志
    public function deleteloginlog() {
        if (D("Admin/Loginlog")->deleteAMonthago()) {
            $this->success("删除登录日志成功！");
        } else {
            $this->error("删除登录日志失败！");
        }
    }

    //操作日志查看
    public function index() {
        $this->display();
    }

    //删除一个月前的操作日志
    public function deletelog() {
        if (D("Admin/Operationlog")->deleteAMonthago()) {
            $this->ajaxReturn(self::createReturn(true, null,'操作成功'));
        } else {
            $this->ajaxReturn(self::createReturn(false, null,'操作失败'));
        }
    }

}
