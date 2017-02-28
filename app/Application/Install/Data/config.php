<?php

// +----------------------------------------------------------------------
// | ZTBCMS
// +----------------------------------------------------------------------

return array(
	/* 数据库设置 */
	'DB_TYPE' => 'mysql', // 数据库类型
	'DB_HOST' => '#DB_HOST#', // 服务器地址
	'DB_NAME' => '#DB_NAME#', // 数据库名
	'DB_USER' => '#DB_USER#', // 用户名
	'DB_PWD' => '#DB_PWD#', // 密码
	'DB_PORT' => '#DB_PORT#', // 端口
	'DB_PREFIX' => '#DB_PREFIX#', // 数据库表前缀
	'DB_DEBUG' => false,

	/* 站点安全设置 */
	"AUTHCODE" => '#AUTHCODE#', //密钥

	/* Cookie设置 */
	"COOKIE_PREFIX" => '#COOKIE_PREFIX#', //Cookie前缀

	/* 数据缓存设置 */
	'DATA_CACHE_PREFIX' => '#DATA_CACHE_PREFIX#', // 缓存前缀
);
