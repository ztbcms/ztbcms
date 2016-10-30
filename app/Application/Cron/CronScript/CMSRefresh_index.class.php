<?php

// +----------------------------------------------------------------------
// | 计划任务 - 刷新首页
// +----------------------------------------------------------------------

namespace Cron\CronScript;

//指定内容模块生成，没有指定默认使用GROUP_NAME
defined('GROUP_MODULE') or define('GROUP_MODULE', 'Content');

class CMSRefresh_index {

	//任务主体
	public function run($cronId) {
		CMS()->Html->index();
	}

}
