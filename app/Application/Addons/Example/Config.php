<?php

/**
 * 插件配置，下面是示例
 */
return array(
    'title' => array(//配置在表单中的键名 ,这个会是config[title]
        'title' => '显示标题:', //表单的文字
        'type' => 'text', //表单的类型：text、textarea、checkbox、radio、select等
        'value' => '拉拉', //表单的默认值
        'style' => "width:200px;", //表单样式
    ),
    'password' => array(
        'title' => '密码输入框:',
        'type' => 'password',
        'value' => '',
        'style' => "width:200px;",
    ),
    'hidden' => array(
        'title' => '隐藏文本框:',
        'type' => 'hidden',
        'value' => '',
    ),
    'radio' => array(
        'title' => '是否显示:',
        'type' => 'radio',
        'options' => array(
            '1' => '显示',
            '0' => '不显示'
        ),
        'value' => '1'
    ),
    'select' => array(
        'title' => '下拉列表:',
        'type' => 'select',
        'options' => array(
            '1' => '1格',
            '2' => '2格',
            '4' => '4格'
        ),
        'value' => '2'
    ),
    'checkbox' => array(
        'title' => '复选框:',
        'type' => 'checkbox',
        'options' => array(
            '1' => '1格',
            '2' => '2格',
            '4' => '4格'
        ),
        'value' => '2'
    ),
    'textarea' => array(
        'title' => '多行文本框:',
        'type' => 'textarea',
        'value' => '2',
        'style' => "width:200px;",
    ),
    'file' => array(
        'title' => '文件上传:',
        'type' => 'file',
        'alowexts' => 'zip,jpg',
        'value' => '',
    ),
    'groups' => array(
        'title' => '会员组:',
        'type' => 'group',
        'showtype' => 'checkbox',
        'value' => '',
    ),
    'editor' => array(
        'title' => '编辑器:',
        'type' => 'editor',
        'toolbar' => 'basic',
        'value' => 'aaa',
    ),
);
