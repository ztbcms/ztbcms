<?php

// +----------------------------------------------------------------------
// | 通行证服务，使用Ucenter的方式！
// +----------------------------------------------------------------------

namespace Libs\Driver\Passport;

use Libs\Service\Passport;

class Ucenter extends Passport {

	public function __construct() {
		$this->config = cache("Member_Config");
		if (!$this->config['uc_api'] || !$this->config['uc_key'] || !$this->config['uc_appid']) {
			E('请检查UC通行证配置是否完整！');
		}
		//连接 UCenter 的方式
		define("UC_CONNECT", $this->config['uc_connect']);
		//UCenter 数据库主机
		define("UC_DBHOST", $this->config['uc_dbhost']);
		//UCenter 数据库用户名
		define("UC_DBUSER", $this->config['uc_dbuser']);
		//UCenter 数据库密码.
		define("UC_DBPW", $this->config['uc_dbpw']);
		//UCenter 数据库名称
		define("UC_DBNAME", $this->config['uc_dbname']);
		//UCenter 数据库字符集
		define("UC_DBCHARSET", $this->config['uc_dbcharset']);
		//UCenter 数据库表前缀
		define("UC_DBTABLEPRE", $this->config['uc_dbtablepre']);
		//与 UCenter 的通信密钥, 要与 UCenter 保持一致
		define("UC_KEY", $this->config['uc_key']);
		//UCenter 服务端的 URL 地址
		define("UC_API", $this->config['uc_api']);
		//UCenter 的 IP
		define("UC_IP", $this->config['uc_ip']);
		//UCenter 的字符集
		define("UC_CHARSET", "utf-8");
		//当前应用的 ID
		define("UC_APPID", $this->config['uc_appid']);
		define('UC_PPP', '20');
		//载入uc接口
		return require_cache(SITE_PATH . 'api/uc_client/client.php');
	}

	/**
	 * 注册会员
	 * @param type $username 用户名
	 * @param type $password 明文密码
	 * @param type $email 邮箱
	 * @return boolean
	 */
	public function userRegister($username, $password, $email) {
		//检查用户名
		$ckname = $this->userCheckUsername($username);
		if ($ckname !== true) {
			return false;
		}
		//检查邮箱
		$ckemail = $this->userCheckeMail($email);
		if ($ckemail !== true) {
			return false;
		}
		$userid = uc_user_register($username, $password, $email);
		if ($userid > 0) {
			//保存到本地
			$Member = D("Member/Member");
			$encrypt = genRandomString(6);
			$password = $Member->encryption(0, $password, $encrypt);
			$data = array(
				"userid" => $userid,
				"username" => $username,
				"password" => $password,
				"email" => $email,
				"encrypt" => $encrypt,
				"amount" => 0,
			);
			$Member->add($data);
			return $userid;
		}
		$this->error = '注册失败！';
		return false;
	}

