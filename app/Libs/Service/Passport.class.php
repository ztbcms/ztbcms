<?php

// +----------------------------------------------------------------------
// | 前台会员通行证
// +----------------------------------------------------------------------

namespace Libs\Service;

class Passport extends \Libs\System\Service {

	//存储用户uid的Key
	const userUidKey = 'spf_userid';

	//参数
	protected $options = array();
	//网站配置参数
	protected $config = array();
	//错误信息
	protected $error = null;
	//当前登录会员详细信息
	static protected $userInfo = array();

	/**
	 * 连接会员系统
	 * @param string $name 服务名
	 * @param array $options 参数
	 * @return Passport
	 */
	public static function connect($name = '', $options = array()) {
		if (false == isModuleInstall('Member')) {
			return new Passport($options);
		}
		if (empty($options['type'])) {
			//网站配置
			$config = cache("Member_Config");
			if ($config['interface']) {
				$type = $config['interface'];
			} else {
				$type = 'Local';
			}
		} else {
			$type = $options['type'];
		}
		//附件存储方案
		$class = strpos($type, '\\') ? $type : 'Libs\\Driver\\Passport\\' . ucwords(strtolower($type));
		if (class_exists($class)) {
			$connect = new $class($options);
		} else {
            $connect = null;
			E("通行证驱动 {$class} 不存在！");
		}
		return $connect;
	}

	/**
	 * 魔术方法
	 * @param string $name
	 * @return null
	 */
	public function __get($name) {
		//从缓存中获取
		if (isset(self::$userInfo[$name])) {
			return self::$userInfo[$name];
		} else {
			$userInfo = $this->getInfo();
			if (!empty($userInfo)) {
				return $userInfo[$name];
			}
			return NULL;
		}
	}

	/**
	 * 获取错误信息
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 获取当前登录用户资料
	 * @return array
	 */
	public function getInfo() {
		if (empty(self::$userInfo)) {
			self::$userInfo = $this->getLocalUser($this->getCookieUid());
		}
		return !empty(self::$userInfo) ? self::$userInfo : false;
	}

	/**
	 * 获取cookie中记录的用户ID
	 * @return string 成功返回用户ID，失败返回false
	 */
	public function getCookieUid() {
		$userId = \Libs\Util\Encrypt::authcode(cookie(self::userUidKey), 'DECODE');
		return (int) $userId ?: false;
	}

	/**
	 * 获取用户信息
	 * @param string $identifier 用户/UID
	 * @param string $password 明文密码，填写表示验证密码
	 * @return array|boolean
	 */
	public function getLocalUser($identifier, $password = null) {
		return array();
	}

	/**
	 * 获取用户头像
	 * @param string $uid 用户ID
	 * @param int $format 头像规格，默认参数90，支持 180,90,45,30
	 * @param boolean $dbs 该参数为true时，表示使用查询数据库的方式，取得完整的头像地址。默认false
	 * @return string 返回头像地址
	 */
	public function getUserAvatar($uid, $format = 90, $dbs = false) {
		$config = cache('Config');
		return "{$config['siteurl']}statics/images/member/nophoto.gif";
	}

	/**
	 * 用户积分变更
	 * @param string $uid 数字为用户ID，其他为用户名
	 * @param int $integral 正数增加积分，负数扣除积分
	 * @return int 成功返回当前积分数，失败返回false，-1 表示当前积分不够扣除
	 */
	public function userIntegration($uid, $integral) {
		if (!isModuleInstall('Member')) {
			return true;
		}
		$map = array();
		if (is_numeric($uid)) {
			$map['userid'] = $uid;
		} else {
			$map['username'] = $uid;
		}
		if (empty($map)) {
			$this->error = '该用户不存在！';
			return false;
		}
		$member = D('Member/Member');
		$info = $member->where($map)->find();
		if (empty($info)) {
			$this->error = '该用户不存在！';
			return false;
		}
		$point = $info['point'] + $integral;
		if ($point < 0) {
			$this->error = '用户积分不足！';
			return false;
		}
		//计算会员组
		$groupid = $member->get_usergroup_bypoint((int) $point);
		//更新
		if (false !== $member->where($map)->save(array("point" => (int) $point, "groupid" => $groupid))) {
			return true;
		}
		$this->error = '积分扣除失败！';
		return false;
	}

