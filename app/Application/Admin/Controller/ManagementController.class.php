<?php

// +----------------------------------------------------------------------
// |  管理员配置管理
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;
use Admin\Service\User;

class ManagementController extends AdminBase
{

    //管理员列表
    public function manager() {
        $where = array();
        $role_id = I('get.role_id', 0, 'intval');
        if ($role_id) {
            $where['role_id'] = $role_id;
            $menuReturn = array(
                'url' => U('Rbac/rolemanage'),
                'name' => '返回角色管理',
            );
            $this->assign('menuReturn', $menuReturn);
        } else {
            if (!User::getInstance()->isAdministrator()) {
                //如果非超级管理员，只能管理下级角色的成员
                $res = D('Admin/Role')->field('id')->where(['parentid' => User::getInstance()->role_id])->select();
                $role_ids = [];
                foreach ($res as $val) {
                    $role_ids[] = $val['id'];
                }
                //如果没有找到下级role_ids 则默认为0
                $where['role_id'] = ['in', $role_ids ? $role_ids : '0'];
            }
        }
        $count = D('Admin/User')->where($where)->count();
        $page = $this->page($count, 20, I('page', 1));
        $User = D('Admin/User')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => 'DESC'))->select();
        $this->assign("Userlist", $User);
        $this->assign("Page", $page->show());

//        $this->display('manager');  //旧版
        $this->display('adminlist');

    }
//      调试后删除
//    public function adminlistNew(){
//        $this->display('adminlist');
//    }
    /**
     *  获取管理员列表 API
     *  @method get
     */
    public function getManager(){
        $where = array();
        $role_id = I('get.role_id', 0, 'intval');
        if ($role_id) {
            $where['role_id'] = $role_id;
            $menuReturn = array(
                'url' => U('Rbac/rolemanage'),
                'name' => '返回角色管理',
            );
            $this->assign('menuReturn', $menuReturn);
        } else {
            if (!User::getInstance()->isAdministrator()) {
                //如果非超级管理员，只能管理下级角色的成员
                $res = D('Admin/Role')->field('id')->where(['parentid' => User::getInstance()->role_id])->select();
                $role_ids = [];
                foreach ($res as $val) {
                    $role_ids[] = $val['id'];
                }
                //如果没有找到下级role_ids 则默认为0
                $where['role_id'] = ['in', $role_ids ? $role_ids : '0'];
            }
        }
        $count = D('Admin/User')->where($where)->count();
        $page = $this->page($count, 20, I('page', 1));
        $User = D('Admin/User')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => 'DESC'))->select();
        foreach ($User as $k => $User_value){
            if($User_value['last_login_time'])
                $User[$k]['last_login_time']= date("Y-m-d H:i:s",$User_value['last_login_time']);
            $User[$k]['role_name'] = D('Admin/Role')->where('id='. $User[$k]['role_id'])->find()['name'];
        }
        return $this->ajaxReturn(self::createReturn(true,$User));
    }


    /**
     *  获取管理员信息通过id
     *  @param $id
     *  @return Array
     */
    public function getManagerByid(){
        $where = [];
        if(IS_POST){
            $id = I('id');
            $where['id'] = $id;
            $res = D('Admin/User')->where($where)->find();
            $res['password'] = '';
            if($res) return $this->ajaxReturn(createReturn(true,$res));
        }
        return $this->ajaxReturn(createReturn(false));
    }

    //编辑信息
    public function edit() {
        $id = I('request.id', 0, 'intval');
        if (empty($id)) {
            $this->error("请选择需要编辑的信息！");
        }
        //判断是否修改本人，在此方法，不能修改本人相关信息
        if (User::getInstance()->id == $id) {
            $this->error("修改当前登录用户信息请进入[我的面板]中进行修改！");
        }
        if (1 == $id) {
            $this->error("该帐号不允许修改！");
        }
        if (IS_POST) {
            if (false !== D('Admin/User')->amendManager($_POST)) {
                $this->success("更新成功！", U("Management/manager"));
            } else {
                $error = D('Admin/User')->getError();
                $this->error($error ? $error : '修改失败！');
            }
        } else {
            $data = D('Admin/User')->where(array("id" => $id))->find();
            if (empty($data)) {
                $this->error('该信息不存在！');
            }
            $myid = User::getInstance()->isAdministrator() ? 0 : User::getInstance()->role_id;
            $this->assign("role", D('Admin/Role')->selectChildHtmlOption($myid, $data['role_id'], 'name="role_id"'));
            $this->assign("data", $data);
            $this->display();
        }
    }

    /**
     *  管理员编辑页面
     *  2020.3.6
     */
    public function adminedit(){
        $this->display();
    }

    //添加管理员
    public function adminadd() {
        if (IS_POST) {
            if (D('Admin/User')->createManager($_POST)) {
                $this->success("添加管理员成功！", U('Management/manager'));
            } else {
                $error = D('Admin/User')->getError();
                $this->error($error ? $error : '添加失败！');
            }
        } else {
            $myid = User::getInstance()->isAdministrator() ? 0 : User::getInstance()->role_id;
//            $this->assign("role", D('Admin/Role')->selectChildHtmlOption($myid, 0, 'name="role_id"'));
//                        $this->display();

            $this->display('adminaddNew');
        }
    }

    //管理员删除
    public function delete() {
        $id = I('get.id');
        if (empty($id)) {
            $this->error("没有指定删除对象！");
        }
        if ((int)$id == User::getInstance()->id) {
            $this->error("你不能删除你自己！");
        }
        //执行删除
        if (D('Admin/User')->deleteUser($id)) {
            $this->success("删除成功！");
        } else {
            $this->error(D('Admin/User')->getError() ?: '删除失败！');
        }
    }

}
