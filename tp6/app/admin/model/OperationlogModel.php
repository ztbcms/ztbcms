<?php

// +----------------------------------------------------------------------
// |  后台操作日志
// +----------------------------------------------------------------------

namespace app\admin\model;

use app\admin\service\AdminUserService;
use think\Model;
use think\facade\Request;

class OperationlogModel extends Model
{

    protected $name = 'operationlog';

    /**
     * 删除一个月前的日志
     * @return boolean
     */
    public function deleteAMonthago()
    {
        $status = $this->where(array("time" => array("lt", time() - (86400 * 30))))->delete();
        return $status !== false ? true : false;
    }

    /**
     * 记录日志
     * @param  \think\Response  $request
     * @return bool
     */
    public function record(\think\Response $request) {
        $fangs = 'GET';
        if (Request::instance()->isGet()) {
            $fangs = 'GET';
        } else if (Request::instance()->isPost()) {
            $fangs = 'POST';
        }

        if(request()->url() == '/home/admin/AdminMessage/getAdminMsgList?page=1&limit=10&read_status=0') {
            //不记录长轮训的日志信息
            return  true;
        }

        $content = json_decode($request->getContent(),true);
        $url = request()->url();
        $logData['uid'] = AdminUserService::getInstance()->getInfo()['id'] ?: 0;
        $logData['status'] = $content['status'] ? 1 : 0;
        $logData['info'] = "提示语：{$content['msg']}<br/>路由：{$url},<br/>请求方式：{$fangs}";
        $logData['get'] = request()->url();
        $logData['time'] = time();
        $logData['ip'] = $_SERVER['REMOTE_ADDR'];
        return $this->insert($logData) !== false ? true : false;
    }

}
