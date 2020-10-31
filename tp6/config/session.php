<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

//引入cms数据库配置
if (file_exists(root_path() . "config/dataconfig.php")) {
    $cmsDataConfig = include root_path() . "config/dataconfig.php";
} else {
    // 兼容 tp3.2
    if (file_exists(root_path() . "../app/Common/Conf/dataconfig.php")) {
        $cmsDataConfig = include root_path() . "../app/Common/Conf/dataconfig.php";
    } else {
        throw new \Exception('找不到dataconfig.php文件');
    }
}

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 1440,
    // 前缀
    'prefix'         => $cmsDataConfig['COOKIE_PREFIX'],
];
