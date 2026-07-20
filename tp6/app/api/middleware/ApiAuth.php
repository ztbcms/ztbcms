<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\api\middleware;

use app\common\service\jwt\JwtService;
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
        $action = $request->action();
        // 跳过认证
        $skipAuthActions = $request->skipAuthActions ?? [];
        if (!empty($skipAuthActions) && $this->_checkActionMatch($action, $skipAuthActions)) {
            // 不需要验证用户凭证
            return $next($request);
        }
        // 尝试认证，即认证不通过时不会中断请求
        $tryAuthActions = $request->tryAuthActions ?? [];
        if (!empty($tryAuthActions) && $this->_checkActionMatch($action, $tryAuthActions)) {
            return $this->tryAuth($request, $next);
        }
        return $this->auth($request, $next, 401);
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
     * @param $unauth_code int 未授权时返回的状态码
     * @return mixed|Json
     */
    private function auth($request, \Closure $next, $unauth_code)
    {
        // header格式: Authorization: Bearer xxxxx
        $auth_str = request()->header('Authorization');
        if (empty($auth_str)) {
            return json(createReturn(false, null, '凭证不能为空', $unauth_code));
        }
        $auth_str = explode(' ', $auth_str);
        if (empty($auth_str) || empty($auth_str[1])) {
            return json(createReturn(false, null, '凭证不能为空', $unauth_code));
        }
        $token = $auth_str[1];
        $jwtService = new JwtService($this->getJwtScene($request));
        $res = $jwtService->parserToken($token);
        if (!$res['status']) {
            return json(createReturn(false, null, $res['msg'], $unauth_code));
        }
        // 注入登录用户信息
        $request->authorization = $res['data'];
        return $next($request);
    }

    /**
     * 验证用户凭证
     * @param $request
     * @param \Closure $next
     * @return mixed|Json
     */
    private function tryAuth($request, \Closure $next)
    {
        // header格式: Authorization: Bearer xxxxx
        $auth_str = request()->header('Authorization');
        if (empty($auth_str)) {
            return $next($request);
        }
        $auth_str = explode(' ', $auth_str);
        if (empty($auth_str) || empty($auth_str[1])) {
            return $next($request);
        }
        $token = $auth_str[1];
        $jwtService = new JwtService($this->getJwtScene($request));
        $res = $jwtService->parserToken($token);
        if (!$res['status']) {
            return $next($request);
        }
        // 注入登录用户信息
        $request->authorization = $res['data'];
        return $next($request);
    }

    /**
     * 获取 JWT 场景
     *
     * 仅从服务端注入的请求上下文读取，不从用户输入决定场景。
     *
     * @param $request 请求对象
     * @return string
     */
    private function getJwtScene($request): string
    {
        $scene = trim((string)($request->jwtScene ?? 'default'));
        return $scene !== '' ? $scene : 'default';
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
