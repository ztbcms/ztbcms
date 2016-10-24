<?php

// +----------------------------------------------------------------------
// | 交易记录模块配置
// +----------------------------------------------------------------------

return array(
	//模块名称
	'modulename' => '交易记录管理模块',
	//图标
	'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
	//模块简介
	'introduce' => '交易记录管理，统一交易明细',
	//模块介绍地址
	'address' => 'http://ztbcms.com',
	//模块作者
	'author' => 'ZtbCMS-zhlhuang',
	//作者地址
	'authorsite' => 'http://github.com/zhlhuang',
	//作者邮箱
	'authoremail' => 'zhlhuang888@foxmail.com',
	//版本号，请不要带除数字外的其他字符
	'version' => '1.0.0',
	//适配最低ZtbFCMS版本，
	'adaptation' => '3.0.0.0',
	//签名
	'sign' => 'b19cc279ed484c13c96c2f7142e2f437',
	//依赖模块
	'depend' => array(
		'Member'
	),
	//行为注册
	'tags' => array(),
	//缓存，格式：缓存key=>array('module','model','action')
	'cache' => array(),
);
