<?php
/**
 * Author: jayinton
 */

namespace app\admin\controller;


use app\admin\service\UserOperateLogService;
use app\common\controller\AdminController;

/**
 * 用户操作日志
 *
 * @package app\admin\controller
 */
class UserOperateLog extends AdminController
{
    /**
     * 列表页
     *
     * @return \think\response\Json|\think\response\View
     */
    function index()
    {
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
        return view();
    }
}