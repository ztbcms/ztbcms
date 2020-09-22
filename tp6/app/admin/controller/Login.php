<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\BaseController;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Login extends BaseController
{
    /**
     * 登录页
     * TODO xxx
     *
     * @return string
     */
    function index()
    {
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
    function doLogin()
    {
        //记录登录失败者IP
        $ip = request()->ip();
        $form = Request::param('form');
        $username = trim($form['username']);
        $password = trim($form['password']);
        $code = trim($form['code']);
        if (empty($username) || empty($password)) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }
        if (empty($code)) {
            return self::makeJsonReturn(false, null, '请输入验证码');
        }

        //登录失败次数检测
//        $login_max_faild = C('LOGIN_MAX_FAILD');
        $login_max_faild = Config::get('admin.login_max_faild', 10);
//        $ban_login_time = C('BAN_LOGIN_TIME');
        $ban_login_time = Config::get('admin.ban_login_time', 30);

        $start_time = time() - $ban_login_time * 60;

//        $count = M('Loginlog')->where([
//            'status' => 0, //登录失败
//            'loginip' => $ip, //当前IP
//            'logintime' => ['BETWEEN', [$start_time, time()]] //登录时间
//        ])->count();
        $count = Db::name('loginlog')->where([
            ['status', '=', 0], //登录失败
            ['loginip', '=', $ip], //当前IP
            ['logintime', 'BETWEEN', [$start_time, time()]] //登录时间
        ])->count();
        if ($count >= $login_max_faild) {
//            $this->error("登录失败次数过多，请".$ban_login_time."分钟后再试！", U("Public/login"));
            return self::makeJsonReturn(false, null, "登录失败次数过多，请".$ban_login_time."分钟后再试！");
        }

        //验证码开始验证
        if (!$this->_vertify($code)) {
//            $this->error("验证码错误，请重新输入！", U("Public/login"));
            return self::makeJsonReturn(false, null, "验证码错误，请重新输入！");
        }
        if (AdminUserService::getInstance()->login($username, $password)) {
            $forward = cookie("forward");
            if (!$forward) {
                $forward = build_url("/Admin/Index/index");
            } else {
                cookie("forward", null);
            }
            //增加登录成功行为调用
            $admin_public_tologin = array(
                'username' => $username,
                'ip'       => $ip,
            );
//            tag('admin_public_tologin', $admin_public_tologin);

//            $this->success('登录成功', U('Admin/Index/index'));
            return self::makeJsonReturn(true, null, "登录成功");
        } else {
            //增加登录失败行为调用
            $admin_public_tologin = array(
                'username' => $username,
                'password' => $password,
                'ip'       => $ip,
            );
//            tag('admin_public_tologin_error', $admin_public_tologin);
//            $this->error("用户名或者密码错误，登录失败！", U("Public/login"));
            return self::makeJsonReturn(true, null, "用户名或者密码错误，登录失败！");
        }
    }

    /**
     * 验证码校验
     * @param $code
     * @param  string  $type
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function _vertify($code, $type = "verify"){
        $checkcode_type = (int) AdminConfigService::getInstance()->getConfig('checkcode_type');
        $checkcode = new \app\common\libs\checkcode\Checkcode($checkcode_type);
        $checkcode->type = $type;
        return $checkcode->validate($code, false);
    }

}