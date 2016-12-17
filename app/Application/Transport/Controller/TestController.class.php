<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\Base;
use Transport\Core\Export;
use Transport\Core\ExportField;
use Transport\Core\ExportFilter;
use Transport\Core\Import;
use Transport\FieldFilter\ContentImportFilter;

/**
 * http://{domain}/?g=transport&m=testExport&a=index
 *
 * @package Transport\Controller
 */
class TestController extends Base {

    protected function _initialize() {
        parent::_initialize();
    }


    function testExport(){

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

        $table = $export->exportTable();
        echo $table;
//        $export->exportXls();
        exit();
    }


    function testImport(){
        $import = new Import();

        $import->setModel('article');

        $import->setFields([
            new ExportField('id', 'ID' , null),
            new ExportField('title', '标题' , null),
            new ExportField('inputtime', '发布时间' , 'InputtimeImportFilter'),
            new ExportField('catid', '栏目ID' , 'SampleCatidImportFilter'),
            new ExportField('status', '审核状态', 'PassStatusImportFilter')
        ]);

        //法一：采用自定义的导入数据方式
//        $import->setImportData([
//            ['栏目ID', '标题'],
//            ['10', '你好，这里是31'],
//            ['10', '你好，这里是32'],
//            ['10', '你好，这里是33'],
//            ['10', '你好，这里是34'],
//        ]);

        //法二： 采用Excel文件导入
        $filename = APP_PATH . '/Transport/Data/数据导出20161213123245.xls';
        $import->setFilename($filename);

        //导入预览
        //$import->exportTable();
        //开始导入
        $import->import();
    }

}