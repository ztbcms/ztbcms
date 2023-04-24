<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\api\controller;

use Firebase\JWT\JWT;
use think\Request;

class Common extends BaseApi
{
    protected $skillAuthActions = ['login'];

    /**
     * 登录
     * @return \think\response\Json
     */
    function login()
    {
        // 自行实现用户凭证校验
        // 数据
        $payload = array(
            "uid" => time(),
            "exp" => time() + 7 * 24 * 60 * 60, // 有效期
        );
        // 生成 JWT
        $config = config('api.jwt');
        $jwt = JWT::encode($payload, $config['secret_key'], $config['algorithm']);
        return self::makeJsonReturn(true, [
            'token' => $jwt,
        ], '认证完成');
    }

    /**
     * 登录信息
     * @return \think\response\Json
     */
    function authInfo(Request $request)
    {
        return self::makeJsonReturn(!!$request->authorization, $request->authorization);
    }
}