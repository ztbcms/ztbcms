<?php
/**
 * User: Cycle3
 * Date: 2020/10/21
 */

namespace app\admin\service;

use app\admin\model\OperationlogModel;
use app\common\service\BaseService;

/**
 * 后台操作日志
 * Class AdminOperationLogService
 * @package app\admin\service
 */
class AdminOperationLogService extends BaseService
{
    /**
     * 获取后台操作日志列表
     * @param  array  $where
     * @param  string  $order
     * @param  string  $page
     * @param  string  $limit
     * @param  array  $time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getAdminOperationLogList($where = [], $order = '', $page = '', $limit = '', $time = [])
    {
        $OperationlogModel = new OperationlogModel();
        $db = $OperationlogModel->where($where)->order($order)->page($page)->limit($limit);
        if (!empty($time)) {
            $db->whereTime('time', 'between', $time);
        }
        if (!empty($order)) {
            $db->order($order);
        }
        $items = $db->select();
        $total_items = $OperationlogModel->where($where)->count();
        $total_page = ceil($total_items / $limit);
        return self::createReturnList(true, $items, $page, $limit, $total_items, $total_page);
    }

}
