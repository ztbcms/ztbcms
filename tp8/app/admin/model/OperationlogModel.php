<?php

namespace app\admin\model;

use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use think\Model;
use think\facade\Request;

/**
 * 操作日志
 *
 * @package app\admin\model
 */
class OperationlogModel extends Model
{

    protected $name = 'operation_log';

    /**
     * 删除登录日志(X天前的数据)
     *
     * @param  int  $day  天数
     *
     * @return bool
     */
    static function deleteOperationLog($day = 30)
    {
        $limit_time = time() - $day * 24 * 60 * 60;
        OperationlogModel::where('time', '<=', $limit_time)->delete();
        return true;
    }

    /**
     * 记录操作日志
     *
     * @param  \think\Response  $response
     *
     * @return bool
     */
    static function record(\think\Response $response)
    {
        if (Request::instance()->isPost()) {
            $request = request();
            $content = json_decode($response->getContent(), true);
            $status = $content['status'] ?? 0;
            $logData = [
                'uid'      => AdminUserService::getInstance()->getInfo()['id'] ?? 0,
                'status'   => $status ? 1 : 0,
                'method'   => $request->method(),
                'url'      => $request->url(),
                'params'   => json_encode($request->post()),
                'response' => $response->getContent(),
                'time'     => time(),
                'ip'       => $request->ip(),
            ];
            OperationlogModel::insert($logData);
            return true;
        } else {
            return false;
        }
    }

}
