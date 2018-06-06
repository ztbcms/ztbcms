<?php

// +----------------------------------------------------------------------
// |  配置
// +----------------------------------------------------------------------
return array(
    'URL_MODEL' => 0,
    'COMPONENTS' => array(
        'Module' => array(
            'class' => '\\Libs\\System\\Module',
            'path' => 'Libs.System.Module',
        ),
    ),
    'LOGIN_MAX_FAILD' => 5,
    'BAN_LOGIN_TIME' => 30 //登录失败5次之后需等待30分钟才可再次登录
);
