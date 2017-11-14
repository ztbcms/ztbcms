<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace System\Service;

use Common\Model\RelationModel;

/**
 * Service基类
 *
 * 如果你在写Service，你应该继承这个基类
 */
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
     * 返回列表信息
     *
     * @param $status
     * @param $items
     * @param $page
     * @param $limit
     * @param $total_items
     * @param $total_pages
     * @return array
     */
    protected static function createReturnList($status, $items, $page, $limit, $total_items, $total_pages){
        return [
            'status' => $status,
            'data' => [
                'items' => $items,
                'page' => $page,
                'limit' => $limit,
                'total_items' => $total_items,
                'total_pages' => $total_pages,
            ],
            'msg' => ''
        ];
    }

    /**
     * 获取模型实例
     *
     * @param $tablename
     * @return \Think\Model
     */
    protected static function getModelInstance($tablename){
        if(strpos($tablename, '/') === false){
            //没有自定义域名
            if(self::isExistSystemModel($tablename)){
                $db = D('System/' . $tablename);
            }else{
                $db = D($tablename);
            }
        }else{
            $db = D($tablename);
        }

        return $db;
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

    /**
     * 获取信息
     *
     * @param string $tablename
     * @param array  $where
     * @param bool   $isRelation
     * @return mixed
     */
    protected static function find($tablename = '', $where = [], $isRelation = false) {
        $db = self::getModelInstance($tablename);
        $fields = '';
        //检测是否有指定字段
        if(method_exists($db, '_getEnableFields')){
            $fields = $db->_getEnableFields();
        }

        if($isRelation && $db instanceof RelationModel){
            $result = $db->where($where)->field($fields)->relation(true)->find();
        }else{
            $result = $db->where($where)->field($fields)->find();
        }


        return self::createReturn(true, $result);
    }

    /**
     * 获取列表信息
     *
     * @param string $tablename
     * @param array  $where
     * @param string $order
     * @param int    $page
     * @param int    $limit
     * @param bool   $isRelation 是否开启关联查询
     * @return array
     */
    protected static function select($tablename = '', $where = [], $order = '', $page = 1, $limit = 20, $isRelation = false) {
        $db = self::getModelInstance($tablename);
        $fields = '';
        //检测是否有指定字段
        if(method_exists($db, '_getEnableFields')){
            $fields = $db->_getEnableFields();
        }

        if($isRelation && $db instanceof RelationModel){
            $items = $db->where($where)->order($order)->page($page)->limit($limit)->relation(true)->field($fields)->select();
        }else{
            $items = $db->where($where)->order($order)->page($page)->limit($limit)->field($fields)->field($fields)->select();
        }

        $total_items = $db->where($where)->count();
        $total_pages = ceil($total_items / $limit);
        if (empty($items)) {
            $items = [];
        }

        return self::createReturnList(true, $items, $page, $limit, $total_items, $total_pages);
    }

    /**
     * 添加数据
     *
     * @param string $tablename
     * @param array  $data
     * @return array
     */
    public static function create($tablename = '', $data = []){
        $db = self::getModelInstance($tablename);
        $result = $db->add($data);

        if($result){
            return self::createReturn(true, $result, '操作成功');
        }else{
            return self::createReturn(true, null, '操作失败,错误信息：'. $db->getError());
        }

    }

    /**
     * 删除
     *
     * @param string $tablename
     * @param array  $where
     * @return array
     */
    public static function delete($tablename = '', $where = []){
        $db = self::getModelInstance($tablename);
        $result = $db->where($where)->delete();

        return self::createReturn(true, $result, '操作成功');
    }

    /**
     * 更新数据
     *
     * @param string $tablename
     * @param array  $where
     * @param array  $update_data
     * @return array
     */
    public static function update($tablename = '', $where = [], $update_data = []){
        $db = self::getModelInstance($tablename);
        $result = $db->where($where)->save($update_data);

        return self::createReturn(true, $result, '操作成功');
    }

    /**
     * 判断是否存在 对应的Model在System模块下
     *
     * @param $model_name
     * @return array
     */
    protected static function isExistSystemModel($model_name){
        $model_name = APP_PATH . DIRECTORY_SEPARATOR . 'System' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $model_name . 'Model.class.php';
        if(file_exists($model_name)){
            return self::createReturn(true, null, $model_name . 'Model 存在');
        }

        return self::createReturn(false, null, $model_name . 'Model 不存在');
    }
}