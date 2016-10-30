<?php

// +----------------------------------------------------------------------
// | 计划任务卸载脚本
// +----------------------------------------------------------------------

namespace Cron\Uninstall;

use Libs\System\UninstallBase;

class Uninstall extends UninstallBase {

	//End
	public function end() {
		//移除Cron目录
//		CMS()->Dir->delDir(PROJECT_PATH . 'Cron/');
		return true;
	}

}
