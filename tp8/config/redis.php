<?php

/**
 * Redis 链接配置
 */

return [
    // 默认使用的连接
    'default' => env('redis.connection', 'default'),

    // 连接配置信息
    'connections' => [
        'default' => [
            'scheme' => env('redis.scheme', 'tcp'), // tcp(推荐),unix,tls
            // scheme = tcp 时 hostname 和 port 必填；或直接 tcp://127.0.0.1:6379
            'host' => env('redis.host', '127.0.0.1'),
            'port' => env('redis.port', 6379),
            'password' => env('redis.password', ''), // 密码

            // scheme = unix 时 path 必填；或直接 unix:///tmp/redis.sock
            //'path' => '/tmp/redis.sock',

            // scheme = tls 时 tls_ca 和 tls_cert 和 tls_key 必填；或直接 tls://127.0.0.1?ssl[cafile]=private.pem&ssl[verify_peer]=true
            //'ssl' => ['cafile' => 'private.pem', 'verify_peer' => true],

            // 数据库序号，通常0~15
            'database' => env('redis.database', 0),
            // 链接超时时间，单位：秒
            'timeout' => env('redis.timeout', 5),
            // 是否持久化
            'persistent' => env('redis.persistent', false),
        ],
    ],
];