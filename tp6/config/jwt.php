<?php
return [
    /**
     * jwt使用场景配置
     */
    'scene' => [
        'default' => [
            // 密钥
            'secret_key' => env('JWT_SECRET_KEY', env('security.authcode')),
            // 算法
            'algorithm' => env('JWT_ALGORITHM', 'HS256'),
            // Token有效期，单位为秒
            'ttl' => 7200,
        ],
        'app' => [
            // 你的配置
        ]
    ]
];
