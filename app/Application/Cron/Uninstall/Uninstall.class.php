<?php

// +----------------------------------------------------------------------
// | 计划任务卸载脚本
// +----------------------------------------------------------------------

namespace Cron\Uninstall;

use Libs\System\UninstallBase;

class Uninstall extends UninstallBase {

	//End
	public function end() {
		return true;
	}

}
