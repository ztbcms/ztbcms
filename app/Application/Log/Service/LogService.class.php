<?php

// +----------------------------------------------------------------------
// | 日志系统
// +----------------------------------------------------------------------

namespace Log\Service;

use Log\Model\LogLogModel;
use System\Service\BaseService;

/**
 * 日志服务
 */
class LogService extends BaseService{

    /**
     * 添加日志
     *
     * @param string $category 日志类别
     * @param string $message 日志信息
     */
    static function log($category = '', $message = ''){
        $db = D('Log/Log');

        $data = [
            'category' => $category,
            'message' => $message,
            'inputtime' => time(),
        ];

        $insertid = $db->add($data);
        if($insertid > 0){
            self::createReturn(true, '', '添加日志成功');
        }else{
            self::createReturn(false, '', '添加日志失败');
        }
    }

    /**
     * 根据传入的：类别关键字，起始日期，结束日期，指定页码，指定记录数获取数据
     * @param string $category      类别关键词
     * @param string $start_date    起始日期 ，格式：2018-01-01
     * @param string $end_date      结束日期 ，格式：2018-01-01
     * @param int    $page          指定的分页页码
     * @param int    $limit         指定显示的记录条数
     * @param string $message       指定
     * @return array
     */
    public static function getLogs($category = '', $start_date = '', $end_date = '', $page = 1, $limit = 20, $message = '')
    {
        $db = D('Log/Log');
        //初始化条件数组
        $where = array();

        if (!empty($category)) {
            $where['category'] = array('LIKE', '%'.$category.'%');
        }
        //
        if(!empty($start_date)){
            //将输入的起始和结束时间转换成时间戳
            $start_date = strtotime($start_date);
            $where['inputtime'] = array('EGT', $start_date);
        }

        if(!empty($end_date)){
            //这里是下面的计算是因为单单转换"结束日期"为时间戳的话，并不会包括"结束日期"的那一天
            $end_date = strtotime($end_date) + 24 * 60 * 60 - 1;
            if(isset($where['inputtime'])){
                $where['inputtime'] = array($where['inputtime'], array('ELT', $end_date), 'AND');
            }else{
                $where['inputtime'] = array('ELT', $end_date);
            }

        }

        if (!empty($message)) {
            $where['message'] = array('LIKE', '%' . $message . '%');
        }

        //获取总记录数
        $count = $db->where($where)->count();
        //总页数
        $total_page = ceil($count / $limit);
        //获取到的分页数据
        $Logs = $db->where($where)->page($page)->limit($limit)->order(array("id" => "desc"))->select();
        $data = [
            'items' => $Logs,
            'page' => $page,
            'limit' => $limit,
            'total_page' => $total_page,
        ];

        return $data;
    }
}