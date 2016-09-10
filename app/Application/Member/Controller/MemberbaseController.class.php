<?php

// +----------------------------------------------------------------------
// | 前台会员中心Action Base
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\Base;

class MemberbaseController extends Base {

	//会员模型相关配置
	protected $memberConfig = array();
	//会员模型缓存
	protected $memberModel = array();
	//会有组缓存
	protected $memberGroup = array();
	//会员数据库对象
	protected $memberDb = NULL;
	//用户id
	protected $userid = 0;
	//用户名
	protected $username = NULL;
	//用户信息
	protected $userinfo = array();
	//是否connect登陆
	protected $isConnectLogin = false;

	protected function _initialize() {
		G('run');
		parent::_initialize();
		$this->memberConfig = cache("Member_Config");
		$this->memberGroup = cache("Member_group");
		$this->memberModel = cache("Model_Member");
		$this->memberDb = D('Member/Member');
		//dump(service("Passport")->userCheckUsername('admin'));exit;
		//登陆检测
		$this->check_member();
		//============全局模板变量==============
		//会员组数组
		$this->assign("Member_group", $this->memberGroup);
		//会员模型配置
		$this->assign("Member_config", $this->memberConfig);
		//会员模型数组
		$this->assign("Model_member", $this->memberModel);
	}

	/**
	 * 检测用户是否已经登陆
	 */
	final public function check_member() {
		$this->userid = (int) service("Passport")->userid;
		$this->username = service("Passport")->username;
		$this->userinfo = service("Passport")->getInfo();
		$this->assign('uid', $this->userid);
		$this->assign('username', $this->username);
		//检查是否授权登陆
		$connect_app = session('connect_app');
		if (!empty($connect_app)) {
			$this->isConnectLogin = true;
		}
		if (substr(ACTION_NAME, 0, 7) == 'public_') {
			//所有以public_开头的方法都无需检测是否登陆
			return true;
		}
		//特定模块无需登陆验证
		if (MODULE_NAME == 'Member' && in_array(CONTROLLER_NAME, array('Public', 'Home'))) {
			//Public,Home不用验证登陆
			if (empty($this->userid)) {
				$this->assign('uid', 0);
			}
			return true;
		}
		//特定方法无需登陆验证
		if (MODULE_NAME == 'Member' && CONTROLLER_NAME == 'Index' && in_array(ACTION_NAME, array('login', 'register', 'logout', 'lostpassword', 'resetpassword', 'verifyemail'))) {
			//该类方法不需要验证是否登陆
			return true;
		}
		if ($this->userid) {
			//禁止访问会员组
			if ($this->userinfo['groupid'] == 1) {
				service("Passport")->logoutLocal();
				$this->error("您的会员组为禁止访问！", self::$Cache['Config']['siteurl']);
			} else if ($this->userinfo['groupid'] == 7) {
//邮箱认证
				service("Passport")->logoutLocal();
				$this->error("您还没有进行邮箱认证！", self::$Cache['Config']['siteurl']);
			}
			//锁定用户
			if ($this->userinfo['islock'] == 1) {
				service("Passport")->logoutLocal();
				$this->error("您的帐号已经被锁定！", self::$Cache['Config']['siteurl']);
			}
			return true;
		} else {
			//对待授权登陆的，进行特别处理把！
			if ($this->isConnectLogin) {
				$this->qq_akey = $this->memberConfig['qq_akey'];
				$this->qq_skey = $this->memberConfig['qq_skey'];
				$getConnect = session('connectname');
				if (empty($getConnect)) {
					//oppid
					$connect_openid = session('connect_openid');
					//授权码
					$access_token = session('access_token');
					$curl = new \Curl();
					//取得授权用户基本信息
					$sUrl = "https://graph.qq.com/user/get_user_info?";
					$aGetParam = array(
						"access_token" => $access_token,
						"oauth_consumer_key" => $this->qq_akey,
						"openid" => $connect_openid,
						"format" => "json",
					);
					$user_info = $curl->get($sUrl . http_build_query($aGetParam));
					//把json数据转换为数组
					$user_info = json_decode($user_info, true);
					$connect['userinfo'] = $user_info;
					$connect['userinfo']['name'] = $user_info['nickname'];
					session('connectname', $connect);
				}

				$this->userid = 0;
				$this->username = $getConnect['userinfo']['name'];
				$this->userinfo['groupid'] = 8;
				if (MODULE_NAME == 'Member' && in_array(CONTROLLER_NAME, array('Index', 'Feed')) && in_array(ACTION_NAME, array('index', 'home', 'logout', 'fetchfeed'))) {
					return true;
				} else {
					$this->assign('waitSecond', '5');
					$this->error('请完善资料后在继续操作！<a href="' . U("Member/Public/connectregister") . '">Go...马上去完善资料？</a>', U("Member/Public/connectregister"));
				}
			}
			service("Passport")->logoutLocal();
			$forward = isset($_REQUEST['forward']) ? $_REQUEST['forward'] : get_url();
			cookie("forward", $forward);
			if (IS_AJAX) {
				$this->message(20001);
			}
			$this->redirect('Member/Index/login');
		}
	}

