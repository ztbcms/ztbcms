<?php

// +----------------------------------------------------------------------
// | 后台Controller
// +----------------------------------------------------------------------

namespace Common\Controller;

use Admin\Service\User;
use Common\Traits\Crypto\CryptoControllerTrait;
use Libs\System\RBAC;

//定义是后台
define('IN_ADMIN', true);

/**
 * 后台基类
 */
class AdminBase extends CMS {

    //加密解密
    use CryptoControllerTrait;

    protected $uid;
    protected $userInfo;

    //初始化
	protected function _initialize() {
		C(array(
			"USER_AUTH_ON" => true, //是否开启权限认证
			"USER_AUTH_TYPE" => 1, //默认认证类型 1 登录认证 2 实时认证
			"REQUIRE_AUTH_CONTROLLER" => "", //需要认证控制器
			"NOT_AUTH_CONTROLLER" => "Public", //无需认证控制器
            "REQUIRE_AUTH_ACTION" => "", //需要认证的操作
            "NOT_AUTH_ACTION" => "", //无需认证的操作
			"USER_AUTH_GATEWAY" => C('USER_AUTH_GATEWAY', null , U("Admin/Public/login")) , //登录地址
		));
		if (false == RBAC::AccessDecision(MODULE_NAME)) {
			//检查是否登录
			if (false === RBAC::checkLogin()) {
				//未登录
                if (IS_AJAX) {
                    //接口返回
                    $this->ajaxReturn(self::createReturn(false, [
                        'user_auth_gateway' => C('USER_AUTH_GATEWAY')
                    ], '请登录', 401));
                } else {
                    //跳转到登录界面
                    $this->assign('user_auth_gateway', C('USER_AUTH_GATEWAY'));
                    $this->display(C('TMPL_ACTION_PAGE_UNAUTHORIZED'));
                }
                exit;
			}
			//没有操作权限
            if (IS_AJAX) {
                $this->ajaxReturn(self::createReturn(false, null, '无权限', 403));
            } else {
                $this->display(C('TMPL_ACTION_PAGE_FORBIDDEN'));
            }
            exit;
		}
		parent::_initialize();
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
			return false;
		}
        $this->uid = $uid;
        //获取当前登录用户信息
		$userInfo = User::getInstance()->getInfo();
		if (empty($userInfo)) {
			User::getInstance()->logout();
			return false;
		}
        $this->userInfo = $userInfo;
        //是否锁定
		if (!$userInfo['status']) {
			User::getInstance()->logout();
			$this->error('您的帐号已经被锁定！', U('Public/login'));
			return false;
		}
		return true;
	}

	/**
	 * 操作错误跳转的快捷方法
	 * @access protected
	 * @param string $message 错误信息
	 * @param string $jumpUrl 页面跳转地址
	 * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @return void
	 */
	final public function error($message = '', $jumpUrl = '', $ajax = false) {
		D('Admin/Operationlog')->record($message, 0);
		parent::error($message, $jumpUrl, $ajax);
	}

	/**
	 * 操作成功跳转的快捷方法
	 * @access protected
	 * @param string $message 提示信息
	 * @param string $jumpUrl 页面跳转地址
	 * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @return void
	 */
	final public function success($message = '', $jumpUrl = '', $ajax = false) {
		D('Admin/Operationlog')->record($message, 1);
		parent::success($message, $jumpUrl, $ajax);
	}

	/**
	 * 分页输出
	 * @param int $total 信息总数
	 * @param int $size 每页数量
	 * @param int $number 当前分页号（页码）
	 * @param array $config 配置，会覆盖默认设置
	 * @return array|mixed
	 */
	protected function page($total, $size = 20, $number = 0, $config = array()) {
		$Page = parent::page($total, $size, $number, $config);
		$Page->SetPager('default', '<span class="all">共有{recordcount}条信息</span>{first}{prev}{liststart}{list}{listend}{next}{last}');
		return $Page;
	}

}
