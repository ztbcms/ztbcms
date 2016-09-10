<?php

// +----------------------------------------------------------------------
// |  后台模块公共方法
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;
use Admin\Service\User;

class PublicController extends AdminBase {

    //后台登录界面
    public function login() {
        //如果已经登录
        if (User::getInstance()->id) {
            $this->redirect('Admin/Index/index');
        }
        $this->display();
    }

    //后台登录验证
    public function tologin() {
        //记录登录失败者IP
        $ip = get_client_ip();
        $username = I("post.username", "", "trim");
        $password = I("post.password", "", "trim");
        $code = I("post.code", "", "trim");
        if (empty($username) || empty($password)) {
            $this->error("用户名或者密码不能为空，请重新输入！", U("Public/login"));
        }
        if (empty($code)) {
            $this->error("请输入验证码！", U("Public/login"));
        }
        //验证码开始验证
        if (!$this->verify($code)) {
            $this->error("验证码错误，请重新输入！", U("Public/login"));
        }
        if (User::getInstance()->login($username, $password)) {
            $forward = cookie("forward");
            if (!$forward) {
                $forward = U("Admin/Index/index");
            } else {
                cookie("forward", NULL);
            }
            //增加登录成功行为调用
            $admin_public_tologin = array(
                'username' => $username,
                'ip' => $ip,
            );
            tag('admin_public_tologin', $admin_public_tologin);
            $this->redirect('Index/index');
        } else {
            //增加登录失败行为调用
            $admin_public_tologin = array(
                'username' => $username,
                'password' => $password,
                'ip' => $ip,
            );
            tag('admin_public_tologin_error', $admin_public_tologin);
            $this->error("用户名或者密码错误，登录失败！", U("Public/login"));
        }
    }

    //退出登录
    public function logout() {
        if (User::getInstance()->logout()) {
            //手动登出时，清空forward
            cookie("forward", NULL);
            $this->success('注销成功！', U("Admin/Public/login"));
        }
    }

    //常用菜单设置
    public function changyong() {
        if (IS_POST) {
            //被选中的菜单项
            $menuidAll = explode(',', I('post.menuid', ''));
            if (is_array($menuidAll) && count($menuidAll) > 0) {
                //取得菜单数据
                $menu_info = cache('Menu');
                $addPanel = array();
                //检测数据合法性
                foreach ($menuidAll as $menuid) {
                    if (empty($menu_info[$menuid])) {
                        continue;
                    }
                    $info = array(
                        'mid' => $menuid,
                        'userid' => User::getInstance()->id,
                        'name' => $menu_info[$menuid]['name'],
                        'url' => "{$menu_info[$menuid]['app']}/{$menu_info[$menuid]['controller']}/{$menu_info[$menuid]['action']}",
                    );
                    if($menu_info[$menuid]['parameter']){
                        $info['url'] .= "?{$menu_info[$menuid]['parameter']}";
                    }
                    $addPanel[] = $info;
                }
                if (D('Admin/AdminPanel')->addPanel($addPanel)) {
                    $this->success("添加成功！", U("Public/changyong"));
                } else {
                    $error = D('Admin/AdminPanel')->getError();
                    $this->error($error ? $error : '添加失败！');
                }
            } else {
                D('Admin/AdminPanel')->where(array("userid" => \Admin\Service\User::getInstance()->id))->delete();
                $this->error("常用菜单清除成功！");
            }
        } else {
            //菜单缓存
            $result = cache("Menu");
            $json = array();
            //子角色列表
            $child = explode(',', D("Admin/Role")->getArrchildid(\Admin\Service\User::getInstance()->role_id));
            foreach ($result as $rs) {
                if ($rs['status'] == 0) {
                    continue;
                }
                //条件
                $where = array('app' => $rs['app'], 'controller' => $rs['controller'], 'action' => $rs['action'], 'role_id' => array('IN', $child));
                //是否有权限
                if (!D('Admin/Access')->isCompetence($where)) {
                    continue;
                }
                $data = array(
                    'id' => $rs['id'],
                    'nocheck' => $rs['type'] ? 0 : 1,
                    'checked' => $rs['id'],
                    'parentid' => $rs['parentid'],
                    'name' => $rs['name'],
                    'checked' => D("Admin/AdminPanel")->isExist($rs['id']) ? true : false,
                );
                $json[] = $data;
            }
            $this->assign('json', json_encode($json))
                    ->display();
        }
    }

}
