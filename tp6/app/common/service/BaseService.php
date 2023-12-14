<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-08
 * Time: 09:18.
 */

namespace app\common\service;


class BaseService
{
    protected $error;

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * 创建统一的Service返回结果
     *
     * @param boolean $status 返回状态
     * @param array   $data   返回数据
     * @param string  $msg    返回提示
     * @param string  $code   错误码
     *
     * @return array
     */
    static function createReturn($status, $data = [], $msg = '', $code = null) {
        //默认成功则为200 错误则为400
        if(empty($code)){
            $code = $status ? 200 : 400;
        }
        return [
            'status' => $status,
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
        ];
    }

    /**
     * 返回列表信息
     *
     * @param $status
     * @param $items
     * @param $page
     * @param $limit
     * @param $total_items
     * @param $total_pages
     *
     * @return array
     */
    static function createReturnList($status, $items, $page, $limit, $total_items, $total_pages) {
        $data = [
            'items'       => $items,
            'page'        => intval($page),
            'limit'       => intval($limit),
            'total_items' => intval($total_items),
            'total_pages' => intval($total_pages),
        ];

        return self::createReturn($status, $data);
    }
}