<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\middleware;

use app\admin\model\OperationlogModel;

class OperationLog
{
    /**
     * 开始
     * @param $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    /**
     * 回调
     * @param  \think\Response  $response
     */
    public function end(\think\Response $response)
    {
        //记录后台操作日志
        (new OperationlogModel())->record($response);
    }
}