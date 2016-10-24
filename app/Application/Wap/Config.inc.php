<?php

// +----------------------------------------------------------------------
// |  3G手机版配置
// +----------------------------------------------------------------------
return array(
	//模块名称
	'modulename' => 'WAP手机版',
	//图标
	'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
	//模块简介
	'introduce' => 'WAP手机版！',
	//模块介绍地址
	'address' => 'http://www.ztbcms.cn',
	//模块作者
	'author' => 'ztbcms',
	//作者地址
	'authorsite' => 'http://www.ztbcms.cn',
	//作者邮箱
	'authoremail' => 'admin@ztbcms.cn',
	//版本号，请不要带除数字外的其他字符
	'version' => '1.0.2.0',
	//适配最低版本，
	'adaptation' => '3.0.0.0',
	//签名
	'sign' => '4B7B06DA1101821D6AAE4B51BC96E6AF',
	//依赖模块
	'depend' => array('Content'),
	//行为注册
	'tags' => array(
		'app_begin' => array(
			'title' => '应用开始标签位',
			'remark' => '应用开始标签位',
			'type' => 1,
			'phpfile:WapBehavior|module:Wap',
		),
	),
	//缓存，格式：缓存key=>array('module','model','action')
	'cache' => array(),
);
