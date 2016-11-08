<?php

// +----------------------------------------------------------------------
// | 通行证服务，使用本地帐号
// +----------------------------------------------------------------------

namespace Libs\Driver\Passport;

use Libs\Service\Passport;

class Local extends Passport {

	/**
	 * 会员登录
	 * @param type $identifier 用户/UID
	 * @param type $password 明文密码，填写表示验证密码
	 * @param type $is_remember_me cookie有效期
	 * @return boolean
	 */
	public function loginLocal($identifier, $password = null, $is_remember_me = 604800) {
		$map = array();
		if (is_int($identifier)) {
			$map['userid'] = $identifier;
			$identifier = intval($identifier);
		} else {
			$map['username'] = $identifier;
		}
		$userinfo = $this->getLocalUser($identifier);
		if (empty($userinfo)) {
			return false;
		}
		//是否需要进行密码验证
		if (!empty($password)) {
			$encrypt = $userinfo['encrypt'];
			//对明文密码进行加密
			$password = D("Member/Member")->encryption($identifier, $password, $encrypt);
			if ($password != $userinfo['password']) {
				$this->error = '密码错误！';
				return false;
			}
		}
		//注册用户登录状态
		if ($this->registerLogin($userinfo, $is_remember_me)) {
			//修改登录时间，和登录IP
			D("Member/Member")->where($map)->save(
				array(
					"lastdate" => time(),
					"lastip" => get_client_ip(),
					"loginnum" => $userinfo['loginnum'] + 1,
				)
			);
			//记录登录日志
			$this->recordLogin($user['userid']);
			//登录成功
			return $userinfo['userid'];
		}
		$this->error = '用户注册状态失败！';
		return false;
	}

