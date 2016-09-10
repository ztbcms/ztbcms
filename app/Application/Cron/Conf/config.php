<?php

// +----------------------------------------------------------------------
// | 配置
// +----------------------------------------------------------------------

return array(
	'AUTOLOAD_NAMESPACE' => array_merge(C('AUTOLOAD_NAMESPACE'), array(
		'CronScript' => PROJECT_PATH . 'Cron/',
	)),
);
