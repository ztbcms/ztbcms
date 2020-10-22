<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\controller;

use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use app\admin\service\AdminOperationLogService;
use app\admin\service\LoginlogService;
use app\common\controller\AdminController;
use think\Request;

/**
 * 日志管理
 * Class Logs
 *
 * @package app\admin\controller
 */
class Logs extends AdminController
{

    /**
     * 登录记录列表
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function loginLogList(Request $request)
    {
        $action = input('_action', '', 'trim');
        if ($action == 'getList') {
            $where = [];
            $username = input('username', '', 'trim');
            if (!empty($username)) {
                $where['username'] = array('like', '%'.$username.'%');
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            $logintime = [];
            if (!empty($start_time) && !empty($end_time)) {
                $logintime = [$start_time, $end_time];
            }
            $loginip = input('loginip', '', 'trim');
            if (!empty($loginip)) {
                $where['loginip '] = array('like', "%{$loginip}%");
            }
            $status = input('status', '', 'trim');
            if ($status != '') {
                $where['status'] = $status;
            }
            $page = input('page', 1, 'trim');
            $limit = input('limit', 20, 'trim');
            $res = LoginlogService::getLoginLogList($where, 'id desc', $page, $limit, $logintime);
            return json($res);
        } else {
            return view('loginLogList');
        }
    }

    /**
     * 删除一个月以前的任务
     *
     * @return \think\response\Json
     */
    function deleteLoginLog()
    {
        $LoginlogModel = new LoginlogModel();
        $LoginlogModel->deleteAMonthago();
        return json(self::createReturn(true, '', '删除成功'));
    }

    /**
     * 获取后台操作日志列表
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function adminOperationLogList(Request $request)
    {
        $action = input('_action', '', 'trim');
        if ($action == 'getList') {
            $where = [];
            $uid = input('uid', '', 'trim');
            if (!empty($uid)) {
                $where['uid'] = array('eq', $uid);
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            $time = [];
            if (!empty($start_time) && !empty($end_time)) {
                $time = [$start_time, $end_time];
            }
            $ip = input('ip', '', 'trim');
            if (!empty($ip)) {
                $where['ip '] = array('like', "%{$ip}%");
            }
            $status = input('status', '', 'trim');
            if ($status != '') {
                $where['status'] = (int) $status;
            }
            $page = input('page', 1, 'trim');
            $limit = input('limit', 20, 'trim');
            $sort_time = input('sort_time', '', 'trim');
            $order = ["id" => "desc"];
            if (!empty($sort_time)) {
                $order = ['time' => $sort_time == 'desc' ? 'desc' : 'asc'];
            }
            $res = AdminOperationLogService::getAdminOperationLogList($where, $order, $page, $limit, $time);
            return json($res);
        } else {
            return view('adminOperationLogList');
        }
    }

    /**
     * 删除后台操作日志
     *
     * @return \think\response\Json
     */
    function deleteAdminOperationLog()
    {
        $OperationlogModel = new OperationlogModel();
        $OperationlogModel->deleteAMonthago();
        return json(self::createReturn(true, '', '删除成功'));
    }
}