<?php
/**
 * User: jayinton
 * Date: 2019/2/27
 * Time: 17:14
 */

namespace Admin\Controller;

use Common\Controller\AdminBase;

/**
 * 后台日志接口
 *
 * @package Admin\Controller
 */
class LogsApiController extends AdminBase
{
    /**
     * 获取后台操作总日志
     */
    function getOperateLogList()
    {
        $uid = I('uid');
        $start_time = I('start_time');
        $end_time = I('end_time');
        $ip = I('ip');
        $status = I('status');
        $page = I('page', 1);
        $limit = I('limit', 20);
        $sort_time = I('sort_time');
        $where = array();
        if (!empty($uid)) {
            $where['uid'] = array('eq', $uid);
        }

        if (!empty($start_time) && !empty($end_time)) {
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where['time'] = array(array('EGT', $start_time), array('ELT', $end_time), 'AND');
        }
        if (!empty($ip)) {
            $where['ip '] = array('like', "%{$ip}%");
        }
        if ($status != '') {
            $where['status'] = (int)$status;
        }
        $total_count = M("Operationlog")->where($where)->count();
        $total_pages = ceil($total_count / $limit);

        $order = ["id" => "desc"];
        if (!empty($sort_time)) {
            $order = ['time' => $sort_time == 'desc' ? 'desc' : 'asc'];
        }
        $logs = M("Operationlog")->where($where)->page($page)->limit($limit)->order($order)->select();

        $this->ajaxReturn($this->createReturnList(true, $logs, $page, $limit, $total_count, $total_pages));
    }
}