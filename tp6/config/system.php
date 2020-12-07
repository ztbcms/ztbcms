<?php
/**
 * Author: jayinton
 */

// 系统配置

//引入cms数据库配置
if (file_exists(config_path()."dataconfig.php")) {
    $cmsDataConfig = include config_path()."dataconfig.php";
}

return [
    /* 站点安全设置 */
    "authcode"           => $cmsDataConfig['AUTHCODE'] ?? '', //密钥

    /* Cookie设置 */
    "cookie_prefix"      => $cmsDataConfig['COOKIE_PREFIX'] ?? '', //Cookie前缀

    /* 数据缓存设置 */
    'data_chache_prefix' => $cmsDataConfig['DATA_CACHE_PREFIX'] ?? '', // 缓存前缀
];


