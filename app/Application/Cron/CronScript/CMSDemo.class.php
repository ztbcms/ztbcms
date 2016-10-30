<?php

// +----------------------------------------------------------------------
// | 计划任务 - 示例脚本
// +----------------------------------------------------------------------

namespace Cron\CronScript;

class CMSDemo {

	//任务主体
	public function run($cronId) {
		\Think\Log::record("我执行了计划任务事例 CMSDemo.class.php！");
	}

}
