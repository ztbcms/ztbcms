<?php

// +----------------------------------------------------------------------
// |  插件模块配置
// +----------------------------------------------------------------------
return array(
    //模块名称
    'modulename' => '插件管理',
    //图标
    'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
    //模块简介
    'introduce' => '插件管理是ZtbCMS官方开发的高级扩展，支持插件的安装和创建~。',
    //模块介绍地址
    'address' => 'http://www.ztbcms.com',
    //模块作者
    'author' => 'ZtbCMS',
    //作者地址
    'authorsite' => 'http://www.ztbcms.com',
    //作者邮箱
    'authoremail' => 'admin@ztbcms.com',
    //版本号，请不要带除数字外的其他字符
    'version' => '1.1.3',
    //适配最低版本，
    'adaptation' => '3.0.0.0',
    //签名
    'sign' => '912b7e22bd9d86dddb1d460ca90581eb',
    //依赖模块
    'depend' => array(),
    //缓存，格式：缓存key=>array('module','model','action')
    'cache' => array(
        'Addons' => array(
            'name' => '插件列表',
            'model' => 'Addons',
            'action' => 'addons_cache',
        ),
    ),
);
