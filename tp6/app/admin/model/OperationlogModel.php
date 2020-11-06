<?php

// +----------------------------------------------------------------------
// |  后台操作日志
// +----------------------------------------------------------------------

namespace app\admin\model;

use app\admin\service\AdminConfigService;
use app\admin\service\AdminUserService;
use think\Model;
use think\facade\Request;

class OperationlogModel extends Model
{

    protected $name = 'operationlog';

    /**
     * 删除登录日志(X天前的数据)
     *
     * @param  int  $day  N
     *
     * @return bool
     */
    public function deleteOperationLog($day = 30)
    {
        $limit_time = time() - $day * 24 * 60 * 60;
        $this->where('time', '<=', $limit_time)->delete();
        return true;
    }

    /**
     * 记录日志
     * @param  \think\Response  $request
     * @return bool
     */
    public function record(\think\Response $request) {
        if (Request::instance()->isPost()) {

            //未开启的状态下不进行记录
            $admin_operation_switch = AdminConfigService::getInstance()->getConfig('admin_operation_switch')['data'];
            if(!is_numeric($admin_operation_switch)) return true;
            if($admin_operation_switch != 1) return true;

            $content = json_decode($request->getContent(),true);
            $url = request()->url();
            $logData['uid'] = AdminUserService::getInstance()->getInfo()['id'] ?: 0;
            $logData['status'] = $content['status'] ? 1 : 0;
            $logData['info'] = "提示语：{$content['msg']}<br/>路由：{$url},<br/>请求方式：POST";
            $logData['get'] = request()->url();
            $logData['time'] = time();
            $logData['ip'] = request()->ip();
            return $this->insert($logData) !== false ? true : false;
        } else {
            return  true;
        }
    }

}
