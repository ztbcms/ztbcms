<?php

// +----------------------------------------------------------------------
// |  行为管理
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;

class BehaviorController extends AdminBase {

    //行为模型
    protected $behavior = NULL;

    protected function _initialize() {
        parent::_initialize();
        $this->behavior = D('Common/Behavior');
    }

    //行为列表
    public function index() {
        $where = array();
        //搜索行为标识
        $keyword = I('get.keyword');
        if (!empty($keyword)) {
            $where['name'] = array('like', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        //获取总数
        $count = $this->behavior->where($where)->count('id');
        $_page = I('get.page', 1);
        $page = $this->page($count, 20, $_page);
        $action = $this->behavior->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "desc"))->select();

        $this->assign("Page", $page->show());
        $this->assign('data', $action);
        $this->display();
    }

    //添加行为
    public function add() {
        if (IS_POST) {
            $post = I('post.', '', '');
            if ($this->behavior->addBehavior($post)) {
                $this->success('添加成功，需要更新缓存后生效！', U('Behavior/index'));
            } else {
                $this->error($this->behavior->getError());
            }
        } else {

            $this->display();
        }
    }

    //编辑行为
    public function edit() {
        if (IS_POST) {
            $post = I('post.', '', '');
            if ($this->behavior->editBehavior($post)) {
                $this->success('修改成功，需要更新缓存后生效！', U('Behavior/index'));
            } else {
                $this->error($this->behavior->getError());
            }
        } else {
            $id = I('get.id', 0, 'intval');
            if (empty($id)) {
                $this->error('请选择需要编辑的行为！');
            }
            //查询出行为信息
            $info = $this->behavior->getBehaviorById($id);
            if (empty($info)) {
                $error = $this->behavior->getError();
                $this->error($error ? $error : '该行为不存在！');
            }

            $this->assign('info', $info);
            $this->display();
        }
    }

    //删除行为
    public function delete() {
        $id = I('get.id', 0, 'intval');
        if (empty($id)) {
            $this->error('请指定需要删除的行为！');
        }
        //删除
        if ($this->behavior->delBehaviorById($id)) {
            $this->success('行为删除成功，需要更新缓存后生效！', U('Behavior/index'));
        } else {
            $error = $this->behavior->getError();
            $this->error($error ? $error : '删除失败！');
        }
    }

    //行为日志
    public function logs() {
        if(IS_POST){
            $this->redirect('loginlog',$_POST);
        }
        $wehre = array();
        $type = I('type', '', 'trim');
        $keyword = I('keyword', '', 'trim');
        if ($type) {
            if ($type == 'guid') {
                $wehre[$type] = array('LIKE', "%{$keyword}%");
            } else {
                $wehre[$type] = $keyword;
            }
            $this->assign('type', $type);
            $this->assign('keyword', $keyword);
        }
        $model = M('BehaviorLog');
        $count = $model->where($wehre)->count();
        $page = $this->page($count, 20,I('page',1));
        $data = $model->where($wehre)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => 'DESC'))->select();
        $this->assign('Page', $page->show())
                ->display();
    }

    //状态转换
    public function status() {
        $id = I('get.id', 0, 'intval');
        if (empty($id)) {
            $this->error('请指定需要状态转换的行为！');
        }
        //状态转换
        if ($this->behavior->statusBehaviorById($id)) {
            $this->success('行为状态转换成功，需要更新缓存后生效！', U('Behavior/index'));
        } else {
            $error = $this->behavior->getError();
            $this->error($error ? $error : '状态转换失败！');
        }
    }

}
