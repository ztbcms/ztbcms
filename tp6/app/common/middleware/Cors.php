<?php

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 支持跨域请求中间件
 */
class Cors
{
    protected $header = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age' => 3600,
        'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ];

    /**
     * 允许跨域请求
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed|Response
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        if (!empty($origin)) {
            $this->header['Access-Control-Allow-Origin'] = $origin;
        }
        if ($request->isOptions()) {
            return response('', 200, $this->header);
        }
        return $next($request)->header($this->header);
    }
}
