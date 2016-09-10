<?php

// +----------------------------------------------------------------------
// | 会员帐号绑定信息
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class ConnectModel extends Model {

	//会员中心配置
	protected $memberConfig;
	//自动验证
	protected $_validate = array(
		array('openid', 'require', 'openid不能为空！', 1, 'regex', 1),
		array('uid', 'require', '用户ID不能为空！', 1, 'regex', 1),
		array('app', 'require', '授权应用名称不能为空！', 1, 'regex', 1),
		array('accesstoken', 'require', '授权码不能为空！', 1, 'regex', 1),
		array('expires', 'require', '授权过期时间不能为空！', 1, 'regex', 1),
	);
	//自动完成
	protected $_auto = array(
	);

	/**
	 * 获取QQ授权地址
	 * @param type $redirect_uri
	 * @return type
	 */
	public function getUrlConnectQQ($redirect_uri = '') {
		$this->memberConfig = cache("Member_Config");
		$qq_akey = $this->memberConfig['qq_akey'];
		$qq_skey = $this->memberConfig['qq_skey'];
		if (empty($qq_akey) || empty($qq_skey)) {
			$this->error = '没有进行QQ互联的相关配置，请配置后在继续使用！';
		}
		$sState = upload_key(get_client_ip());
		session("state", $sState);
		//回调地址
		if (empty($redirect_uri)) {
			$redirect_uri = CONFIG_SITEURL_MODEL . "index.php?g=Member&m=Connectqq&a=callback";
		}
		session("redirect_uri", $redirect_uri);
		//请求用户授权时向用户显示的可进行授权的列表
		$scope = "get_user_info,add_share,check_page_fans";
		//请求参数
		$aParam = array(
			"response_type" => "code",
			"client_id" => $qq_akey,
			"redirect_uri" => $redirect_uri,
			"scope" => $scope,
			"state" => $sState,
		);

		//对参数进行URL编码
		$aGet = array();
		foreach ($aParam as $key => $val) {
			$aGet[] = $key . "=" . urlencode($val);
		}
		//请求地址
		$sUrl = "https://graph.qq.com/oauth2.0/authorize?";
		$sUrl .= join("&", $aGet);
		return $sUrl;
	}

	/**
	 * 获取新浪微博授权地址
	 * @param type $redirect_uri
	 * @return type
	 */
	public function getUrlConnectSinaWeibo($redirect_uri = '') {
		$this->memberConfig = cache("Member_Config");
		$sinawb_akey = $this->memberConfig['sinawb_akey'];
		$sinawb_skey = $this->memberConfig['sinawb_skey'];
		if (empty($sinawb_akey) || empty($sinawb_skey)) {
			$this->error = '获取不到相关配置，新浪互联无法进行！';
		}
		$sState = upload_key(get_client_ip());
		session("state", $sState);
		//回调地址
		if (empty($redirect_uri)) {
			$redirect_uri = CONFIG_SITEURL_MODEL . "index.php?g=Member&m=Connectsina&a=callback";
		}
		session("redirect_uri", $redirect_uri);
		//请求参数
		$aParam = array(
			"client_id" => $sinawb_akey, //申请应用时分配的AppKey。
			"redirect_uri" => $redirect_uri, //授权回调地址
			"state" => $sState,
		);

		//对参数进行URL编码
		$aGet = array();
		foreach ($aParam as $key => $val) {
			$aGet[] = $key . "=" . urlencode($val);
		}
		//请求地址
		$sUrl = "https://api.weibo.com/oauth2/authorize?";
		$sUrl .= join("&", $aGet);
		return $sUrl;
	}

	/**
	 * 根据授权信息，取得对应绑定的用户ID
	 * @param type $openid
	 * @param type $app
	 * @return boolean
	 */
	public function getUserid($openid, $app) {
		if (empty($openid) || empty($app)) {
			return false;
		}
		return $this->where(array("openid" => $openid, "app" => $app))->getField("uid");
	}

	/**
	 * 根据用户ID取得相应的授权记录
	 * @param type $userid 用户ID
	 * @param type $app 应用标识
	 * @return boolean
	 */
	public function getUserAuthorize($userid, $app) {
		if (empty($userid) || empty($app)) {
			return false;
		}
		$info = $this->where(array('uid' => $userid, 'app' => $app))->find();
		if (empty($info)) {
			return false;
		}
		return $info;
	}

	/**
	 * 更新授权信息
	 * @param type $openid
	 * @param type $app
	 * @param type $data
	 * @return boolean
	 */
	public function connectSave($openid, $app, $data) {
		C('TOKEN_ON', false);
		if (empty($openid) || empty($app)) {
			return false;
		}
		if (empty($data)) {
			$this->error = '数据不能为空';
			return false;
		}
		$data = $this->create($data, 2);
		if ($data) {
			if (false !== $this->where(array("openid" => $openid, "app" => $app))->save($data)) {
				return true;
			} else {
				$this->error = '更新失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 检查用户是否已经授权过
	 * @param type $access_token
	 * @param type $app
	 * @return boolean
	 */
	public function isUserAuthorize($access_token, $app) {
		if (empty($access_token) || empty($app)) {
			return false;
		}
		$info = $this->where(array('accesstoken' => $access_token, 'app' => $app))->count();
		if (empty($info)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 添加绑定信息
	 * @param type $data
	 * @return boolean
	 */
	public function connectAdd($data) {
		C('TOKEN_ON', false);
		if (empty($data)) {
			$this->error = '数据不能为空！';
			return false;
		}
		$data = $this->create($data, 1);
		if ($data) {
			$connectid = $this->add($data);
			if ($connectid) {
				return $connectid;
			} else {
				$this->error = '帐号绑定失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 删除授权关系
	 * @param type $connectid
	 * @return boolean
	 */
	public function connectDel($connectid, $userid = 0) {
		if (empty($connectid)) {
			$this->error = '请指定需要删除的授权信息！';
			return false;
		}
		$where = array(
			'connectid' => $connectid,
		);
		if (is_array($connectid)) {
			$where['connectid'] = array('IN', $connectid);
		}
		if (!empty($userid)) {
			$where['uid'] = $userid;
		}
		//执行删除
		if (false !== $this->where($where)->delete()) {
			return true;
		} else {
			$this->error = '删除授权失败！';
			return false;
		}
	}

}
