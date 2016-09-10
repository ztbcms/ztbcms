<?php

namespace Member\Controller;

use Common\Controller\CMS;

define('IN_DISCUZ', TRUE);

define('UC_CLIENT_VERSION', '1.5.0'); //note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20081031');

define('API_DELETEUSER', 1); //note 用户删除 API 接口开关
define('API_RENAMEUSER', 1); //note 用户改名 API 接口开关
define('API_GETTAG', 1); //note 获取标签 API 接口开关
define('API_SYNLOGIN', 1); //note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1); //note 同步登出 API 接口开关
define('API_UPDATEPW', 1); //note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1); //note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1); //note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1); //note 更新应用列表 开关
define('API_UPDATECLIENT', 1); //note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1); //note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1); //note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1); //note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1); //note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('DISCUZ_ROOT', SITE_PATH . "/api/");

class UcenterController extends CMS {

	public function index() {
		//note 普通的 http 通知方式
		if (!defined('IN_UC')) {

			error_reporting(0);
			set_magic_quotes_runtime(0);

			defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
			service("Passport");

			$_DCACHE = $get = $post = array();

			$code = @$_GET['code'];
			parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
			if (MAGIC_QUOTES_GPC) {
				$get = _stripslashes($get);
			}

			$timestamp = time();
			if ($timestamp - $get['time'] > 3600) {
				exit('Authracation has expiried');
			}

			if (empty($get)) {
				exit('Invalid Request');
			}
			$action = $get['action'];

			require_once DISCUZ_ROOT . './uc_client/lib/xml.class.php';

			$post = xml_unserialize(file_get_contents('php://input'));

			if (in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
				exit($this->$get['action']($get, $post));
			} else {
				exit(API_RETURN_FAILED);
			}
		}
	}

	/**
	 * 此接口供仅测试连接。当 UCenter 发起 test 的接口请求时，如果成功获取到接口返回的 API_RETURN_SUCCEED 值，表示 UCenter 和应用通讯正常。
	 * @param type $get
	 * @param type $post
	 * @return string
	 */
	protected function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 删除一个用户时，会发起 deleteuser 的接口请求，通知所有应用程序删除相应的用户。
	 * @param type $get
	 * @param type $post
	 */
	protected function deleteuser($get, $post) {
		$where = array();
		$where['userid'] = array("IN", $get['ids']);
		$info = M("Member")->where($where)->select();
		if ($info) {
			$Model_Member = cache("Model_Member");
			foreach ($info as $k => $v) {
				$modelid = $v['modelid'];
				$tablename = ucwords($Model_Member[$modelid]['tablename']);
				M("Member")->where(array("userid" => $uid))->delete();
				M($tablename)->where(array("userid" => $uid))->delete();
			}
		}
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 更改一个用户的用户名时，会发起 renameuser 的接口请求，通知所有应用程序改名。
	 * @param type $get
	 * @param type $post
	 */
	protected function renameuser($get, $post) {
		$uid = $get['uid'];
		$usernamenew = $get['newusername'];
		M("Member")->where(array("userid" => $uid))->save(array("username" => $usernamenew));
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当用户更改用户密码时，此接口负责接受 UCenter 发来的新密码。
	 * @param type $get
	 * @param type $post
	 */
	protected function updatepw($get, $post) {
		$username = $get['username'];
		$password = $get['password'];
		$Member = D("Member/Member");
		$encrypt = genRandomString(6);
		//新密码
		$password = $Member->encryption(0, $password, $encrypt);
		$Member->where(array("username" => $username))->save(array("password" => $password, "encrypt" => $encrypt));
		return API_RETURN_SUCCEED;
	}

	/**
	 * 如果应用程序存在标签功能，可以通过此接口把应用程序的标签数据传递给 UCenter。
	 * @param type $get
	 * @param type $post
	 */
	protected function gettag($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 如果应用程序需要和其他应用程序进行同步登录，此部分代码负责标记指定用户的登录状态。
	 * @param type $get
	 * @param type $post
	 */
	protected function synlogin($get, $post) {
		$userid = $get['uid'];
		$info = M("Member")->where(array("userid" => $userid))->find();
		if ($info) {
			$Passport = service("Passport");
			$Passport->UCenter = FALSE;
			$Passport->registerLogin($info);
			return API_RETURN_SUCCEED;
		}
		return false;
	}

	/**
	 * 如果应用程序需要和其他应用程序进行同步退出登录，此部分代码负责撤销用户的登录的状态。
	 * @param type $get
	 * @param type $post
	 */
	protected function synlogout($get, $post) {
		$Passport = service("Passport");
		$Passport->UCenter = FALSE;
		$Passport->logoutLocal();
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 的词语过滤设置变更时，此接口负责通知所有应用程序更新后的词语过滤设置内容。
	 * @param type $get
	 * @param type $post
	 */
	protected function updatebadwords($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 的域名解析设置变更时，此接口负责通知所有应用程序更新后的域名解析设置内容。
	 * @param type $get
	 * @param type $post
	 */
	protected function updatehosts($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 的应用程序列表变更时，此接口负责通知所有应用程序更新后的应用程序列表。
	 * @param type $get
	 * @param type $post
	 */
	protected function updateapps($get, $post) {
		$post = uc_unserialize(file_get_contents('php://input'));
		$cachefile = DISCUZ_ROOT . './uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = ' . var_export($post, TRUE) . ";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当 UCenter 的基本设置信息变更时，此接口负责通知所有应用程序更新后的基本设置内容。
	 * @param type $get
	 * @param type $post
	 */
	protected function updateclient($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 当某应用执行了积分兑换请求的接口函数 uc_credit_exchange_request() 后，此接口负责通知被兑换的目的应用程序所需修改的用户积分值。
	 * @param type $get
	 * @param type $post
	 */
	protected function updatecredit($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 此接口负责把应用程序的积分设置传递给 UCenter，以供 UCenter 在积分兑换设置中使用。
	 * 此接口无输入参数。输出的数组需经过 uc_serialize 处理。
	 */
	protected function getcreditsettings() {
//        echo uc_serialize($credits);
	}

	/**
	 * 此接口负责接收 UCenter 积分兑换设置的参数。
	 * @param type $get
	 * @param type $post
	 */
	protected function updatecreditsettings($get, $post) {
		return API_RETURN_SUCCEED;
	}

	/**
	 * 此接口用于把应用程序中指定用户的积分传递给 UCenter。
	 * @param type $get
	 * @param type $post
	 */
	protected function getcredit($get, $post) {
		$uid = intval($get['uid']);
		$credit = intval($get['credit']); //积分编号
	}

}

//note 使用该函数前需要 require_once $this->appdir.'./config.inc.php';
function _setcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '') . $var, $value, $life ? $timestamp + $life : 0, $cookiepath, $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

//UC 加密函数
function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya . md5($keya . $keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for ($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for ($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for ($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if ($operation == 'DECODE') {
		if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace('=', '', base64_encode($result));
	}
}

function _stripslashes($string) {
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}
