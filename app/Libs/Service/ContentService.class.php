<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Libs\Service;

use Common\Model\RelationModel;
use System\Service\BaseService;

/**
 * 内容管理服务
 */
class ContentService extends BaseService {

    /**
     * 获取栏目信息列表
     * feature: 支持关联查询
     *
     * @param int|string $catid
     * @param array      $filter
     * @param array      $operator
     * @param array      $value
     * @param string     $order
     * @param int        $page
     * @param int        $limit
     * @return array
     */
    static function classlist(
        $catid,
        $filter = [],
        $operator = [],
        $value = [],
        $order = "id DESC",
        $page = 1,
        $limit = 20
    ) {
        //当前栏目信息
        $catInfo = getCategory($catid);
        if (empty($catInfo)) {
            return self::createReturn(false, null, '该栏目不存在');
        }
        //查询条件
        $where = array();
        $where['catid'] = array('EQ', $catid);
        //栏目所属模型
        $modelid = $catInfo['modelid'];
        $tablename = getModel($modelid, 'tablename');
        //实例化模型, 需要在System模块下新建模型
        $model = D('System/' . $tablename);

        if (is_array($filter)) {
            foreach ($filter as $index => $k) {
                if ($value[$index] != '') {
                    $filter[$index] = trim($filter[$index]);
                    $operator[$index] = trim($operator[$index]);
                    $value[$index] = trim($value[$index]);

                    if (empty($where[$filter[$index]])) {
                        $where[$filter[$index]] = [];
                    }
                    if (strtolower($operator[$index]) == 'like') {
                        $condition = array($operator[$index], '%' . $value[$index] . '%');
                    } else {
                        $condition = array($operator[$index], $value[$index]);
                    }
                    $where[$filter[$index]][] = $condition;
                }
            }


        }
        if ($model instanceof RelationModel) {
            //关联查询
            $data = $model->where($where)->page($page)->limit($limit)->order($order)->relation(true)->select();
        } else {
            $data = $model->where($where)->page($page)->limit($limit)->order($order)->select();
        }

        $amount = $model->where($where)->count();

        if (empty($data)) {
            $data = [];
        }
        $ret = [
            'page' => intval($page),
            'limit' => intval($limit),
            'amount' => intval($amount),
            'items' => $data
        ];

        return self::createReturn(true, $ret);
    }

    /**
     * 获取表的内容
     *
     * @param string  $tablename 表名
     * @param array  $filter
     * @param array  $operator
     * @param array  $value
     * @param string $order
     * @param int    $page
     * @param int    $limit
     * @return array
     */
    static function lists(
        $tablename,
        $filter = [],
        $operator = [],
        $value = [],
        $order = '',//"id DESC"
        $page = 1,
        $limit = 20
    ) {
        //查询条件
        $where = array();
        //实例化模型, 需要在System模块下新建模型
        $model = D('System/' . $tablename);

        if (is_array($filter)) {
            foreach ($filter as $index => $k) {
                if ($value[$index] != '') {
                    $filter[$index] = trim($filter[$index]);
                    $operator[$index] = trim($operator[$index]);
                    $value[$index] = trim($value[$index]);

                    if (empty($where[$filter[$index]])) {
                        $where[$filter[$index]] = [];
                    }
                    if (strtolower($operator[$index]) == 'like') {
                        $condition = array($operator[$index], '%' . $value[$index] . '%');
                    } else {
                        $condition = array($operator[$index], $value[$index]);
                    }
                    $where[$filter[$index]][] = $condition;
                }
            }


        }
        if ($model instanceof RelationModel) {
            //关联查询
            $data = $model->where($where)->page($page)->limit($limit)->order($order)->relation(true)->select();
        } else {
            $data = $model->where($where)->page($page)->limit($limit)->order($order)->select();
        }

        $amount = $model->where($where)->count();

        if (empty($data)) {
            $data = [];
        }
        $ret = [
            'page' => intval($page),
            'limit' => intval($limit),
            'amount' => intval($amount),
            'items' => $data
        ];

        return self::createReturn(true, $ret);
    }

}