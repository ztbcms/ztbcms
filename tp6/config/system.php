<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

// 系统配置

//引入cms数据库配置
if (file_exists(root_path() . "../app/Common/Conf/dataconfig.php")) {
    $cmsDataConfig = include root_path() . "../app/Common/Conf/dataconfig.php";
} else {
    throw new \Exception('找不到dataconfig.php文件');
}

return [
    /* 站点安全设置 */
    "authcode" => $cmsDataConfig['AUTHCODE'], //密钥

    /* Cookie设置 */
    "cookie_prefix" => $cmsDataConfig['COOKIE_PREFIX'], //Cookie前缀

    /* 数据缓存设置 */
    'data_chache_prefix' => $cmsDataConfig['DATA_CACHE_PREFIX'], // 缓存前缀
];


