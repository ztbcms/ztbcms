<?php
/**
 * User: jayinton
 * Date: 2019-08-24
 * Time: 14:11
 */

namespace Member\Controller;


use Common\Controller\AdminBase;

class DashboardAdminController extends AdminBase
{
    function index()
    {
        $this->display();
    }

    /**
     * 获取面板数据
     */
    function getDashboardIndexInfo()
    {
        $total_member = M('member')->count();

        $today_new_member = M('member')->where([
            ['regdate' => ['EGT', strtotime(date('Y-m-d'))]],
            ['regdate' => ['ELT', strtotime(date('Y-m-d')) + 24 * 60 * 60 - 1]],
        ])->count();

        $last_sevent_day_new_member = M('member')->where([
            ['regdate' => ['EGT', strtotime(date('Y-m-d')) - 6 * 24 * 60 * 60]],
            ['regdate' => ['ELT', strtotime(date('Y-m-d')) + 24 * 60 * 60 - 1]],
        ])->count();

        $admin_statistics_info = [
            'total_member' => $total_member,
            'today_new_member' => $today_new_member,
            'last_sevent_day_new_member' => $last_sevent_day_new_member,
        ];

        $return_data = [
            'admin_statistics_info' => $admin_statistics_info
        ];

        $this->ajaxReturn(self::createReturn(true, $return_data));
    }

}