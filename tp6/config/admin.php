<?php

// 管理后台相关信息
return [
    "cms_name"                          => "ZTBCMS开发框架", //CMS名称
    "cms_build"                         => '202611232232', //CMS流水号
    "cms_version"                       => "4.12.3", //CMS版本号
    "application_version"               => "0.0.0", //产品版本号
    // 登录防爆破与受信代理配置
    "login_faild_time_interval"         => 5, // 同 IP 登录失败判定时间间隔（分钟）
    "login_max_faild"                   => 5, // 同 IP 登录失败判定次数
    "ban_login_time"                    => 30, // 同 IP 禁止登录时间（分钟）
    "login_account_faild_time_interval" => 15, // 同账号连续失败判定时间间隔（分钟）
    "login_account_max_faild"           => 5, // 同账号连续失败锁定阈值
    "login_account_ban_time"            => 15, // 同账号禁止登录时间（分钟）
    "proxy_server_ips"                  => [], // 受信反向代理服务器 IP/CIDR 列表，留空表示不信任转发头
    "proxy_client_ip_headers"           => [], // 受信代理下允许解析的客户端 IP 请求头（需与 proxy_server_ips 显式配套配置，如 ['HTTP_X_FORWARDED_FOR']）
];
