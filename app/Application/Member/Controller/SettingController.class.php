<?php

// +----------------------------------------------------------------------
// | 会员中心后台管理
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\AdminBase;

class SettingController extends AdminBase {

	//会员用户组缓存
	protected $groupCache = array();
	//会员模型
	protected $groupsModel = array();
	//会员数据模型
	protected $member = NULL;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->groupCache = cache('Member_group');
		$this->groupsModel = cache('Model_Member');
		$this->member = D('Member/Member');
	}

	//会员模块设置
	public function setting() {
		if (IS_POST) {
			$setting = $_POST['setting'];
			$data['setting'] = serialize($setting);
			$Module = M('Module');
			if ($Module->create()) {
				if ($Module->where(array("module" => "Member"))->save($data) !== false) {
					$this->member->member_cache();
					$this->success("更新成功！", U("Setting/setting"));
				} else {
					$this->error("更新失败！", U("Setting/setting"));
				}
			} else {
				$this->error($Module->getError());
			}
		} else {
			//取得会员接口信息
			$Dir = new \Dir(PROJECT_PATH . 'Libs/Driver/Passport/');
			$Interface = array(
				'' => '请选择帐号通行证接口（默认本地）',
			);
			$lan = array(
				'Local' => '本地用户通行证',
				'Ucenter' => 'Ucenter用户通行证',
			);
			foreach ($Dir->toArray() as $r) {
				$neme = str_replace(array('Passport', '.class.php'), '', $r['filename']);
				$Interface[$neme] = $lan[$neme] ? $lan[$neme] : $neme;
			}
			$setting = M("Module")->where(array("module" => "Member"))->getField("setting");
			foreach ($this->groupCache as $g) {
				if (in_array($g['groupid'], array(8, 1, 7))) {
					continue;
				}
				$groupCache[$g['groupid']] = $g['name'];
			}
			foreach ($this->groupsModel as $m) {
				$groupsModel[$m['modelid']] = $m['name'];
			}
			$this->assign('groupCache', $groupCache);
			$this->assign('groupsModel', $groupsModel);
			$this->assign("setting", unserialize($setting));
			$this->assign("Interface", $Interface);
			$this->display();
		}
	}

	//Ucenter 测试数据库链接
	public function myqsl_test() {
		$host = isset($_GET['host']) && trim($_GET['host']) ? trim($_GET['host']) : exit('0');
		$password = isset($_GET['password']) && trim($_GET['password']) ? trim($_GET['password']) : exit('0');
		$username = isset($_GET['username']) && trim($_GET['username']) ? trim($_GET['username']) : exit('0');
		if (@mysql_connect($host, $username, $password)) {
			exit('1');
		} else {
			exit('0');
		}
	}

}