	/**
	 * 更新用户基本资料
	 * @param type $username 用户名
	 * @param type $oldpw 旧密码
	 * @param type $newpw 新密码，如不修改为空
	 * @param type $email Email，如不修改为空
	 * @param type $ignoreoldpw 是否忽略旧密码
	 * @param type $data 其他信息
	 * @return boolean
	 */
	public function userEdit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $data = array()) {
		$model = D("Member/Member");
		$status = uc_user_edit($username, $oldpw, $newpw, $email, $ignoreoldpw);
		if ($status < 0) {
			$this->error = '用户信息修改失败！';
			return false;
		}
		//验证旧密码是否正确
		if ($ignoreoldpw == 0) {
			$info = $model->where(array("username" => $username))->find();
			$pas = $model->encryption(0, $oldpw, $info['encrypt']);
			if ($pas != $info['password']) {
				$this->error = '旧密码错误！';
				return false;
			}
		}
		if ($newpw) {
			//随机密码
			$encrypt = genRandomString(6);
			//新密码
			$password = $model->encryption(0, $newpw, $encrypt);
			$data['password'] = $password;
			$data['encrypt'] = $encrypt;
		} else {
			unset($data['password'], $data['encrypt']);
		}
		if ($email) {
			$data['email'] = $email;
		} else {
			unset($data['email']);
		}
		if (empty($data)) {
			return true;
		}
		if ($model->where(array("username" => $username))->save($data) !== false) {
			return true;
		} else {
			$this->error = '用户资料更新失败！';
			return false;
		}
	}

	/**
	 * 删除用户
	 * @param type $uid 用户UID
	 * @return boolean
	 */
	public function userDelete($uid) {
		$modelid = M("Member")->where(array("userid" => $uid))->getField("modelid");
		if (empty($modelid)) {
			$this->error = '该用户不存在，删除失败！';
			return false;
		}
		$Model_Member = cache("Model_Member");
		$tablename = ucwords($Model_Member[$modelid]['tablename']);
		if (!uc_user_delete($uid)) {
			$this->error = '用户删除失败！';
			return false;
		}
		//删除本地用户数据开始
		if (M("Member")->where(array("userid" => $uid))->delete() !== false) {
			M($tablename)->where(array("userid" => $uid))->delete();
			//删除connect
			M("Connect")->where(array("uid" => $uid))->delete();
			return 1;
		}
		$this->error = '用户删除失败！';
		return false;
	}

	/**
	 * 删除用户头像
	 * @param type $uid 用户名UID
	 * @return boolean
	 */
	public function userDeleteAvatar($uid) {
		if (uc_user_deleteavatar($uid)) {
			return true;
		} else {
			$this->error = '头像删除失败！';
			return false;
		}
	}

	/**
	 * 检查 Email 地址
	 * @param type $email 邮箱地址
	 * @return boolean
	 */
	public function userCheckeMail($email) {
		if (strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) {
			$errId = uc_user_checkemail($email);
			if ($errId) {
				return true;
			}
			$this->error = '该 Email 已经被注册！';
			return false;
		}
		$this->error = "Email 格式有误！";
		return false;
	}

	/**
	 * 注册会员
	 * @param type $username 用户名
	 * @param type $password 明文密码
	 * @param type $email 邮箱
	 * @return boolean
	 */
	public function userCheckUsername($username) {
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		if (!preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $username)) {
			$errId = uc_user_checkname($username);
			if ($errId) {
				return true;
			}
			$this->error = '用户名已经存在！';
			return false;
		}
		$this->error = '用户名不合法！';
		return false;
	}

	/**
	 * 获取上传头像FLASH代码
	 * @param type $uid 用户ID
	 * @param type $type 类型
	 * @param type $returnhtml
	 * @return string 头像代码
	 */
	public function getUploadPhotosHtml($uid, $type = 'virtual', $returnhtml = 1) {
		return uc_avatar($uid, $type, $returnhtml);
	}

	/**
	 * 获取用户头像地址
	 * @param type $uid 用户UID
	 * @param type $format 头像规格
	 * @param type $dbs 是否查库，不是有猜地址的方式获取
	 * @return type
	 */
	public function getUserAvatar($uid, $format = 90, $dbs = false) {
		//该参数为true时，表示使用查询数据库的方式，取得完整的头像地址。
		//比如QQ登录，使用QQ头像，此时可以使用该种方式
		if ($dbs) {
			$user_getavatar_cache = S("user_getavatar_$uid");
			if ($user_getavatar_cache) {
				return $user_getavatar_cache;
			} else {
				$Member = M("Member");
				$userpic = $Member->where(array("userid" => $uid))->getField("userpic");
				if ($userpic) {
					S("user_getavatar_$uid", $userpic, 3600);
				} else {
					$userpic = self::$Cache['Config']['siteurl'] . "statics/images/member/nophoto.gif";
				}
				return $userpic;
			}
		}

		//头像规格
		$avatar = array(
			180 => "big",
			90 => "middle",
			45 => "small",
			30 => "small",
		);
		$format = in_array($format, $avatar) ? $format : 90;
		$picurl = $this->config['uc_api'] . "/avatar.php?uid=" . $uid . "&size=" . $avatar[$format];
		return $picurl;
	}

	/**
	 * 获取用户信息
	 * @param type $identifier 用户/UID
	 * @param type $password 明文密码，填写表示验证密码
	 * @return array|boolean
	 */
	public function getLocalUser($identifier, $password = null) {
		if (empty($identifier)) {
			return false;
		}
		$map = array();
		if (is_int($identifier)) {
			$map['userid'] = $identifier;
			$isuid = 1;
		} else {
			$map['username'] = $identifier;
			$isuid = 0;
		}
		$UserMode = M('Member');
		$user = $UserMode->where($map)->find();
		if (empty($user)) {
			$this->error = '该用户不存在！';
			return false;
		}
		if ($password) {
			$user_login = uc_user_login($identifier, $password, $isuid);
			if ($user_login[0] < 1) {
				$this->error = '用户密码错误！';
				return false;
			}
		}
        //用户附表信息
        $user_model = M('Model')->where(['modelid' => $user['modelid']])->find();
        $user_data = M($user_model['tablename'])->where(['userid' => $user['userid']])->find();
        $user['data'] = $user_data;

		return $user;
	}

	/**
	 * 会员登录
	 * @param type $identifier 用户/UID
	 * @param type $password 明文密码，填写表示验证密码
	 * @param type $is_remember_me cookie有效期
	 * @return boolean
	 */
	public function loginLocal($identifier, $password = null, $is_remember_me = 3600) {
		$db = D("Member/Member");
		//检查登录方式
		if (is_int($identifier)) {
			$isuid = 1;
		} else {
			$isuid = 0;
		}
		$user = uc_user_login($identifier, $password, $isuid);
		if ($user[0] > 0) {
			$userid = $user[0];
			$username = $user[1];
			$ucpassword = $user[2];
			$ucemail = $user[3];
			$map = array();
			$map['userid'] = $userid;
			$map['username'] = $username;
			//取得本地相应用户
			$userinfo = $db->where($map)->find();
			//检查是否存在该用户信息
			if (empty($userinfo)) {
				//UC中有该用户，本地没有时，创建本地会员数据
				$data = array();
				$data['userid'] = $userid;
				$data['username'] = $username;
				$data['nickname'] = $username;
				$data['encrypt'] = genRandomString(6); //随机密码
				$data['password'] = $db->encryption(0, $ucpassword, $data['encrypt']);
				$data['email'] = $ucemail;
				$data['regdate'] = time();
				$data['regip'] = get_client_ip();
				$data['modelid'] = $this->config['defaultmodelid'];
				$data['point'] = $this->config['defualtpoint'];
				$data['amount'] = $this->config['defualtamount'];
				$data['groupid'] = $db->get_usergroup_bypoint($this->config['defualtpoint']);
				$data['checked'] = 1;
				$data['lastdate'] = time();
				$data['loginnum'] = 1;
				$data['lastip'] = get_client_ip();
				$db->add($data);
				$Model_Member = F("Model_Member");
				$tablename = $Model_Member[$data['modelid']]['tablename'];
				M(ucwords($tablename))->add(array("userid" => $userid));
				$userinfo = $data;
			} else {
				//更新密码
				$encrypt = genRandomString(6); //随机密码
				$pw = $db->encryption(0, $ucpassword, $encrypt);
				$db->where(array("userid" => $userid))->save(array("encrypt" => $encrypt, "password" => $pw, "lastdate" => time(), "lastip" => get_client_ip(), 'loginnum' => $userinfo['loginnum'] + 1));
				$userinfo['password'] = $pw;
				$userinfo['encrypt'] = $encrypt;
			}
			if ($this->registerLogin($userinfo, $is_remember_me)) {
				//记录登录日志
				$this->recordLogin($user['userid']);
				//登录成功
				return $userinfo['userid'];
			} else {
				$this->error = '用户注册状态失败！';
				return false;
			}
		} else {
			$this->error = '用户登录失败！';
			return false;
		}
	}

	/**
	 * 注册用户的登录状态 (即: 注册cookie + 注册session + 记录登录信息)
	 * @param array $user 用户相信信息 uid , username
	 * @param type $is_remeber_me 有效期
	 * @return type 成功返回布尔值
	 */
	public function registerLogin(array $user, $is_remeber_me = 604800) {
		parent::registerLogin($user, $is_remeber_me);
		cookie('uc_user_synlogin', 1);
		return true;
	}

	//注销登录
	public function logoutLocal() {
		parent::logoutLocal();
		echo uc_user_synlogout();
		return true;
	}

}
