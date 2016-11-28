<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\Base;
use Transport\Core\Export;
use Transport\Core\ExportField;
use Transport\Core\ExportFilter;

/**
 * http://{domain}/?g=transport&m=test&a=index
 *
 * @package Transport\Controller
 */
class TestController extends Base {


    function index(){

        $export = new Export();

        $export->setFilename('所有文章'); //导出文件名
        $export->setModel('article'); //导出模型

        //筛选条件方式一
        $export->setCondition([
            'status' => ['EQ', '99']
        ]);

        //筛选条件方式二： SQL形式
//        $export->setFilterString('order_status = 2');

        $export->setFields([
            new ExportField('id', 'ID' , null),
            new ExportField('title', '标题' , null),
            new ExportField('inputtime', '发布时间' , 'InputtimeFilter'),

        ]);

        $export->setTableStyle('border: 1px solid red');
        $export->setTableTdStyle('border: 1px solid black');
        $export->setTableThStyle('border: 1px solid green');


        $table = $export->exportTable();
        echo $table;
//        $export->exportXls();
        exit();
    }

}