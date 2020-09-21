<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use Admin\Service\User;
use app\admin\service\AdminConfigService;
use app\BaseController;
use think\facade\Db;
use think\facade\View;

class Login extends BaseController
{
    /**
     * 登录页
     * @return string
     */
    function index(){
        //如果已经登录
//        if (User::getInstance()->id) {
//            $this->redirect('Admin/Index/index');
//        }
//        //安全码校验
//        $code = I('code');
//        $ADMIN_PANEL_SECURITY_CODE = C('ADMIN_PANEL_SECURITY_CODE');
//        if(!empty($ADMIN_PANEL_SECURITY_CODE) && $code != $ADMIN_PANEL_SECURITY_CODE){
//            $this->display(C('TMPL_ACTION_PAGE_NOT_FOUND'));
//            return;
//        }

        $config = AdminConfigService::getInstance()->getConfig()['data'];

        return View::fetch('index', [
            'Config' => $config
        ]);
    }

    /**
     * 登录操作
     */
    function doLogin(){
        //记录登录失败者IP
        $ip = request()->ip();
        $form = I('post.form');
        $form_data = $this->decryptJsonString($form);
        $username = trim($form_data['username']);
        $password = trim($form_data['password']);
        $code = trim($form_data['code']);
        if (empty($username) || empty($password)) {
            $this->error("用户名或者密码不能为空，请重新输入！", U("Public/login"));
        }
        if (empty($code)) {
            $this->error("请输入验证码！", U("Public/login"));
        }

        //登录失败次数检测
        $login_max_faild = C('LOGIN_MAX_FAILD');
        $ban_login_time = C('BAN_LOGIN_TIME');
        $start_time = time() - $ban_login_time*60;
        $count = M('Loginlog')->where([
            'status' => 0, //登录失败
            'loginip' => $ip, //当前IP
            'logintime' => ['BETWEEN', [$start_time, time()]] //登录时间
        ])->count();
        if($count >= $login_max_faild){
            $this->error("登录失败次数过多，请".$ban_login_time."分钟后再试！", U("Public/login"));
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

            $this->success('登录成功', U('Admin/Index/index'));
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

}