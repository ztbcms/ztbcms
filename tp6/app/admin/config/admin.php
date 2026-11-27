<?php
/**
 * Auth: jayinton
 */


return [
    'login_faild_time_interval'         => 5, //登录失败判定时间间隔，单位，分钟
    'login_max_faild'                   => 5, //登录失败判定次数
    'ban_login_time'                    => 30, //禁止登录时间
    'login_account_faild_time_interval' => 15, //同账号连续失败判定时间间隔（分钟）
    'login_account_max_faild'           => 5, //同账号连续失败锁定阈值
    'login_account_ban_time'            => 15, //同账号禁止登录时间（分钟）
    'proxy_server_ips'                  => [], //受信反向代理服务器 IP/CIDR 列表
    'proxy_client_ip_headers'           => [], //受信代理客户端 IP 头（需与 proxy_server_ips 显式配套配置）
    'admin_panel_security_code'         => '', //后台面板安全码
];