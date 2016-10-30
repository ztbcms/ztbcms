<?php

// +----------------------------------------------------------------------
// | 计划任务 - 刷新静态栏目页
// +----------------------------------------------------------------------

namespace Cron\CronScript;

//指定内容模块生成，没有指定默认使用GROUP_NAME
use Cron\Base\Cron;

defined('GROUP_MODULE') or define('GROUP_MODULE', 'Content');

class Refresh_category extends Cron {

	//任务主体
	public function run($cronId) {
		$r = M('Cron')->where(array('cron_id' => $cronId))->find();
		if ($r) {
			$catid = explode(",", $r['data']);
			if (is_array($catid)) {
				foreach ($catid as $cid) {
					CMS()->Html->category($cid);
				}
			}
		}
	}

}
