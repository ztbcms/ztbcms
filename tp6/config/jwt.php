<?php

$defaultSecret = (string)env('jwt.secret_key', '');
$appSecret = (string)env('jwt.secret_key_app', '');

return [
    /**
     * jwt使用场景配置
     */
    'scene' => [
        'default' => [
            // 密钥
            'secret_key' => $defaultSecret,
            // 算法
            'algorithm' => env('jwt.algorithm', 'HS256'),
            // Token有效期，单位为秒
            'ttl' => 7200,
        ],
        'app' => [
            // APP 场景独立密钥
            'secret_key' => $appSecret,
        ],
    ],
];
