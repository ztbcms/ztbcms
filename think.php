<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

//当前目录路径
define('SITE_PATH', getcwd() . '/');

require __DIR__ . '/tp6/vendor/autoload.php';


// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