	/**
	 * 获取用户信息
	 * @param type $identifier 用户/UID
	 * @param type $password 明文密码，填写表示验证密码
	 * @return array|boolean
	 */
	public function getLocalUser($identifier, $password = null) {
		$map = array();
		if (empty($identifier)) {
			$this->error = '参数为空！';
			return false;
		}
		if (is_int($identifier)) {
			$map['userid'] = $identifier;
		} else {
			$map['username'] = $identifier;
		}
		$UserMode = D('Member/Member');
		$user = $UserMode->where($map)->find();
		if (empty($user)) {
			$this->error = '该用户不存在！';
			return false;
		}
		//是否需要进行密码验证
		if (!empty($password)) {
			$encrypt = $user["encrypt"];
			//对明文密码进行加密
			$password = $UserMode->encryption($identifier, $password, $encrypt);
			if ($password != $user['password']) {
				$this->error = '用户密码错误！';
				//密码错误
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
	 * 获取上传头像FLASH代码
	 * @param type $uid 用户ID
	 * @param type $type 类型
	 * @param type $returnhtml
	 * @return string 头像代码
	 */
	public function getUploadPhotosHtml($uid, $type = 'virtual', $returnhtml = 1) {
		//验证
		$auth_data = urlencode(\Libs\Util\Encrypt::authcode($uid, ''));
		$upurl = CONFIG_SITEURL_MODEL . 'index.php?g=Member&m=Index&a=uploadavatar&auth_data=' . $auth_data;
		$html = '<script type="text/javascript">
swfobject.addDomLoadEvent(function () {
    var swfFullAvatar = new fullAvatarEditor("myContent", {
        id: \'swf\',
        upload_url: \'' . $upurl . '\',
        avatar_sizes:"90*90|45*45|22*22|15*15",
        avatar_scale:2,
        avatar_sizes_desc:"180*180像素|90*90像素|45*45像素|30*30像素",
        src_upload: 1
    }, fullAvatarCallback);
});
</script>';
		return $html;
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
	public function userEdit($username, $oldpw, $newpw = '', $email = '', $ignoreoldpw = 0, $data = array()) {
		$model = D("Member/Member");
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
	 * 检查用户名
	 * @param type $username 用户名
	 * @return boolean|int
	 */
	public function userCheckUsername($username) {
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		if (!preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $username)) {
			$find = M("Member")->where(array("username" => $username))->count();
			if ($find) {
				$this->error = '该用户名已经存在！';
				return false;
			}
			return true;
		}
		$this->error = '用户名不合法！';
		return false;
	}

	/**
	 * 检查 Email 地址
	 * @param type $email 邮箱地址
	 * @return boolean
	 */
	public function userCheckeMail($email) {
		if (strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) {
			$find = M('Member')->where(array("email" => $email))->count();
			if ($find) {
				$this->error = '该 Email 已经被注册！';
				return false;
			}
			return true;
		}
		$this->error = 'Email 格式有误！';
		return false;
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
		$Member = D("Member/Member");
		$encrypt = genRandomString(6);
		$password = $Member->encryption(0, $password, $encrypt);
		$data = array(
			"username" => $username,
			"password" => $password,
			"email" => $email,
			"encrypt" => $encrypt,
			"amount" => 0,
		);
		$userid = $Member->add($data);
		if ($userid) {
			return $userid;
		}
		$this->error = $Member->getError() ?: '注册失败！';
		return false;
	}

	/**
	 * 获取用户头像地址
	 * @param type $uid 用户UID
	 * @param type $format 头像规格
	 * @param type $dbs 是否查库，不是有猜地址的方式获取
	 * @return type
	 */
	public function getUserAvatar($uid, $format = 90, $dbs = false) {
		$config = cache('Config');
		//该参数为true时，表示使用查询数据库的方式，取得完整的头像地址。
		//比如QQ登录，使用QQ头像，此时可以使用该种方式
		if ($dbs) {
			$user_getavatar_cache = S("user_getavatar_{$uid}");
			if ($user_getavatar_cache) {
				return $user_getavatar_cache;
			} else {
				$Member = M('Member');
				$userpic = $Member->where(array("userid" => $uid))->getField('userpic');
				if ($userpic) {
					S("user_getavatar_$uid", $userpic, 3600);
				} else {
					$userpic = "{$config['siteurl']}statics/images/member/nophoto.gif";
				}
				return $userpic;
			}
		}
		//头像规格
		$avatar = array(
			180 => "180x180.jpg",
			90 => "90x90.jpg",
			45 => "45x45.jpg",
			30 => "30x30.jpg",
		);
		$format = in_array($format, $avatar) ? $format : 90;
		$userpic = $this->getAvatarPath($uid) . "{$uid}_" . $avatar[$format];
		if ($userpic) {
			$picurl = $config['sitefileurl'] . $userpic;
		} else {
			$picurl = "{$config['siteurl']}statics/images/member/nophoto.gif";
		}
		return $picurl;
	}

	/**
	 * 删除用户
	 * @param type $uid 用户UID
	 * @return boolean
	 */
	public function userDelete($uid) {
		$modelid = M("Member")->where(array("userid" => $uid))->getField("modelid");
		if (empty($modelid)) {
			$this->error = '该会员不存在！';
			return false;
		}
		$Model_Member = cache("Model_Member");
		$tablename = ucwords($Model_Member[$modelid]['tablename']);
		//删除本地用户数据开始
		if (M("Member")->where(array("userid" => $uid))->delete() !== flase) {
			M($tablename)->where(array("userid" => $uid))->delete();
			//删除connect
			M("Connect")->where(array("uid" => $uid))->delete();
			return true;
		}
		$this->error = '删除会员失败！';
		return false;
	}

	/**
	 * 删除用户头像
	 * @param type $uid 用户名UID
	 * @return boolean
	 */
	public function userDeleteAvatar($uid) {
		$dr = C("UPLOADFILEPATH") . "avatar/{$uid}/";
		if (service('Attachment')->delDir($dr)) {
			M("Member")->where(array("userid" => $uid))->save(array("userpic" => ""));
			return true;
		} else {
			$this->error = '头像删除失败！';
			return false;
		}
	}

}
