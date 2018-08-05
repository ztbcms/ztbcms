<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Log\Controller;


use Common\Controller\AdminBase;
use Log\Service\LogService;
use Log\Model\LogLogModel;

class IndexController extends AdminBase {

    // 日志列表
    public function index() {
        $this->display();
    }

    /**
     * 获取日志列表信息
     */
    public function getLogs() {
        //按类别搜索时的类别关键字
        $category = I('category');
        //设置时间范围，从 $start_date 到 $end_date
        $start_date = I('start_date');
        $end_date = I('end_date');
        //指定获取分页结果的第几页
        $page = I('page', 1);
        $limit = I('limit', 20);
        //按内容搜索时的日志内容关键字
        $message = I('message');

        $data = LogService::getLogs($category, $start_date, $end_date, $page, $limit, $message);
        //返回数据
        $this->ajaxReturn(self::createReturn(true, $data));
    }

}
