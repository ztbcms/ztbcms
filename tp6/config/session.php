<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

//引入cms数据库配置
$cmsDataConfig = [];
if (file_exists(config_path()."dataconfig.php")) {
    $cmsDataConfig = include config_path()."dataconfig.php";
}

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'cache',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 1440,
    // 前缀
    'prefix'         => $cmsDataConfig['COOKIE_PREFIX'] ?? '',
];
