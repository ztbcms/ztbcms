<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use app\admin\model\AdminUserModel;
use app\admin\model\LoginlogModel;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use app\common\libs\helper\ClientIpHelper;
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
        //如果已经登录，需实时校验账号仍然存在且处于启用状态
        $adminUserService = AdminUserService::getInstance();
        $userId = (int) $adminUserService->isLogin();
        if ($userId > 0) {
            $userInfo = $adminUserService->getRealtimeUserInfoById($userId);
            if (!empty($userInfo) && (int) ($userInfo['status'] ?? AdminUserModel::STATUS_DISABLE) === AdminUserModel::STATUS_ENABLE) {
                return redirect(build_url('/admin/dashboard/index'));
            }
            $adminUserService->logout();
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
     *
     * @return \think\response\Json
     */
    function doLogin()
    {
        if (!Request::isPost()) {
            return self::returnMethodNotAllowedJson();
        }
        //获取受信任的客户端真实 IP（未配置受信代理时忽略 X-Forwarded-For 等伪造头）
        $ip = ClientIpHelper::getClientIp();
        $form = Request::post('form', []);
        if (!is_array($form)) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }

        $encodedUsername = $form['username'] ?? null;
        $encodedPassword = $form['password'] ?? null;
        $code = $form['code'] ?? null;
        if (!is_string($encodedUsername) || !is_string($encodedPassword)) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }
        if (!is_string($code)) {
            return self::makeJsonReturn(false, null, '请输入验证码');
        }

        $decodedUsername = base64_decode($encodedUsername, true);
        $decodedPassword = base64_decode($encodedPassword, true);
        if ($decodedUsername === false || $decodedPassword === false) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }

        $username = urldecode($decodedUsername);
        $password = urldecode($decodedPassword);
        $code = trim($code);
        if (empty($username) || empty($password)) {
            return self::makeJsonReturn(false, null, '用户名或者密码不能为空，请重新输入');
        }
        if (empty($code)) {
            return self::makeJsonReturn(false, null, '请输入验证码');
        }

        $now = time();
        // 1. 基于客户端 IP 的失败频次限制
        $login_faild_time_interval = max(1, (int) Config::get('admin.login_faild_time_interval', 5));
        $login_max_faild = max(1, (int) Config::get('admin.login_max_faild', 5));
        $ban_login_time = max(1, (int) Config::get('admin.ban_login_time', 30));

        if ($this->isIpLocked($ip, $now, $login_faild_time_interval, $login_max_faild, $ban_login_time)) {
            return self::makeJsonReturn(false, null, "登录失败次数过多，请" . $ban_login_time . "分钟后再试！");
        }

        // 2. 基于目标账号维度的连续失败锁定（防范分布式代理池撞库爆破）
        $accountFailInterval = max(1, (int) Config::get('admin.login_account_faild_time_interval', 15));
        $accountMaxFail = max(1, (int) Config::get('admin.login_account_max_faild', 5));
        $accountBanTime = max(1, (int) Config::get('admin.login_account_ban_time', 15));

        if ($this->isAccountLocked($username, $now, $accountFailInterval, $accountMaxFail, $accountBanTime)) {
            return self::makeJsonReturn(false, null, "账号登录失败次数过多，请" . $accountBanTime . "分钟后再试！");
        }

        //验证码开始验证
        if (!$this->_vertify($code)) {
            return self::makeJsonReturn(false, null, "验证码错误，请重新输入！");
        }
        if (AdminUserService::getInstance()->login($username, $password)) {
            return self::makeJsonReturn(true, [
                'forward' => build_url("/admin/Dashboard/index")
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
        if (!Request::isPost()) {
            return self::returnMethodNotAllowedJson();
        }
        AdminUserService::getInstance()->logout();
        return self::makeJsonReturn(true, [
            'redirect' => build_url('/admin/login/index')
        ], '注销成功');
    }

    /**
     * 判断客户端 IP 是否处于登录失败锁定期间
     *
     * @param string $ip              客户端 IP
     * @param int    $now             当前时间戳
     * @param int    $intervalMinutes 失败统计窗口（分钟）
     * @param int    $maxFail         窗口内允许最大失败次数
     * @param int    $banMinutes      锁定时长（分钟）
     * @return bool
     */
    private function isIpLocked(string $ip, int $now, int $intervalMinutes, int $maxFail, int $banMinutes): bool
    {
        $lookbackSeconds = max($intervalMinutes, $banMinutes) * 60;
        $recentFails = LoginlogModel::where('status', '=', 0)
            ->where('loginip', '=', $ip)
            ->where('logintime', '>=', $now - $lookbackSeconds)
            ->order('logintime', 'desc')
            ->order('id', 'desc')
            ->limit($maxFail)
            ->select();

        if (count($recentFails) < $maxFail) {
            return false;
        }

        $latestFailTime = (int) $recentFails[0]['logintime'];
        $oldestFailTime = (int) $recentFails[$maxFail - 1]['logintime'];

        return ($latestFailTime - $oldestFailTime) <= ($intervalMinutes * 60)
            && ($now - $latestFailTime) < ($banMinutes * 60);
    }

    /**
     * 判断目标账号是否因连续失败处于锁定期间（成功登录后自动重置连续计数）
     *
     * @param string $username        目标用户名
     * @param int    $now             当前时间戳
     * @param int    $intervalMinutes 连续失败判定窗口（分钟）
     * @param int    $maxFail         连续失败阈值
     * @param int    $banMinutes      锁定时长（分钟）
     * @return bool
     */
    private function isAccountLocked(string $username, int $now, int $intervalMinutes, int $maxFail, int $banMinutes): bool
    {
        $lookbackSeconds = max($intervalMinutes, $banMinutes) * 60;
        $recentLogs = LoginlogModel::where('username', '=', $username)
            ->where('logintime', '>=', $now - $lookbackSeconds)
            ->order('logintime', 'desc')
            ->order('id', 'desc')
            ->limit($maxFail)
            ->select();

        $consecutiveFails = 0;
        $latestFailTime = 0;
        $oldestFailInStreak = 0;

        foreach ($recentLogs as $row) {
            if ((int) ($row['status'] ?? 0) === 1) {
                break;
            }
            $logTime = (int) ($row['logintime'] ?? 0);
            if ($consecutiveFails === 0) {
                $latestFailTime = $logTime;
            }
            $oldestFailInStreak = $logTime;
            $consecutiveFails++;
        }

        if ($consecutiveFails < $maxFail) {
            return false;
        }

        return ($latestFailTime - $oldestFailInStreak) <= ($intervalMinutes * 60)
            && ($now - $latestFailTime) < ($banMinutes * 60);
    }

}
