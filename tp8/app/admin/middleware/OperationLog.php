<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\middleware;

use app\admin\model\OperationlogModel;
use app\admin\service\AdminConfigService;

/**
 * 操作日志管理
 * Class OperationLog
 *
 * @package app\admin\middleware
 */
class OperationLog
{
    /**
     * 进入请求
     *
     * @param $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    /**
     * 请求返回回调
     *
     * @param  \think\Response  $response
     */
    public function end(\think\Response $response)
    {
        //记录后台操作日志
        //未开启的状态下不进行记录
        $admin_operation_switch = AdminConfigService::getInstance()->getConfig('admin_operation_switch')['data'];
        if ($admin_operation_switch == 1) {
            OperationlogModel::record($response);
        }
    }
}