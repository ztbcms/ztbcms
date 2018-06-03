<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Content\Controller;

use Common\Controller\AdminBase;

/**
 * 数据库表字段导出
 */
class FieldExportController extends AdminBase {

    /**
     * 导出字段页
     */
    function exportModelFields(){
        $this->display();
    }

    /**
     * 获取模型字段导出数据
     */
    private function _getModelExportInfo($modelid){
        $where['modelid'] = $modelid;
        $where['issystem'] = 1;//主表
        $where['disabled'] = 0;//已启用

        $db = D("Content/ModelField");
        $fields = $db->where($where)->field('modelid,field,name,formtype,tips,setting')->select();
        $fields = empty($fields) ? [] : $fields;
        foreach ($fields as $index => $field){
            $setting = unserialize($field['setting']);
            $fields[$index]['type'] = $this->_getTypeByFromtype($field['formtype'], $setting);
            unset($fields[$index]['formtype']);
        }

        $tableInfo = M("Model")->where(array("modelid" => $modelid))->field('name,tablename,modelid')->find();

        $result = [
            'tablename' => C('DB_PREFIX').$tableInfo['tablename'],
            'table_name' => $tableInfo['name'],
            'fields' => $fields
        ];

        return $result;
    }


    /**
     * 根据表单字段类型获取对应的数类型
     * @param $formtype
     * @return string
     */
    private function _getTypeByFromtype($formtype, $setting = []){
        switch ($formtype){
            case 'author':
            case 'box':
            case 'copyfrom':
            case 'downfile':
            case 'downfiles':
            case 'editor':
            case 'image':
            case 'images':
            case 'keyword':
            case 'omnipotent':
            case 'pages':
            case 'posid':
            case 'tags':
            case 'template':
            case 'text':
            case 'textarea':
            case 'title':
            case 'typeid':
                return 'string';
            case 'islink':
            case 'catid':
            case 'datetime':
                return 'int';
            case 'number':
                if($setting['decimaldigits'] == 0){
                    return 'int';
                }else{
                    return 'float';
                }
        }
        return '';
    }

    /**
     * 获取模型导出数据
     *
     * 参数： modelid 为空的时候，获取全部
     */
    function getExportModelFieldsInfo(){
        $modelid = I('modelid', '');
        $result = [];
        if(!$modelid){
            //全部自定义的模型
            $models = M("Model")->where(array("type" => 0))->field('name,tablename,modelid')->select();
            foreach ($models as $index => $model){
                $result []= $this->_getModelExportInfo($model['modelid']);
            }
        }else {
            $result []= $this->_getModelExportInfo($modelid);
        }

        $this->ajaxReturn(self::createReturn(true, $result));
    }

    /**
     * 手动填写表名导出
     */
    function exportTableFields(){
        $this->display();
    }

    /**
     * 获取单表的字段信息
     */
    function getExportTableFieldsInfo(){
        $table = trim(I('tablename'));
        $result = [];
        if(empty($table)){
            $this->ajaxReturn(self::createReturn(false, null, '请输入表名'));
            return;
        }

        $isTable = M()->query("SHOW TABLES LIKE '{$table}'");
        if(empty($isTable)) {
            $this->ajaxReturn(self::createReturn(false, null, '您输入的表不存在'));
            return;
        }

        $dbName = C('DB_NAME');
        $sql = "select *  from information_schema.TABLES as a  where a.TABLE_SCHEMA = '{$dbName}' and a.TABLE_NAME= '{$table}' ";
        $tableInfos = M(trim(I('table')))->query($sql);
        $tableInfo = $tableInfos[0];
        $sql = "select a.column_name,a.data_type,a.CHARACTER_MAXIMUM_LENGTH,a.column_comment from information_schema.COLUMNS as a  where a.TABLE_SCHEMA = '{$dbName}' and a.TABLE_NAME= '{$table}' ";
        $fields = M(trim(I('table')))->query($sql);

        $result_fields = [];
        foreach ($fields as $index => $field){
            $result_fields []= [
                'field' => $field['column_name'],
                'name' => $field['column_comment'],
                'type' => $field['data_type'],
                'tips' => '/',
            ];
        }

        $result []= [
            'tablename' => $table,
            'table_name' => $tableInfo['table_comment'],
            'fields' => $result_fields
        ];

        $this->ajaxReturn(self::createReturn(true, $result));
    }

}