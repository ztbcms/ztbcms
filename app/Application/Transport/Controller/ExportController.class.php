<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\AdminBase;
use Transport\Core\ExportField;
use Transport\Core\Export;

/**
 * 数据导出
 *
 * @package Transport\Controller
 */
class ExportController extends AdminBase {

    /**
     * 导出栏目列表页的搜索结果
     */
    function classlist(){
        //设置脚本最大执行时间
		set_time_limit(0);
        
        $filter = I('get._filter');
        $operator = I('get._operator');
        $value = I('get._value');

        $where = array();
        if (is_array($filter)) {
            foreach ($filter as $index => $k){
                if( $value[$index] != '' ){
                    $filter[$index] = trim($filter[$index]);
                    $operator[$index] = trim($operator[$index]);
                    $value[$index] = trim($value[$index]);

                    if(empty($where[$filter[$index]])){
                        $where[$filter[$index]] = [];
                    }
                    if(strtolower($operator[$index]) == 'like'){
                        $condition = array($operator[$index], '%' . $value[$index] . '%');
                    }else{
                        $condition = array($operator[$index], $value[$index]);
                    }

                    $where[$filter[$index]][] = $condition;
                }
            }
        }

        //导出
        $export = new Export();
        $export->setFilename('数据导出'.date('Ymdhis')); //导出文件名

        $modelid = getCategory(I('catid'), 'modelid');
        $modelInfo = M('Model')->where(['modelid' => $modelid])->field('tablename')->find();
        $export->setModel($modelInfo['tablename']); //导出模型

        //筛选条件方式一
        $export->setCondition($where);

        $export->setFields([

            new ExportField('id', 'ID' , null),
            new ExportField('title', '标题' , null),
            new ExportField('inputtime', '发布时间' , 'InputtimeFilter'),

        ]);


//        $table = $export->exportTable();
//        echo $table;
        $export->exportXls();
        exit();
    }

}