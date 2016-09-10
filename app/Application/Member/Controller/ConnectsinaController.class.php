<?php

// +----------------------------------------------------------------------
// | 新浪互联帐号登录
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\Base;

class ConnectsinaController extends Base {

	//会员中心配置
	protected $memberConfig;
	//App Key
	protected $sinawb_akey;
	//App Secret
	protected $sinawb_skey;
	//标识
	private $connectMark = 'sina_weibo';

	public function _initialize() {
		parent::_initialize();
		$this->memberConfig = cache("Member_Config");
		$this->sinawb_akey = $this->memberConfig['sinawb_akey'];
		$this->sinawb_skey = $this->memberConfig['sinawb_skey'];
		if (!$this->sinawb_akey || !$this->sinawb_skey) {
			$msg = "没有进行新浪互联的相关配置，请配置后在继续使用！";
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
		header("location:" . D("Member/Connect")->getUrlConnectSinaWeibo());
	}

	//回调
	public function callback() {
		//安全验证，验证state是否合法
		$state = $_GET['state'];
		if ($state != upload_key(get_client_ip())) {
			$this->error("IP不正确");
		}
		$curl = new \Curl();
		$sUrl = "https://api.weibo.com/oauth2/access_token";
		$aGetParam = array(
			"code" => $_GET["code"], //用于调用access_token，接口获取授权后的access token
			"client_id" => $this->sinawb_akey, //申请应用时分配的AppSecret
			"client_secret" => $this->sinawb_skey, //申请应用时分配的AppSecret
			"grant_type" => "authorization_code", //请求的类型，可以为authorization_code、password、refresh_token。
			"redirect_uri" => session("redirect_uri"), //回调地址
		);
		session("redirect_uri", NULL);

		$sContent = $curl->post($sUrl, $aGetParam);

		if ($sContent == FALSE) {
			$this->error("帐号授权出现错误！");
		}
		//参数处理
		$aParam = json_decode($sContent, true);
		//保存access_token
		session("access_token", $aParam["access_token"]);

		//新浪微博没有类似腾讯还需取得openid，直接以新浪uid作为标识
		$this->user($aParam);
	}

	/**
	 * 登录/注册
	 * @param type $openid 标识
	 */
	protected function user($Result) {
		$openid = $Result['uid'];
		if (!$openid) {
			$this->error("授权失败！", U("Connectsina/index"));
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
					$this->error("登录失败！", U("Member/Index/login"));
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
			$this->redirect('Member/Public/connectregister');
		}
	}

}
