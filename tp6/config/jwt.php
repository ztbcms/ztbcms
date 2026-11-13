<?php

$defaultSecret = (string)env('jwt.secret_key', '');
$appSecret = (string)env('jwt.secret_key_app', '');
$uploadSecret = (string)env('jwt.secret_key_upload', '');

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
        'upload' => [
            // 上传场景密钥（优先使用独立密钥 jwt.secret_key_upload，未配置时回退默认密钥并由用途声明隔离）
            'secret_key' => $uploadSecret !== '' ? $uploadSecret : $defaultSecret,
            // 上传凭证有效期，单位为秒
            'ttl' => 300,
        ],
    ],
];
