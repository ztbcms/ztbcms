<?php

// +----------------------------------------------------------------------
// | 会员模型
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class MemberModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！'),
		array('password', 'require', '密码不能为空！', 0, 'regex', 1),
		array('email', 'email', '邮箱地址有误！'),
		array('username', '', '帐号名称已经存在！', 0, 'unique', 1),
		array('nickname', '', '该昵称已经存在！', 0, 'unique', 1),
		array('pwdconfirm', 'password', '两次输入的密码不一样！', 0, 'confirm'),
		//callback
		array('username', 'checkName', '用户名已经存在或不合法！', 0, 'callback', 1),
		array('email', 'checkEmail', '邮箱已经存在或者不合法！', 0, 'callback', 1),
		array('groupid', 'checkGroupid', '该会员组不存在！', 0, 'callback'),
		array('modelid', 'checkModelid', '该会员模型不存在！', 0, 'callback'),
	);
	//array(填充字段,填充内容,[填充条件,附加规则])
	protected $_auto = array(
		array('regdate', 'time', 1, 'function'),
		array('regip', 'get_client_ip', 1, 'function'),
	);

	/**
	 * 根据错误代码返回错误提示
	 * @param string $errorCodes 错误代码
	 * @return string
	 */
	public function getErrorMesg($errorCodes) {
		switch ($errorCodes) {
			case -1:
				$error = '用户名不合法';
				break;
			case -2:
				$error = '包含不允许注册的词语';
				break;
			case -3:
				$error = '用户名已经存在';
				break;
			case -4:
				$error = 'Email 格式有误';
				break;
			case -5:
				$error = 'Email 不允许注册';
				break;
			case -6:
				$error = '该 Email 已经被注册';
				break;
			default:
				$error = '操作出现错误';
				break;
		}

		return $error;
	}

	//检查用户名
	public function checkName($name) {
		if (service("Passport")->userCheckUsername($name)) {
			return true;
		}
		return false;
	}

	//检查邮箱
	public function checkEmail($email) {
		if (service("Passport")->userCheckeMail($email)) {
			return true;
		}
		return false;
	}

	//检查会员组
	public function checkGroupid($groupid) {
		$Member_group = cache('Member_group');
		if (!$Member_group[$groupid]) {
			return false;
		}
		return true;
	}

	//检查会员模型
	public function checkModelid($modelid) {
		$Model_Member = cache("Model_Member");
		if (!$Model_Member[$modelid]) {
			return false;
		}
		return true;
	}

	/**
	 * 对明文密码，进行加密，返回加密后的密码
	 * @param string $identifier 为数字时，表示uid，其他为用户名
	 * @param string $pass 明文密码，不能为空
	 * @return string 返回加密后的密码
	 */
	public function encryption($identifier, $pass, $verify = "") {
		$v = array();
		if (is_numeric($identifier)) {
			$v["id"] = $identifier;
		} else {
			$v["username"] = $identifier;
		}
		$pass = md5($pass . md5($verify));
		return $pass;
	}

	/**
	 * 根据标识修改对应用户密码
	 * @param string $identifier
	 * @param string $password
	 * @return boolean
	 */
	public function ChangePassword($identifier, $password) {
		if (empty($identifier) || empty($password)) {
			return false;
		}
		$term = array();
		if (is_numeric($identifier)) {
			$term['userid'] = $identifier;
		} else {
			$term['username'] = $identifier;
		}
		$verify = $this->where($term)->getField('verify');

		$data['password'] = $this->encryption($identifier, $password, $verify);

		$up = $this->where($term)->save($data);
		if ($up) {
			return true;
		}
		return false;
	}

	/**
	 * 根据积分算出用户组
	 * @param $point int 积分数
     * @return int
	 */
	public function get_usergroup_bypoint($point = 0) {
		$groupid = 2;
		if (empty($point)) {
			$member_setting = cache("Member_Config");
			//新会员默认点数
			$point = $member_setting['defualtpoint'] ? $member_setting['defualtpoint'] : 0;
		}
		//获取会有组缓存
		$grouplist = cache("Member_group");
		foreach ($grouplist as $k => $v) {
			$grouppointlist[$k] = $v['point'];
		}
		//对数组进行逆向排序
		arsort($grouppointlist);
		//如果超出用户组积分设置则为积分最高的用户组
		if ($point > max($grouppointlist)) {
			$groupid = key($grouppointlist);
		} else {
			foreach ($grouppointlist as $k => $v) {
				if ($point >= $v) {
					$groupid = $tmp_k;
					break;
				}
				$tmp_k = $k;
			}
		}
		return $groupid;
	}

	/**
	 * 取得本应用中的用户资料
	 * @param string $identifier
	 * @param string $field
	 * @return boolean|array
	 */
	public function getUserInfo($identifier, $field = '*') {
		if (empty($identifier)) {
			return false;
		}
		$where = array();
		if (is_numeric($identifier) && gettype($identifier) == "integer") {
			$where['userid'] = $identifier;
		} else {
			$where['username'] = $identifier;
		}
		$userInfo = $this->where($where)->field($field)->find();
		if (empty($userInfo)) {
			return false;
		}
		return $userInfo;
	}

	/**
	 * 取得用户配置
	 * @param string $userid 用户UID
	 * @return boolean
	 */
	public function getUserConfig($userid) {
		if (empty($userid) || $userid < 1) {
			$this->error = '请指定用户ID！';
			return false;
		}
		//检查缓存是否存在
		$userConfig = S('user_config_' . $userid);
		if (!empty($userConfig)) {
			return $userConfig;
		}
		//取得用户信息
		$userInfo = service("Passport")->getLocalUser((int) $userid);
		if (empty($userInfo)) {
			$this->error = '该用户不存在！';
			return false;
		}
		//会员组缓存
		$memberGroupCache = cache('Member_group');
		//取得该用户所属会有组信息
		$groupInfo = $memberGroupCache[$userInfo['groupid']];
		if (empty($groupInfo)) {
			$this->error = '获取不到该用户所属会有组信息！';
			return false;
		}
		$getUserConfig = array();
		$getUserConfig = array_merge($getUserConfig, $groupInfo);
		$getUserConfig['heat'] = $userInfo['heat'];
		$getUserConfig['theme'] = $userInfo['theme'];
		$getUserConfig['praise'] = $userInfo['praise'];
		$getUserConfig['attention'] = $userInfo['attention'];
		$getUserConfig['fans'] = $userInfo['fans'];
		$getUserConfig['share'] = $userInfo['share'];
		$getUserConfig['nickname'] = $userInfo['nickname'];
		$getUserConfig['userpic'] = $userInfo['userpic'];
		$getUserConfig['groupid'] = $userInfo['groupid'];
		$getUserConfig['modelid'] = $userInfo['modelid'];
		$getUserConfig['message'] = $userInfo['message'];
		$getUserConfig['vip'] = $userInfo['vip'];
		$getUserConfig['overduedate'] = $userInfo['overduedate'];
		//进行缓存
		S('user_config_' . $userid, $getUserConfig, 3600);
		return $getUserConfig;
	}

	//会员配置缓存
	public function member_cache() {
		$data = unserialize(M('Module')->where(array('module' => 'Member'))->getField('setting'));
		cache("Member_Config", $data);
		$this->member_model_cahce();
		return $data;
	}

	//会员模型缓存
	public function member_model_cahce() {
		$data = D('Content/Model')->getModelAll(2);
		cache("Model_Member", $data);
		return $data;
	}

}
