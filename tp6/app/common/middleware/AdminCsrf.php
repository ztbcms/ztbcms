<?php

namespace app\common\middleware;

use Closure;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;
use think\Request;
use think\Response;

/**
 * 校验依赖后台会话的写请求。
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
                return json(createReturn(false, null, '页面校验失败，请刷新页面后重试', 403), 403);
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
