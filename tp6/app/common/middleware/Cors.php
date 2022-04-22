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
    protected $header
        = [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 1800,
            'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => '*',
        ];


    /**
     * 允许跨域请求
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed|Response
     */
    public function handle($request, Closure $next)
    {
        if ($request->isOptions()) {
            return response('', 200, $this->header);
        }

        return $next($request);
    }
}
