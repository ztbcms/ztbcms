<?php
/**
 * User: jayinton
 * Date: 2018/12/22
 * Time: 14:20
 */

namespace Admin\Controller;

use Admin\Service\User;
use Common\Controller\AdminBase;
use Common\Controller\CMS;
use Libs\System\RBAC;

//定义是后台
define('IN_ADMIN', true);

class AdminApiBaseController extends CMS
{
    protected $uid;
    protected $userInfo;

    protected function _initialize()
    {
        parent::_initialize();
        $this->supportCros();

        C(array(
            "USER_AUTH_ON" => true, //是否开启权限认证
            "USER_AUTH_TYPE" => 1, //默认认证类型 1 登录认证 2 实时认证
            "REQUIRE_AUTH_CONTROLLER" => "", //需要认证控制器
            "NOT_AUTH_CONTROLLER" => "AdminApi", //无需认证控制器
            "REQUIRE_AUTH_ACTION" => "", //需要认证的操作
            "NOT_AUTH_ACTION" => "", //无需认证的操作
            "USER_AUTH_GATEWAY" => C('USER_AUTH_GATEWAY', null , U("Admin/Public/login")) , //登录地址
        ));
        if (false == RBAC::AccessDecision(MODULE_NAME)) {
            //检查是否登录
            if (false === RBAC::checkLogin()) {
                //跳转到登录界面
                $this->ajaxReturn(self::createReturn(false, [
                    'user_auth_gateway' => C('USER_AUTH_GATEWAY')
                ], '请登录账号', 401));
                return;
            }
            //没有操作权限
            $this->ajaxReturn(self::createReturn(false, null, '您没有操作此项的权限', 403));
            return;
        }

        //验证登录
        $this->competence();
    }

    /**
     * 验证登录
     * @return boolean
     */
    private function competence() {
        //检查是否登录
        $uid = (int) User::getInstance()->isLogin();
        if (empty($uid)) {
            $this->ajaxReturn(self::createReturn(false, [
                'user_auth_gateway' => C('USER_AUTH_GATEWAY')
            ],'请登录',401));
        }
        $this->uid = $uid;
        //获取当前登录用户信息
        $userInfo = User::getInstance()->getInfo();
        if (empty($userInfo)) {
            User::getInstance()->logout();
            $this->ajaxReturn(self::createReturn(false, [
                'user_auth_gateway' => C('USER_AUTH_GATEWAY')
            ],'请登录',401));
            return false;
        }
        $this->userInfo = $userInfo;
        //是否锁定
        if (!$userInfo['status']) {
            User::getInstance()->logout();
            $this->ajaxReturn(self::createReturn(false, [
                'user_auth_gateway' => C('USER_AUTH_GATEWAY')
            ],'您的帐号已经被锁定' ,401));
            return false;
        }
        return true;
    }

    function supportCros(){
        //http 预检响应
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: *');
            header('Access-Control-Allow-Headers: *,'); //不能设置为 *，必须指定
            header('Access-Control-Max-Age: 86400'); // cache for 1 day

            exit();
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *'); //不能设置为 *，必须指定
    }
}