<?php

// +----------------------------------------------------------------------
// | 模块卸载脚本抽象类
// +----------------------------------------------------------------------

namespace Libs\System;

abstract class UninstallBase {

	//错误信息
	protected $error = '';

	/**
	 * 卸载开始执行
	 * @return boolean
	 */
	public function run() {
		return true;
	}

	/**
	 * 卸载完回调
	 * @return boolean
	 */
	public function end() {
		return true;
	}

	/**
	 * 获取错误
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

}