	/**
	 * 检验用户是否已经登录
	 */
	public function isLogged() {
		//获取cookie中的用户id
		$uid = $this->getCookieUid();
		if (empty($uid) || $uid < 1) {
			return false;
		}
		return $uid;
	}

	/**
	 * 注册用户的登录状态 (即: 注册cookie + 注册session + 记录登录信息)
	 * @param array $user 用户相信信息 uid , username
	 * @param int $is_remeber_me 有效期
	 * @return boolean 成功返回布尔值
	 */
	public function registerLogin(array $user, $is_remeber_me = 604800) {
		$key = \Libs\Util\Encrypt::authcode((int) $user['userid'], '');
		cookie(self::userUidKey, $key, (int) $is_remeber_me);
		return true;
	}

	/**
	 * 注销登录
	 * @return boolean
	 */
	public function logoutLocal() {
		// 注销cookie
		cookie(self::userUidKey, null);
		return true;
	}

	/**
	 * 会员登录
	 * @param string $identifier 用户/UID
	 * @param string $password 明文密码，填写表示验证密码
	 * @param int $is_remember_me cookie有效期
	 * @return boolean
	 */
	public function loginLocal($identifier, $password = null, $is_remember_me = 3600) {
		return false;
	}

	/**
	 * 记录登录信息
	 * @param string $uid 用户ID
     * @return boolean
	 */
	public function recordLogin($uid) {
		return true;
	}

	/**
	 * 注册会员
	 * @param string $username 用户名
	 * @param string $password 明文密码
	 * @param string $email 邮箱
	 * @return boolean
	 */
	public function userRegister($username, $password, $email, $_data = array()) {
		return false;
	}

	/**
	 * 更新用户基本资料
	 * @param string $username 用户名
	 * @param string $oldpw 旧密码
	 * @param string $newpw 新密码，如不修改为空
	 * @param string $email Email，如不修改为空
	 * @param int $ignoreoldpw 是否忽略旧密码
	 * @param array $data 其他信息
	 * @return boolean
	 */
	public function userEdit($username, $oldpw, $newpw = '', $email = '', $ignoreoldpw = 0, $data = array()) {
		return false;
	}

	/**
	 * 删除用户
	 * @param string $uid 用户UID
	 * @return boolean
	 */
	public function userDelete($uid) {
		return true;
	}

	/**
	 * 删除用户头像
	 * @param string $uid 用户名UID
	 * @return boolean
	 */
	public function userDeleteAvatar($uid) {
		return false;
	}

	/**
	 * 检查 Email 地址
	 * @param string $email 邮箱地址
	 * @return boolean
	 */
	public function userCheckeMail($email) {
		return false;
	}

	/**
	 * 检查用户名
	 * @param string $username 用户名
	 * @return boolean|int
	 */
	public function userCheckUsername($username) {
		return false;
	}

	/**
	 * 修改头像
	 * @param string $uid 用户 ID
	 * @param string $type 头像类型
	 *                                       real:真实头像
	 *                                       virtual:(默认值) 虚拟头像
	 * @param int $returnhtml 是否返回 HTML 代码
	 *                                                     1:(默认值) 是，返回设置头像的 HTML 代码
	 *                                                     0:否，返回设置头像的 Flash 调用数组
	 * @return string 返回设置头像的 HTML 代码
	 *                array 返回设置头像的 Flash 调用数组
	 */
	public function userAvatarEdit($uid, $type = 'virtual', $returnhtml = 1) {
		return false;
	}

	/**
	 * 获取头像存储路径
	 * @param string $uid 会员UID
	 * @return string
	 */
	public function getAvatarPath($uid) {
		$uid = abs(intval($uid)); //UID取整数绝对值
		$uid = sprintf("%09d", $uid); //前边加0补齐9位，例如UID为31的用户变成 000000031
		$dir1 = substr($uid, 0, 3); //取左边3位，即 000
		$dir2 = substr($uid, 3, 2); //取4-5位，即00
		$dir3 = substr($uid, 5, 2); //取6-7位，即00
		return 'avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
	}

}
