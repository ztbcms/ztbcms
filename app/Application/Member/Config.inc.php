<?php

/**
 * 模块安装，基本配置
 */
return array(
    //模块名称
    'modulename' => '会员中心',
    //图标
    'icon' => 'https://dn-coding-net-production-pp.qbox.me/e57af720-f26c-4f3b-90b9-88241b680b7b.png',
    //模块简介
    'introduce' => '免费版会员中心',
    //模块介绍地址
    'address' => 'http://www.ztbcms.com',
    //模块作者
    'author' => 'ZtbCMS',
    //作者地址
    'authorsite' => 'http://www.ztbcms.com',
    //作者邮箱
    'authoremail' => 'admin@ztbcms.com',
    //版本号，请不要带除数字外的其他字符
    'version' => '1.0.2',
    //适配最低版本，
    'adaptation' => '2.0.0',
    //签名
    'sign' => '05f78872791fe1847815f5a192aa6dce',
    //依赖模块
    'depend' => array(),
    //注册缓存
    'cache' => array(
        'Member_Config' => array(
            'name' => '会员配置',
            'model' => 'Member',
            'action' => 'member_cache',
        ),
        'Member_group' => array(
            'name' => '会员组',
            'model' => 'MemberGroup',
            'action' => 'membergroup_cache',
        ),
        'Model_Member' => array(
            'name' => '会员模型',
            'model' => 'Member',
            'action' => 'member_model_cahce',
        ),
    ),
    //行为
    'tags' => array(
        'view_admin_top_menu' => array(
            'title' => '后台框架首页右上角菜单',
            'remark' => '后台框架首页右上角菜单',
            'type' => 2,
            'phpfile:ViewAdminTopMenuBehavior|module:Member',
        ),
        'view_member_menu' => array(
            'title' => '管理中心左侧导航',
            'remark' => '管理中心左侧导航',
            'type' => 2,
        ),
        'view_member_right' => array(
            'title' => '管理中心右侧',
            'remark' => '管理中心右侧',
            'type' => 2,
        ),
        'view_member_show_medal' => array(
            'title' => '会员个人空间首页勋章',
            'remark' => '会员个人空间首页勋章显示',
            'type' => 2,
            'phpfile:ViewMemberShowMedalBehavior|module:Member',
        ),
        'view_member_home_indexright' => array(
            'title' => '会员个人空间首页右侧',
            'remark' => '会员个人空间首页右侧信息',
            'type' => 2,
            'phpfile:ViewMemberHomeIndexrightBehavior|module:Member',
        ),
        'view_member_home_right' => array(
            'title' => '会员个人空间右侧',
            'remark' => '会员个人空间右侧信息',
            'type' => 2,
            'phpfile:ViewMemberHomeRightBehavior|module:Member',
        ),
        'view_member_home_nav' => array(
            'title' => '会员个人空间导航',
            'remark' => '会员个人空间导航',
            'type' => 2,
        ),
        'action_member_loginend' => array(
            'title' => '会员登录成功后行为调用',
            'remark' => '会员登录成功后行为调用',
            'type' => 1,
        ),
        'action_member_registerend' => array(
            'title' => '会员注册成功后行为调用',
            'remark' => '会员注册成功后行为调用',
            'type' => 1,
        ),
        'action_member_logout' => array(
            'title' => '会员退出登录后行为调用',
            'remark' => '会员退出登录后行为调用',
            'type' => 1,
        ),
        'content_check_end' => array(
            'title' => '信息审核后的行为调用',
            'remark' => '信息审核后的行为调用',
            'type' => 1,
            'phpfile:ContentCheckEndBehavior|module:Member',
        ),
        'content_edit_end' => array(
            'title' => '内容修改完成时行为调用',
            'remark' => '内容修改完成时行为调用',
            'type' => 1,
            'phpfile:ContentEditEndBehavior|module:Member',
        ),
        'content_delete_end' => array(
            'title' => '内容删除完成时行为调用',
            'remark' => '内容删除完成时行为调用',
            'type' => 1,
            'phpfile:ContentDeleteEndBehavior|module:Member',
        ),
    ),
);
