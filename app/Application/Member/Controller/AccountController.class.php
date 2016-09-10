<?php

// +----------------------------------------------------------------------
// | 帐户管理
// +----------------------------------------------------------------------

namespace Member\Controller;

class AccountController extends MemberbaseController {

	//互联模型
	protected $connect = NULL;

	protected function _initialize() {
		parent::_initialize();
		$this->connect = D('Member/Connect');
	}

	//个人帐户
	public function assets() {

		$this->assign('isqqlogin', $this->connect->getUserAuthorize($this->userid, 'qq'));
		$this->assign('isweibologin', $this->connect->getUserAuthorize($this->userid, 'sina_weibo'));
		$this->display();
	}

	//取消绑定
	public function cancelbind() {
		$connectid = I('get.connectid', 0, 'intval');
		if (empty($connectid)) {
			$this->error('参数不正确！');
		}
		//查询出绑定信息
		$info = $this->connect->where(array('connectid' => $connectid, 'uid' => $this->userid))->find();
		if (empty($info)) {
			$this->error('该绑定信息不存在，无法解绑！');
		}
		if ($this->connect->connectDel($connectid, $this->userid)) {
			$this->success('解绑成功！');
		} else {
			$this->error('解绑失败！');
		}
	}

	//授权绑定
	public function authorize() {
		$type = I('get.type');
		if (empty($type)) {
			$this->error('请指定授权类型！');
		}
		switch ($type) {
			case 'qq':
				$redirect_uri = self::$Cache['Config']['siteurl'] . "index.php?g=Member&m=Account&a=qqbind";
				header("location:" . $this->connect->getUrlConnectQQ($redirect_uri));
				break;
			case 'sina_weibo':
				$redirect_uri = self::$Cache['Config']['siteurl'] . "index.php?g=Member&m=Account&a=sinabind";
				header("location:" . $this->connect->getUrlConnectSinaWeibo($redirect_uri));
				break;
			default:
				$this->error('授权类型错误！');
				break;
		}
	}

	//QQ绑定
	public function qqbind() {
		$curl = new \Curl();
		$sUrl = "https://graph.qq.com/oauth2.0/token";
		$aGetParam = array(
			"grant_type" => "authorization_code",
			"client_id" => $this->memberConfig['qq_akey'],
			"client_secret" => $this->memberConfig['qq_skey'],
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
		//检查帐号是否已经绑定过了
		if ($this->connect->isUserAuthorize($Result['access_token'], 'qq')) {
			$this->error('您已经绑定过，不能重复绑定！');
		}
		//绑定
		if ($this->connect->connectAdd(array(
			'openid' => $Result['openid'],
			'uid' => $this->userid,
			'app' => 'qq',
			'accesstoken' => $Result['access_token'],
			'expires' => time() + (int) $Result['expires_in'],
		))) {
			$this->success('绑定成功！', U('Account/assets'));
		} else {
			$this->error($this->connect->getError() ?: '绑定失败！');
		}
	}

	//新浪微博绑定
	public function sinabind() {
		$curl = new \Curl();
		$sUrl = "https://api.weibo.com/oauth2/access_token";
		$aGetParam = array(
			"code" => $_GET["code"], //用于调用access_token，接口获取授权后的access token
			"client_id" => $this->memberConfig['sinawb_akey'], //申请应用时分配的AppSecret
			"client_secret" => $this->memberConfig['sinawb_skey'], //申请应用时分配的AppSecret
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
		//检查帐号是否已经绑定过了
		if ($this->connect->isUserAuthorize($aParam['access_token'], 'sina_weibo')) {
			$this->error('您已经绑定过，不能重复绑定！');
		}
		//绑定
		if ($this->connect->connectAdd(array(
			'openid' => $aParam['uid'],
			'uid' => $this->userid,
			'app' => 'sina_weibo',
			'accesstoken' => $aParam['access_token'],
			'expires' => time() + (int) $aParam['expires_in'],
		))) {
			$this->success('绑定成功！', U('Account/assets'));
		} else {
			$this->error($this->connect->getError() ?: '绑定失败！');
		}
	}

}
