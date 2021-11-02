<?php
/**
 * Author: jayinton
 */

namespace app\admin\service;


use app\admin\model\UserOperateLogModel;
use app\common\service\BaseService;

class UserOperateLogService extends BaseService
{

    /**
     * 获取操作日志列表
     *
     * @param  array  $where
     * @param  string  $order
     * @param  string  $page
     * @param  string  $limit
     * @param  array  $time
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getUserOperateLogList($where = [], $order = '', $page = '', $limit = '', $time = [])
    {
        $UserOperatelogModel = new UserOperateLogModel();
        $db = $UserOperatelogModel->where($where)->order($order)->page($page)->limit($limit);
        if (!empty($time)) {
            $db->whereTime('create_time', 'between', $time);
        }
        if (!empty($order)) {
            $db->order($order);
        }
        $items = $db->select();
        $total_items = $UserOperatelogModel->where($where)->count();
        $total_page = ceil($total_items / $limit);
        return self::createReturnList(true, $items, $page, $limit, $total_items, $total_page);
    }

    /**
     * 添加用户操作记录
     *
     * @param $operateData
     *
     * @return bool
     */
    static function addUserOperateLog($operateData)
    {
        $userInfo = AdminUserService::getInstance()->getInfo();
        $data = [
            'user_id'     => $userInfo['id'],
            'user_name'   => $userInfo['username'],
            'create_time' => time(),
            'ip'          => request()->ip(),
            'source_type' => $operateData['source_type'] ?? '',
            'source'      => $operateData['source'] ?? 0,
            'content'     => $operateData['content'] ?? ''
        ];
        $UserOperateLogModel = new UserOperateLogModel();
        $UserOperateLogModel->insert($data);
        return true;
    }
}