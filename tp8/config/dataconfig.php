<?php

return [
	/* 数据库设置 */
	'DB_TYPE' => env('database.type', 'mysql'), // 数据库类型
	'DB_HOST' => env('database.hostname', '127.0.0.1'), // 服务器地址
	'DB_NAME' => env('database.database', ''), // 数据库名
	'DB_USER' => env('database.username', 'root'), // 用户名
	'DB_PWD' => env('database.password', ''), // 密码
	'DB_PORT' => env('database.hostport', '3306'), // 端口
	'DB_PREFIX' => env('database.prefix', 'ztb_'), // 数据库表前缀
	'DB_DEBUG' => env('app_debug', false),
    'DB_CHARSET' => env('database.charset', 'utf8mb4'), //字符集

	/* 站点安全设置 */
	"AUTHCODE" => env('security.authcode', 'ztbcms_auth_key'), //密钥

	/* Cookie设置 */
	"COOKIE_PREFIX" => env('security.cookie_prefix', 'ztb_'), //Cookie前缀

	/* 数据缓存设置 */
	'DATA_CACHE_PREFIX' => env('security.cache_prefix', 'ztb_'), // 缓存前缀

    # Redis 配置
    'REDIS_HOST' => env('redis.host', ''),
    'REDIS_PORT' => env('redis.port', '6379'),
];
