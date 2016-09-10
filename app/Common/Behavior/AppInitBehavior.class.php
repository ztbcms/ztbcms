<?php

namespace Common\Behavior;

use Libs\System\Cache;

defined('THINK_PATH') or exit();

class AppInitBehavior {

	//执行入口
	public function run(&$param) {
		// 注册AUTOLOAD方法
		spl_autoload_register('Common\Behavior\AppInitBehavior::autoload');
		//检查是否安装
		if ($this->richterInstall() == false) {
			redirect('./install.php');
			return false;
		}
		//站点初始化
		$this->initialization();
	}

	/**
	 * 是否安装检测
	 */
	private function richterInstall() {
		//日志目录
		if (!is_dir(LOG_PATH)) {
			mkdir(LOG_PATH);
		}
		$dbHost = C('DB_HOST');
		if (empty($dbHost) && !defined('INSTALL')) {
			return false;
		}
		return true;
	}

	//初始化
	private function initialization() {
		if (!C('DB_PWD')) {
			return true;
		}
		//产品版本号
		define("CMS_VERSION", C("CMS_VERSION"));
		//产品流水号
		define("CMS_BUILD", C("CMS_BUILD"));
		//产品名称
		define("CMS_APPNAME", C("CMS_APPNAME"));
		//MODULE_ALLOW_LIST配置
		$moduleList = cache('Module');
		$moduleAllowList = array('Admin', 'Api', 'Attachment', 'Content', 'Install', 'Template');
		if (!empty($moduleList)) {
			foreach ($moduleList as $rs) {
				if ($rs['disabled']) {
					$moduleAllowList[] = $rs['module'];
				}
			}
		}
		C('MODULE_ALLOW_LIST', $moduleAllowList);
	}

	/**
	 * 类库自动加载
	 * @param string $class 对象类名
	 * @return void
	 */
	static public function autoload($class) {
		//内容模型content_xx.class.php类自动加载
		if (in_array($class, array('content_form', 'content_input', 'content_output', 'content_update', 'content_delete'))) {
			\Content\Model\ContentModel::classGenerate();
			require_cache(RUNTIME_PATH . "{$class}.class.php");
			return;
		}
	}

}
