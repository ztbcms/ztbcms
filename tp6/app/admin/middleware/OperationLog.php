<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\middleware;

use app\admin\model\OperationlogModel;

/**
 * 操作日志管理
 * Class OperationLog
 * @package app\admin\middleware
 */
class OperationLog
{
    /**
     * 进入请求
     * @param $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    /**
     * 请求返回回调
     * @param  \think\Response  $response
     */
    public function end(\think\Response $response)
    {
        //记录后台操作日志
        (new OperationlogModel())->record($response);
    }
}