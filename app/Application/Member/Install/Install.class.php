<?php

// +----------------------------------------------------------------------
// | 会员中心模块安装脚本
// +----------------------------------------------------------------------

namespace Member\Install;

use Libs\System\InstallBase;

class Install extends InstallBase {

	//模块地址
	private $path = NULL;

	public function __construct() {
		$this->path = APP_PATH . 'Member/';
	}

	//安装前进行处理
	public function run() {
		//通行证目录权限检测
		if ($this->chechmod(PROJECT_PATH . 'Libs/Driver/') == false) {
			$this->error = '目录 ' . PROJECT_PATH . 'Libs/Driver/' . ' 没有可写权限！';
			return false;
		}
		//Api目录权限检查
		if ($this->chechmod(APP_PATH . 'Api/Controller/', 0777, true) == false) {
			$this->error = '目录 ' . APP_PATH . 'Api/Controller/' . ' 没有可写权限！';
			return false;
		}
		if (file_exists(APP_PATH . 'Api/Controller/UserController.class.php')) {
			$this->error = '目录 ' . APP_PATH . 'Api/Controller/UserController.class.php' . ' 已经存在，请删除后再进行安装！';
			return false;
		}
		//创建uc api目录
		if (file_exists(SITE_PATH . 'api/')) {
			$this->error = '目录 ' . SITE_PATH . 'api/' . ' 已经存在，请删除后再进行安装！';
			return false;
		}
		if (mkdir(SITE_PATH . 'api/', 0777, true) == false) {
			$this->error = '目录 ' . SITE_PATH . 'api/' . ' 创建失败！';
			return false;
		}
		//检查是否有添加 view_admin_top_menu 行为，没有增加
		if (D('Common/Behavior')->where(array('name' => 'view_admin_top_menu'))->count() < 1) {
			C('TOKEN_ON', false);
			D('Common/Behavior')->addBehavior(array(
				'name' => 'view_admin_top_menu',
				'title' => '后台框架首页右上角菜单',
				'type' => 2,
				'remark' => '后台框架首页右上角菜单',
			));
		}
		return true;
	}

	//基本安装结束后的回调
	public function end() {
		//移动通行证到lib目录下
		CMS()->Dir->copyDir($this->path . "Install/Driver/Passport/", PROJECT_PATH . 'Libs/Driver/Passport/');
		CMS()->Dir->copyDir($this->path . "Install/Api/", SITE_PATH . 'api/');
		@copy($this->path . "Install/UserController.class.php", APP_PATH . 'Api/Controller/UserController.class.php');
		//填充默认配置
		$Setting = include $this->path . 'Install/Setting.inc.php';
		if (!empty($Setting) && is_array($Setting)) {
			M("Module")->where(array("module" => "Member"))->save(array('setting' => serialize($Setting)));
		}
		return true;
	}

	/**
	 * 检查对应目录是否有相应的权限
	 * @param type $path 目录地址
	 * @return boolean
	 */
	protected function chechmod($path) {
		//检查模板文件夹是否有可写权限 TEMPLATE_PATH
		$tfile = "_test.txt";
		$fp = @fopen($path . $tfile, "w");
		if (!$fp) {
			return false;
		}
		fclose($fp);
		$rs = @unlink($path . $tfile);
		if (!$rs) {
			return false;
		}
		return true;
	}

}
