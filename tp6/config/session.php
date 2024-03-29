<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'SID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式
    'type'           => 'cache',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间，默认是7天过期
    'expire'         => 604800,
    // 前缀
    'prefix'         => 'SID_',
];
