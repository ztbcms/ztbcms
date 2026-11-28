<?php

namespace app\common\middleware;

use Closure;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;
use think\Request;
use think\Response;

/**
 * 为使用 Session Cookie 的后台控制器提供 CSRF 防护。
 * Session 初始化后生成并保存随机 Token，由后台模板输出给同源请求使用。
 * 同一会话可在多个页面和连续提交中复用此 Token。
 * 启用校验时，登录、退出和其他非只读请求须通过 X-CSRF-TOKEN 提交匹配值，
 * 否则在控制器执行前返回 HTTP 403。兼容模式仍提供 Token，便于旧项目逐步接入。
 */
class AdminCsrf
{
    private const SESSION_KEY = '_admin_csrf_token';
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session(self::SESSION_KEY);
        $safe = in_array($request->method(true), self::SAFE_METHODS, true)
            && in_array($request->method(), self::SAFE_METHODS, true);

        if (Config::get('admin.csrf_enabled') && !$safe) {
            $submittedToken = $request->header('X-CSRF-TOKEN');
            if (!is_string($token) || $token === '' || !is_string($submittedToken)
                || !hash_equals($token, $submittedToken)) {
                return json(createReturn(
                    false,
                    ['reason' => 'csrf_validation_failed'],
                    '页面校验失败，请刷新页面后重试',
                    403
                ), 403);
            }
        }

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        View::assign('adminCsrfToken', $token);

        $response = $next($request);
        if (stripos((string) $response->getHeader('Content-Type'), 'text/html') === 0) {
            $response->header(['Cache-Control' => 'no-store']);
        }

        return $response;
    }
}
