<?php

// +----------------------------------------------------------------------
// | 模块管理模型
// +----------------------------------------------------------------------

namespace Common\Model;

use Libs\System\Module;

class ModuleModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('module', 'require', '模块目录名称不能为空！', 1, 'regex', 3),
		array('sign', 'require', '模块签名不能为空！', 1, 'regex', 3),
		//array('module', 'isInstall', '该模块已经安装过了！', 0, 'callback', 1),
		array('modulename', 'require', '模块名称不能为空！', 1, 'regex', 3),
		array('version', 'require', '模块版本号不能为空！', 1, 'regex', 3),
	);
	//自动完成
	protected $_auto = array(
		array('iscore', 0),
		array('disabled', 1),
		array('installtime', 'time', 1, 'function'),
		array('updatetime', 'time', 1, 'function'),
	);

	/**
	 * 检查是否已经安装
	 * @param type $moduleName
	 * @return boolean 有安装false，没安装true
	 */
	public function isInstall($moduleName) {
		if (is_object(CMS()->Module)) {
			return CMS()->Module->isInstall($moduleName) ? false : true;
		} else {
			return \Libs\System\Module::getInstance()->isInstall($moduleName) ? false : true;
		}
	}

	/**
	 * 模块状态转换
	 * @param type $module 模块
	 * @return boolean
	 */
	public function disabled($module) {
		if (empty($module)) {
			$this->error = '请选模块！';
			return false;
		}
		//取得该模块数据库中记录的安装信息
		$info = $this->where(array('module' => $module))->find();
		if (empty($info)) {
			$this->error = '该模块未安装，无需进行此操作！';
			return false;
		}
		if ($info['iscore']) {
			$this->error = '内置模块，不能禁用！';
			return false;
		}
		$disabled = $info['disabled'] ? 0 : 1;
		if (false !== $this->where(array('module' => $module))->save(array('disabled' => $disabled))) {
			//更新缓存
			cache('Module', NULL);
			return true;
		} else {
			$this->error = '状态转换失败！';
			return false;
		}
	}

	/**
	 * 更新缓存
	 * @return type
	 */
	public function module_cache() {
		$data = $this->select();
		if (empty($data)) {
			return false;
		}
		$module = array();
		foreach ($data as $v) {
			$module[$v['module']] = $v;
		}
		cache('Module', $module);
		return $module;
	}

}
