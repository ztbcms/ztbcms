<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\controller;

use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use app\admin\service\AdminConfigService;
use app\admin\service\AdminOperationLogService;
use app\admin\service\LoginlogService;
use app\admin\service\UserOperateLogService;
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
     * @param Request $request
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
                $where[] = ['username', 'like', '%' . $username . '%'];
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            $logintime = [];
            if (!empty($start_time) && !empty($end_time)) {
                $logintime = [$start_time, $end_time];
            }
            $loginip = input('loginip', '', 'trim');
            if (!empty($loginip)) {
                $where[] = ['loginip', 'like', "%" . $loginip . "%"];
            }
            $status = input('status', '', 'trim');
            if ($status != '') {
                $where[] = ['status', '=', $status];
            }
            $sort_time = input('sort_time', '', 'trim');
            $order = ["id" => "desc"];
            if (!empty($sort_time)) {
                $order = ['logintime' => $sort_time == 'desc' ? 'desc' : 'asc'];
            }
            $page = input('page', 1, 'trim');
            $limit = input('limit', 20, 'trim');
            $res = LoginlogService::getLoginLogList($where, $order, $page, $limit, $logintime);
            return json($res);
        } else {
            return view('loginLogList');
        }
    }

    /**
     * 删除登录日志
     *
     * @return \think\response\Json
     */
    function deleteLoginLog()
    {
        $day = input('day', 30, 'intval');
        $LoginlogModel = new LoginlogModel();
        $LoginlogModel->deleteLoginLog($day);
        return json(self::createReturn(true, '', '删除成功'));
    }

    /**
     * 获取后台操作日志列表
     *
     * @param Request $request
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
                $where[] = ['uid', '=', $uid];
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            $time = [];
            if (!empty($start_time) && !empty($end_time)) {
                $time = [$start_time, $end_time];
            }
            $ip = input('ip', '', 'trim');
            if (!empty($ip)) {
                $where[] = ['ip', 'like', '%' . $ip . '%'];
            }
            $status = input('status', '', 'trim');
            if ($status != '') {
                $where[] = ['status', '=', (int)$status];
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
        } else if ($action == 'getAdminOperationSwitch') {
            $admin_operation_switch = (int)AdminConfigService::getInstance()->getConfig('admin_operation_switch')['data'];
            return json(self::createReturn(true, $admin_operation_switch));
        } else if ($action == 'switchingAdminOperation') {
            $admin_operation_switch = input('admin_operation_switch', '', 'trim');
            $getConfig = AdminConfigService::getInstance()->updateConfig(['admin_operation_switch' => $admin_operation_switch]);
            AdminConfigService::getInstance()->clearConfigCache();
            return json($getConfig);
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
        $day = input('day', 30, 'intval');
        $OperationlogModel = new OperationlogModel();
        $OperationlogModel->deleteOperationLog($day);
        return json(self::createReturn(true, '', '删除成功'));
    }

    /**
     * 用户操作日志列表
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function userOperateLog(){
        $_action = input('_action');
        if ($_action == 'getList') {
            $where = [];
            $user_id = input('user_id', '', 'trim');
            if (!empty($user_id)) {
                $where[] = ['user_id', '=', $user_id];
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            $time = [];
            if (!empty($start_time) && !empty($end_time)) {
                $start_time = strtotime($start_time);
                $end_time = strtotime($end_time) + 24 * 60 * 60 - 1;
                $time = [$start_time, $end_time];
            }
            $ip = input('ip', '', 'trim');
            if (!empty($ip)) {
                $where[] = ['ip', 'like', '%'.$ip.'%'];
            }
            $source_type = input('source_type', '', 'trim');
            if ($source_type != '') {
                $where[] = ['source_type', 'like', '%'.$source_type.'%'];
            }
            $source = input('source', '', 'trim');
            if ($source != '') {
                $where[] = ['source', '=', "$source"];
            }
            $page = input('page', 1, 'trim');
            $limit = input('limit', 20, 'trim');
            $sort_time = input('sort_time', '', 'trim');
            $order = ["id" => "desc"];
            if (!empty($sort_time)) {
                $order = ['create_time' => $sort_time == 'desc' ? 'desc' : 'asc'];
            }
            $res = UserOperateLogService::getUserOperateLogList($where, $order, $page, $limit, $time);
            return json($res);
        }
        return view('userOperateLog');
    }
}
