<?php

// +----------------------------------------------------------------------
// | 搜索模块配置
// +----------------------------------------------------------------------

return array(
	//模块名称
	'modulename' => '表单',
	//图标
	'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
	//模块简介
	'introduce' => '自定义信息表单，用于收集个例信息',
	//模块介绍地址
	'address' => 'http://ztbcms.com',
	//模块作者
	'author' => 'ZtbCMS',
	//作者地址
	'authorsite' => 'http://www.ztbcms.com',
	//作者邮箱
	'authoremail' => 'admin@ztbcms.com',
	//版本号，请不要带除数字外的其他字符
	'version' => '1.0.1.0',
	//适配最低版本，
	'adaptation' => '3.0.0.0',
	//签名
	'sign' => 'b19cc279ed484c13c96c2f7142e2f437',
	//依赖模块
	'depend' => array('Content'),
	//行为注册
	'tags' => array(),
	//缓存，格式：缓存key=>array('module','model','action')
	'cache' => array(
		'Model_form' => array(
			'name' => '自定义表单模型',
			'model' => 'Formguide',
			'action' => 'formguide_cache',
		),
	),
);
