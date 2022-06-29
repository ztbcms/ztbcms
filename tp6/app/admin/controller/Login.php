<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use app\admin\model\LoginlogModel;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

/**
 * 登录
 *
 * @package app\admin\controller
 */
class Login extends AdminController
{
    public $noNeedLogin = ['index', 'doLogin', 'doLogout'];
    /**
     * 登录页
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index()
    {
        //如果已经登录
        if (AdminUserService::getInstance()->isLogin()) {
            return redirect(build_url('/admin/dashboard/index'));
        }

        //安全码校验
        $code = Request::param('code', ' ');
        $ADMIN_PANEL_SECURITY_CODE = Config::get('admin.admin_panel_security_code');
        if (!empty($ADMIN_PANEL_SECURITY_CODE) && $code != $ADMIN_PANEL_SECURITY_CODE) {
            return View::fetch('common/404');
        }

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
        $username = urldecode(base64_decode($form['username']));
        $password = urldecode(base64_decode($form['password']));
        $code = trim($form['code']);
        if (empty($username) || empty($password)) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }
        if (empty($code)) {
            return self::makeJsonReturn(false, null, '请输入验证码');
        }

        //登录失败次数检测（x分钟内失败y次，则禁止登录n分钟）
        $login_faild_time_interval = Config::get('admin.login_faild_time_interval', 5);
        $login_max_faild = Config::get('admin.login_max_faild', 10);
        $ban_login_time = Config::get('admin.ban_login_time', 30);
        $start_time = time() - $login_faild_time_interval * 60;

        $count = LoginlogModel::where([
            ['status', '=', 0], //登录失败
            ['loginip', '=', $ip], //当前IP
            ['logintime', 'BETWEEN', [$start_time, time()]] //登录时间
        ])->count();
        if ($count >= $login_max_faild) {
            return self::makeJsonReturn(false, null, "登录失败次数过多，请" . $ban_login_time . "分钟后再试！");
        }

        //验证码开始验证
        if (!$this->_vertify($code)) {
            return self::makeJsonReturn(false, null, "验证码错误，请重新输入！");
        }
        if (AdminUserService::getInstance()->login($username, $password)) {
            $forward = cookie("forward");
            if (!$forward) {
                $forward = build_url("/admin/Dashboard/index");
            } else {
                cookie("forward", null);
            }
            return self::makeJsonReturn(true, [
                'forward' => $forward
            ], "登录成功");
        } else {
            return self::makeJsonReturn(false, null, "用户名或者密码错误");
        }
    }

    /**
     * 验证码校验
     * @param $code
     * @param  string $type
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function _vertify($code, $type = "verify")
    {
        $checkcode_type = (int)AdminConfigService::getInstance()->getConfig('checkcode_type')['data'];
        $checkcode = new \app\common\libs\checkcode\Checkcode($checkcode_type);
        $checkcode->type = $type;
        return $checkcode->validate($code, false);
    }


    /**
     * 注销登录
     */
    function doLogout()
    {
        AdminUserService::getInstance()->logout();
        return self::makeJsonReturn(true, [
            'redirect' => build_url('/admin/login/index')
        ], '注销成功');
    }

}
