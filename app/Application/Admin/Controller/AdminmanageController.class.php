<?php

// +----------------------------------------------------------------------
// |  我的面板
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;
use Admin\Service\User;

class AdminmanageController extends AdminBase {

    /**
     * 修改当前登录状态下的用户个人信息
     */
    public function myinfo() {
        if (IS_POST) {
            $data = array(
                'id' => User::getInstance()->id,
                'nickname' => I('nickname'),
                'email' => I('email'),
                'remark' => I('remark')
            );
            $db = D("Admin/User");
            if ($db->create($data)) {
                if (D("Admin/User")->where(array('id' => User::getInstance()->id))->save() !== false) {
                    $this->ajaxReturn(self::createReturn(true, null, '操作成功'));
                } else {
                    $this->ajaxReturn(self::createReturn(false, null, '操作失败'));
                }
            } else {
                $this->ajaxReturn(self::createReturn(false, null, $db->getError()));
            }
        } else {
            $user_info = User::getInstance()->getInfo();
            $data = [
                'id' => $user_info['id'],
                'username' => $user_info['username'],
                'nickname' => $user_info['nickname'],
                'email' => $user_info['email'],
                'remark' => $user_info['remark']
            ];
            $this->assign("data", $data);
            $this->display();
        }
    }

    //后台登录状态下修改当前登录人密码
    public function chanpass() {
        if (IS_POST) {
            $oldPass = I('post.password', '', 'trim');
            $newPass = I('post.new_password', '', 'trim');
            $new_pwdconfirm = I('post.new_pwdconfirm', '', 'trim');

            if (empty($oldPass)) {
                $this->ajaxReturn(self::createReturn(false, null,  '请输入旧密码'));
                return;
            }
            if ($newPass != $new_pwdconfirm) {
                $this->ajaxReturn(self::createReturn(false, null,  '两次密码不相同'));
                return;
            }
            if (D("Admin/User")->changePassword(User::getInstance()->id, $newPass, $oldPass)) {
                //退出登录
                User::getInstance()->logout();

                $this->ajaxReturn(self::createReturn(true, [
                    'rediret_url' => U("Admin/Public/login") //跳转链接
                ],  '密码已经更新，请从新登录'));
                return;
            } else {
                $this->ajaxReturn(self::createReturn(false, null,  '密码更新失败'));
                return;
            }
        } else {
            $this->assign('data', User::getInstance()->getInfo());
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