	/**
	 * 信息提示
	 * @access protected
	 * @param string $message 错误信息，可以是错误代码
	 * @param array $data 附带数据
	 * @param $type error 错误提示，success，普通小心提示
	 * @return void
	 */
	protected function message($message = '', $data = array(), $type = 'success') {
		//如果是错误代码
		if (is_numeric($message)) {
			switch ($message) {
				case 1:
					$message = '名字中带有禁用词，请更换一个！';
					$data['error'] = 1;
					break;
				case 2:
					$message = '名字不能超过12个字母或6个汉字！';
					$data['error'] = 2;
					break;
				case 3:
					$message = '名字中至少要含有一个字母或汉字！';
					$data['error'] = 3;
					break;
				case 4:
					$message = '输入的电子邮箱格式不正确！';
					$data['error'] = 4;
					break;
				case 10000:
					$message = '操作成功！';
					$data['error'] = 10000;
					break;
				case 10005:
					$message = '登陆账号不能为空！';
					$data['error'] = 10005;
					break;
				case 10006:
					$message = '分组名超过不能超过七个字！';
					$data['error'] = 10006;
					break;
				case 10007:
					$message = '分组名称不能为空！';
					$data['error'] = 10007;
					break;
				case 1011:
					$message = '该帐号已经存在，请更换一个！';
					$data['error'] = 1011;
					break;
				case 20001:
					$message = '您没有登录或已经退出，请登录后再进行操作 ！';
					$data['error'] = 20001;
					break;
				case 20002:
					$message = '您没有权限进行操作 ！';
					$data['error'] = 20002;
					break;
				case 20011:
					$message = '此邮箱已经存在，请更换一个！';
					$data['error'] = 20011;
					break;
				case 20014:
					$message = '该账号已被锁定！';
					$data['error'] = 20014;
					break;
				case 20015:
					$message = '邮箱账号未激活！';
					$data['error'] = 20015;
					break;
				case 20021:
					$message = '两次输入密码不相同！';
					$data['error'] = 20021;
					break;
				case 20022:
					$message = '当前密码不正确，请从新输入！';
					$data['error'] = 20022;
					break;
				case 20023:
					$message = '账号或密码错误！';
					$data['error'] = 20023;
					break;
				case 20024:
					$message = '请输入您的密码！';
					$data['error'] = 20024;
					break;
				case 20025:
					$message = '密码长度应是6位以上！';
					$data['error'] = 20025;
					break;
				case 20031:
					$message = '验证码错误！';
					$data['error'] = 20031;
					break;
				default:
					break;
			}
		}
		//如果是数组
		if (is_array($message)) {
			$info = $message['info'];
			$error = $message['error'];
			$message = $info;
			$data['error'] = $error;
		}
		if (IS_AJAX) {
			$callback = I('request.callback', '');
			if (empty($callback)) {
				$type = 'JSON';
			} else {
				$type = 'JSONP';
				C('VAR_JSONP_HANDLER', 'callback');
			}
			$data['info'] = $message;
			$data['status'] = $data['error'] == 10000 ? true : false;
			$this->ajaxReturn($data, $type);
		} else {
			if ('success' === $type) {
				$this->success($message);
			} else {
				$this->error($message);
			}
		}
		exit;
	}

}
