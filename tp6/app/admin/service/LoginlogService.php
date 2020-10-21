<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\service;

use app\admin\model\LoginlogModel;
use app\common\service\BaseService;

class LoginlogService extends BaseService
{

    /**
     * 获取登录日志
     * @param  array  $where
     * @param  string  $order
     * @param  string  $page
     * @param  string  $limit
     * @param  array  $logintime
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getLoginLogList($where = [], $order = '', $page = '', $limit = '', $logintime = [])
    {
        $LoginlogModel = new LoginlogModel();
        $db = $LoginlogModel->where($where)->order($order)->page($page)->limit($limit);
        if (!empty($logintime)) {
            $db->whereTime('logintime', 'between', $logintime);
        }
        if (!empty($order)) {
            $db->order($order);
        }
        $items = $db->select();

        $total_items = $LoginlogModel->where($where)->count();
        $total_page = ceil($total_items / $limit);
        return self::createReturnList(true, $items, $page, $limit, $total_items, $total_page);
    }

}