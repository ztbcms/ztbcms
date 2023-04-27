<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\api\middleware;

use app\common\service\jwt\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\response\Json;

class ApiAuth
{
    /**
     * 进入请求
     *
     * @param $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 跳过认证
        $skillAuthActions = $request->skillAuthActions ?? [];
        if (!empty($skillAuthActions) && $this->_checkActionMatch($request->action(), $skillAuthActions)) {
            // 不需要验证用户凭证
            return $next($request);
        }
        return $this->auth($request, $next);
    }

    /**
     * 检测控制器的方法是否匹配
     *
     * @param $action
     * @param $arr
     *
     * @return bool
     */
    function _checkActionMatch($action, array $arr)
    {
        if (empty($arr)) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在, * 为忽略全部
        if (in_array(strtolower($action), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 验证用户凭证
     * @param $request
     * @param \Closure $next
     * @return mixed|Json
     */
    private function auth($request, \Closure $next)
    {
        $auth_str = request()->header('Authorization');
        if (empty($auth_str)) {
            return json(createReturn(false, null, '凭证不能为空'));
        }
        $auth_str = explode(' ', $auth_str);
        if (empty($auth_str) || empty($auth_str[1])) {
            return json(createReturn(false, null, '凭证不能为空'));
        }
        $token = $auth_str[1];   // 生成 JWT
        $jwtService = new JwtService();
        $res = $jwtService->parserToken($token);
        if(!$res['status']){
            return json($res);
        }
        // 注入登录用户信息
        $request->authorization = $res['data'];
        return $next($request);
    }

    /**
     * 请求返回回调
     *
     * @param \think\Response $response
     */
    public function end(\think\Response $response)
    {

    }
}