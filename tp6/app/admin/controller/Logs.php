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
 * @package app\admin\controller
 */
class Logs extends AdminController
{

    /**
     * 登录记录列表
     * @param  Request  $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginLogList(Request $request)
    {
        if ($request->isPost()) {
            $where = array();
            $username = Input('username');
            if (!empty($username)) {
                $where['username'] = array('like', '%'.$username.'%');
            }

            $start_time = Input('start_time');
            $end_time = Input('end_time');
            $logintime = [];
            if (!empty($start_time) && !empty($end_time)) {
                $logintime = [$start_time, $end_time];
            }

            $loginip = Input('loginip');
            if (!empty($loginip)) {
                $where['loginip '] = array('like', "%{$loginip}%");
            }

            $status = Input('status');
            if ($status != '') {
                $where['status'] = $status;
            }

            $page = Input('page', 1);
            $limit = Input('limit', 20);

            $res = LoginlogService::getLoginLogList($where, 'id desc', $page, $limit, $logintime);
            return json($res);
        } else {
            return view('loginLogList');
        }
    }

    /**
     * 删除一个月以前的任务
     * @return \think\response\Json
     */
    public function deleteLoginLog()
    {
        $LoginlogModel = new LoginlogModel();
        $LoginlogModel->deleteAMonthago();
        return json(self::createReturn(true, '', '删除成功'));
    }

    /**
     * 获取后台操作日志列表
     * @param  Request  $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function adminOperationLogList(Request $request)
    {
        if ($request->isPost()) {
            $where = [];

            $uid = Input('uid');
            if (!empty($uid)) {
                $where['uid'] = array('eq', $uid);
            }

            $start_time = Input('start_time');
            $end_time = Input('end_time');
            $time = [];
            if (!empty($start_time) && !empty($end_time)) {
                $time = [$start_time, $end_time];
            }

            $ip = Input('ip');
            if (!empty($ip)) {
                $where['ip '] = array('like', "%{$ip}%");
            }

            $status = Input('status');
            if ($status != '') {
                $where['status'] = (int) $status;
            }

            $page = Input('page', 1);
            $limit = Input('limit', 20);
            $sort_time = Input('sort_time');

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
     * @return \think\response\Json
     */
    public function deleteAdminOperationLog()
    {
        $OperationlogModel = new OperationlogModel();
        $OperationlogModel->deleteAMonthago();
        return json(self::createReturn(true, '', '删除成功'));
    }
}