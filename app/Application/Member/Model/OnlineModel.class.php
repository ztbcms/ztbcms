<?php

// +----------------------------------------------------------------------
// | 在线用户
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class OnlineModel extends Model {

	//数据表
	protected $tableName = 'member_online';
	//当前时间
	protected $time = 0;
	//当前登录状态下，最后操作时间
	protected $lastTime = 0;
	//更新周期
	protected $upTime = 15;
	//是否进行时间间隔判断
	protected $isWriteTimeInterval = false;

	protected function _initialize() {
		parent::_initialize();
		$this->time = time();
		$this->upTime = $this->upTime * 60;
	}

	/**
	 * 取得在线用户列表
	 * @param type $page 当前分页
	 * @param type $limit 每次显示多少
	 * @return type
	 */
	public function getOnlineUserList($page = 1, $limit = 10) {
		$count = $this->alias('as O')->join(' INNER JOIN `' . C('DB_PREFIX') . 'member` as M ON O.userid = M.userid')->count('M.userid');
		$page = page($count, $limit, $page);
		$cache = $this->alias('as O')
			->join(' INNER JOIN `' . C('DB_PREFIX') . 'member` as M ON O.userid = M.userid')
			->field('M.*')
			->limit($page->firstRow . ',' . $page->listRows)
			->order(array("O.lasttime" => "DESC"))
			->select();
		return array(
			'count' => $count,
			'data' => $cache,
			'Page' => $page->show('Admin'),
		);
	}

	/**
	 * 注册用户在线状态
	 * @param type $userid 用户ID
	 * @return boolean
	 */
	public function registerOnlineStatus($userid = 0) {
		if (empty($userid)) {
			//取得当前登录用户id
			$userid = service('Passport')->getCookieUid();
			if (empty($userid)) {
				$this->error = '用户ID不能为空！';
				return false;
			}
		}
		//检查是否已经有存在在线记录
		if ($this->getLastActiveTime($userid)) {
			return true;
		}
		//取得用户信息
		$userInfo = D('Member')->getUserInfo((int) $userid, 'userid,username');
		if (empty($userInfo)) {
			$this->error = '用户不存在！';
			return false;
		}
		//添加在线记录
		$id = $this->add(array(
			'userid' => $userid,
			'username' => $userInfo['username'],
			'lasttime' => $this->time,
		));
		if ($id) {
			session('lasttime', $this->time);
			//剔除离线用户
			$this->timeExpiredOnlineDel();
			return $id;
		} else {
			$this->error = '在线状态注册失败！';
			return false;
		}
	}

	/**
	 * 维护用户在线状态
	 * @param type $userid 用户ID
	 * @return boolean
	 */
	public function maintainOnlineStatus($userid = 0) {
		if (empty($userid)) {
			//取得当前登录用户id
			$userid = service('Passport')->getCookieUid();
			if (empty($userid)) {
				$this->error = '用户ID不能为空！';
				return false;
			}
		}
		$onlineId = $this->where(array('userid' => $userid))->getField('lasttime');
		if (empty($onlineId)) {
			return $this->registerOnlineStatus($userid);
		} else {
			//取得session中的lasttime
			$sessionLasttime = (int) session('lasttime');
			if ($this->time - $sessionLasttime < $this->upTime) {
				return true;
			}
			//剔除离线用户
			$this->timeExpiredOnlineDel();
			return $this->where(array('userid' => $userid))->save(array('lasttime' => $this->time));
		}
	}

	/**
	 * 注销在线状态
	 * @param type $userid 用户ID
	 * @return boolean
	 */
	public function onlineDel($userid = 0) {
		if (empty($userid)) {
			//取得当前登录用户id
			$userid = service('Passport')->getCookieUid();
			if (empty($userid)) {
				$this->error = '用户ID不能为空！';
				return false;
			}
		}
		$this->where(array('userid' => $userid))->delete();
		session('lasttime', NULL);
		return true;
	}

	/**
	 * 清楚离线记录
	 */
	public function timeExpiredOnlineDel() {
		$this->where(array('lasttime' => array('LT', $this->time - $this->upTime)))->delete();
	}

	/**
	 * 返回用户最后活动时间戳
	 * @param int $userid 用户ID
	 * @return boolean|int
	 */
	public function getLastActiveTime($userid = 0) {
		static $getLastActiveTime = array();
		if (empty($userid)) {
			//取得当前登录用户id
			$userid = service('Passport')->getCookieUid();
			if (empty($userid)) {
				$this->error = '用户ID不能为空！';
				return false;
			}
		}
		if (isset($getLastActiveTime[$userid])) {
			return $getLastActiveTime[$userid];
		}
		$getLastActiveTime[$userid] = $this->where(array('userid' => (int) $userid))->getField('lasttime');
		return $getLastActiveTime[$userid];
	}

}
