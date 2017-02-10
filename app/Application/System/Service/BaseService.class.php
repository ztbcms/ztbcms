<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace System\Service;

class BaseService {

    /**
     * 创建统一的Service返回结果
     *
     * @param boolean $status
     * @param array   $data
     * @param string  $msg
     * @return array
     */
    protected static function createReturn($status, $data = [], $msg = '') {
        return [
            'status' => $status,
            'data' => $data,
            'msg' => $msg
        ];
    }

    /**
     * 获取
     *
     * @param string $tablename
     * @param array  $where
     * @return mixed
     */
    protected static function findBy($tablename = '', $where = []) {
        $result = M($tablename)->where($where)->find();

        return self::createReturn(true, $result);
    }

    /**
     * 筛选列表
     *
     * @param string $tablename
     * @param array  $where
     * @param string $order
     * @param int    $page
     * @param int    $limit
     * @return array
     */
    protected static function selectBy($tablename = '', $where = [], $order = '', $page = 1, $limit = 20) {
        $db = M($tablename);
        $result = $db->where($where)->order($order)->page($page)->limit($limit)->select();
        if (empty($result)) {
            $result = [];
        }

        return self::createReturn(true, $result);
    }

}