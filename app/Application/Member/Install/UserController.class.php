<?php

// +----------------------------------------------------------------------
// | 获取当前登录信息
// +----------------------------------------------------------------------

namespace Api\Controller;

use Common\Controller\Base;

class UserController extends Base {

	//用户id
	protected $userid = 0;
	//用户名
	protected $username = NULL;
	//用户信息
	protected $userinfo = array();

	protected function _initialize() {
		parent::_initialize();
		$this->userid = (int) service("Passport")->userid;
		$this->username = service("Passport")->username;
		$this->userinfo = service("Passport")->getInfo();
	}

	//jsonp/json的方式获取当前登录信息
	public function getuser() {
		$data = array(
			'userid' => $this->userid,
			'username' => $this->username,
			//昵称
			'nickname' => $this->userinfo['nickname'],
			//头像地址
			'avatar' => $this->userid ? service("Passport")->getUserAvatar((int) $this->userid, 45) : '',
			//分享总数
			'dance_num' => $this->userinfo['share'],
			//状态
			'status' => $this->userid ? true : false,
		);
		$callback = I('request.callback', '');
		if (empty($callback)) {
			$type = 'JSON';
		} else {
			$type = 'JSONP';
			C('VAR_JSONP_HANDLER', 'callback');
		}
		$this->ajaxReturn($data, $type);
	}

}
