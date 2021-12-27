<?php

namespace app\common\middleware;

use app\admin\model\AdminUserModel;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use think\facade\Config;
use think\facade\View;
use think\middleware\AllowCrossDomain;
use think\Request;
use think\Response;

/**
 * 支持跨域请求中间件
 */
class Cors extends AllowCrossDomain
{
    protected $header
        = [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 1800,
            'Access-Control-Allow-Methods'     => '*',
            'Access-Control-Allow-Headers'     => '*',
        ];
}
