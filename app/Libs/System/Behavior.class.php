<?php

// +----------------------------------------------------------------------
// | 行为抽象类
// +----------------------------------------------------------------------

namespace Libs\System;

abstract class Behavior {

	// 使用的模板引擎 每个行为可以单独配置不受系统影响
	protected $template = '';

	/**
	 * 模板输出变量
	 * @var tVar
	 * @access protected
	 */
	protected $tVar = array();

	/**
	 * 模板变量赋值
	 * @access public
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function assign($name, $value = '') {
		if (is_array($name)) {
			$this->tVar = array_merge($this->tVar, $name);
		} else {
			$this->tVar[$name] = $value;
		}
	}

	/**
	 * 渲染模板输出 供render方法内部调用
	 * @access public
	 * @param string $templateFile  模板文件
	 * @return string
	 */
	protected function renderFile($templateFile = '') {
		if (!file_exists_case($templateFile)) {
			// 自动定位模板文件
			$className = explode('\\', get_called_class());
			//行为名
			$behaviorName = str_replace('Behavior', '', end($className));
			//获取模板文件名称
			$filename = empty($templateFile) ? $behaviorName : $templateFile;
			$moduleName = $className[0];
			$templateFile = APP_PATH . $moduleName . '/Behavior/' . $behaviorName . '/' . $filename . C('TMPL_TEMPLATE_SUFFIX');
			if (!file_exists_case($templateFile)) {
				E(L('_TEMPLATE_NOT_EXIST_') . '[' . $templateFile . ']');
			}

		}
		$tpl = \Think\Think::instance('Think\View');
		$tpl->assign($this->tVar);
		return $tpl->fetch($templateFile);
	}

	/**
	 * 模板显示 调用内置的模板引擎显示方法，
	 * @access protected
	 * @param string $templateFile 指定要调用的模板文件
	 * 默认为空 由系统自动定位模板文件
	 * @return void
	 */
	protected function display($templateFile = '') {
		echo $this->renderFile($templateFile);
	}

	/**
	 * 执行行为 run方法是Behavior唯一的接口
	 * @access public
	 * @param mixed $params  行为参数
	 * @return void
	 */
	abstract public function run(&$params);
}
