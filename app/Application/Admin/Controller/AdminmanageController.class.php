<?php

// +----------------------------------------------------------------------
// |  我的面板
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;
use Admin\Service\User;

class AdminmanageController extends AdminBase {

    //修改当前登录状态下的用户个人信息
    public function myinfo() {
        if (IS_POST) {
            $data = array(
                'id' => User::getInstance()->id,
                'nickname' => I('nickname'),
                'email' => I('email'),
                'remark' => I('remark')
            );
            if (D("Admin/User")->create($data)) {
                if (D("Admin/User")->where(array('id' => User::getInstance()->id))->save() !== false) {
                    $this->success("资料修改成功！", U("Adminmanage/myinfo"));
                } else {
                    $this->error("更新失败！");
                }
            } else {
                $this->error(D("Admin/User")->getError());
            }
        } else {
            $this->assign("data", User::getInstance()->getInfo());
            $this->display();
        }
    }

    //后台登录状态下修改当前登录人密码
    public function chanpass() {
        if (IS_POST) {
            $oldPass = I('post.password', '', 'trim');
            if (empty($oldPass)) {
                $this->error("请输入旧密码！");
            }
            $newPass = I('post.new_password', '', 'trim');
            $new_pwdconfirm = I('post.new_pwdconfirm', '', 'trim');
            if ($newPass != $new_pwdconfirm) {
                $this->error("两次密码不相同！");
            }
            if (D("Admin/User")->changePassword(User::getInstance()->id, $newPass, $oldPass)) {
                //退出登录
                User::getInstance()->logout();
                $this->success("密码已经更新，请从新登录！", U("Admin/Public/login"));
            } else {
                $error = D("Admin/User")->getError();
                $this->error($error ? $error : "密码更新失败！");
            }
        } else {
            $this->assign('userInfo', User::getInstance()->getInfo());
            $this->display();
        }
    }

    //验证密码是否正确
    public function public_verifypass() {
        $password = I("get.password");
        if (empty($password)) {
            $this->error("密码不能为空！");
        }
        //验证密码
        $user = D('Admin/User')->getUserInfo((int) User::getInstance()->id, $password);
        if (!empty($user)) {
            $this->success("密码正确！");
        } else {
            $this->error("密码错误！");
        }
    }

}
