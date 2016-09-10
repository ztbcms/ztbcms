<?php

// +----------------------------------------------------------------------
// | QQ空间帐号登录
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\Base;

class ConnectqqController extends Base {

	//APP ID
	protected $qq_akey;
	//App secret key
	protected $qq_skey;
	//会员中心配置
	protected $memberConfig;
	//标识
	private $connectMark = 'qq';

	public function _initialize() {
		parent::_initialize();
		$this->memberConfig = cache("Member_Config");
		$this->qq_akey = $this->memberConfig['qq_akey'];
		$this->qq_skey = $this->memberConfig['qq_skey'];
		if (!$this->qq_akey || !$this->qq_skey) {
			$msg = "没有进行QQ互联的相关配置，请配置后在继续使用！";
			if (APP_DEBUG) {
				// 模块不存在 抛出异常
				E($msg);
			} else {
				if (C('LOG_EXCEPTION_RECORD')) {
					Log::write($msg);
				}
				send_http_status(404);
				exit;
			}
		}
		//跳转时间
		$this->assign("waitSecond", 2000);
	}

	//跳转到授权界面
	public function index() {
		//跳转
		header("location:" . D("Member/Connect")->getUrlConnectQQ());
	}

	//回调
	public function callback() {
		//安全验证，验证state是否合法
		$state = $_GET['state'];
		if ($state != upload_key(get_client_ip())) {
			$this->error("IP不正确");
		}
		$curl = new \Curl();
		$sUrl = "https://graph.qq.com/oauth2.0/token";
		$aGetParam = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->qq_akey,
			"client_secret" => $this->qq_skey,
			"code" => $_GET["code"],
			"state" => $_GET["state"],
			"redirect_uri" => session("redirect_uri"),
		);
		session("redirect_uri", NULL);
		//Step2：通过Authorization Code获取Access Token
		foreach ($aGetParam as $key => $val) {
			$aGet[] = $key . "=" . urlencode($val);
		}
		$sContent = $curl->get($sUrl . "?" . implode("&", $aGet));

		if ($sContent == FALSE) {
			$this->error("帐号授权出现错误！");
		}
		//参数处理
		$aTemp = explode("&", $sContent);
		$aParam = array();
		foreach ($aTemp as $val) {
			$aTemp2 = explode("=", $val);
			$aParam[$aTemp2[0]] = $aTemp2[1];
		}
		//保存access_token
		session("access_token", $aParam["access_token"]);
		$sUrl = "https://graph.qq.com/oauth2.0/me";
		$aGetParam = array(
			"access_token" => $aParam["access_token"],
		);
		//$sContent = $this->get($sUrl, $aGetParam);
		foreach ($aGetParam as $key => $val) {
			$aGet[] = $key . "=" . urlencode($val);
		}
		$sContent = $curl->get($sUrl . "?" . implode("&", $aGet));

		if ($sContent == FALSE) {
			$this->error("帐号授权出现错误！");
		}
		$aTemp = array();
		//处理授权成功以后，返回的一串类似：callback( {"client_id":"000","openid":"xxx"} );
		preg_match('/callback\(\s+(.*?)\s+\)/i', $sContent, $aTemp);
		//把json数据转换为数组
		$aResult = json_decode($aTemp[1], true);
		//合并数组，把access_token和expires_in合并。
		$Result = array_merge($aResult, $aParam);
		$this->user($Result);
	}

	/**
	 * 登录/注册
	 * @param type $openid 标识
	 */
	protected function user($Result) {
		$openid = $Result['openid'];
		if (!$openid) {
			$this->error("登录失败！");
		}
		$Connect = D("Member/Connect");
		$uid = $Connect->getUserid($openid, $this->connectMark);
		if ($uid) {
			//更新access_token
			$Connect->connectSave($openid, $this->connectMark, array(
				"accesstoken" => $Result['access_token'],
				"expires" => time() + (int) $Result['expires_in'],
			));
			//存在直接登录
			$Member = D("Member");
			$info = $Member->getUserInfo((int) $uid);
			if ($info) {
				//待审核
				if ($info['checked'] == 0) {
					$this->error("该帐号还未审核通过，暂无法登录！", U("Member/Index/login"));
				}
				if (service("Passport")->registerLogin($info)) {
					$forward = $_REQUEST['forward'] ? $_REQUEST['forward'] : cookie("forward");
					redirect($forward ? $forward : U("Member/Index/index"), 0, '');
				} else {
					$this->error("登录失败！");
				}
			} else if ($status == -1) {
				$this->error("用户不存在！", U("Member/Public/connectregister"));
			} else {
				$this->error("登录失败！", U("Member/Index/login"));
			}
		} else {
			header("Content-type: text/html; charset=utf-8");
			session("connect_openid", $openid);
			session("connect_expires", time() + (int) $Result['expires_in']);
			session("connect_app", $this->connectMark);
			//不存在，跳转到注册页面
			$this->redirect('Member/Index/index');
		}
	}

}
