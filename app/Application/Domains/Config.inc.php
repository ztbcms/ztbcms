<?php

// +----------------------------------------------------------------------
// | 模块配置
// +----------------------------------------------------------------------

return array(
	//模块名称
	'modulename' => '域名绑定',
	//图标
	'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
	//模块简介
	'introduce' => '提供对模块进行二级域名绑定！',
	//模块介绍地址
	'address' => 'http://www.ztbcms.com',
	//模块作者
	'author' => 'ztbcms',
	//作者地址
	'authorsite' => 'http://www.ztbcms.com',
	//作者邮箱
	'authoremail' => 'admin@ztbcms.com',
	//版本号，请不要带除数字外的其他字符
	'version' => '1.0.0',
	//适配最低版本，
	'adaptation' => '3.0.0.0',
	//签名
	'sign' => '01d1cc6e0b01e5b5a1bc114ea8f2b3e9',
	//依赖模块
	'depend' => array(),
	//行为标签
	'tags' => array(
		'app_init' => array(
			'type' => 1,
			'phpfile:Domains|module:Domains',
		),
	),
	//缓存，格式：缓存key=>array('module','model','action')
	'cache' => array(
		'Domains_list' => array(
			'name' => '域名绑定模块',
			'model' => 'Domains',
			'action' => 'domains_cache',
		),
		'Module_Domains_list' => array(
			'name' => '模块绑定域名',
			'model' => 'Domains',
			'action' => 'domains_domainslist',
		),
	),
);
