<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;


class Export {

    //导出模型名称(一般为不含前缀的表名)
    protected $model = '';

    //导出字段
    protected $fields = array();

    //条件筛选
    protected $condition = array();
    protected $filterString = ''; //sql


    //样式
    protected $table_style = 'border: 1px solid black';
    protected $table_tr_style = 'border: 1px solid black';
    protected $table_th_style = 'border: 1px solid black';
    protected $table_td_style = 'border: 1px solid black';

    //导出文件名
    protected $filename = 'export';

    //导出表格内容
    protected $_content = '';

    //
    protected $data = [];

    /**
     * 获取数据筛选条件
     *
     * @return array
     */
    private function getConditions() {
        return $this->getCondition();
    }

    /**
     * 获取数据
     *
     * @return array|mixed
     */
    private function getExportData() {
        $filter = $this->getConditions();
        $filterString = $this->getFilterString();
        $db = M($this->getModel())->where($filter);

        if(!empty($filterString)){
            $db = $db->where($filter);
        }
        $data = $db->select();

        if (empty($data)) {
            return [];
        }

        return $data;
    }


    /**
     * 表格头单列渲染
     *
     * @param $field ExportField
     * @return string
     */
    private function exportHeader($field) {
        return '<th style="' . $this->table_th_style. '">' . $field->getExportName() . '</th>';
    }

    /**
     * 表格头
     *
     * @param $fields array
     * @return string
     */
    function exportHeaders($fields = []) {
        $content_header = '<tr style="' . $this->table_tr_style . '">';
        foreach ($this->fields as $index => $field) {
            $content_header .= $this->exportHeader($field);
        }

        $content_header .= '</tr>';


        return $content_header;
    }

    /**
     * 渲染一格
     *
     * @param ExportField $field
     * @param array       $row_data
     * @return string
     */
    private function exportCell(ExportField $field, $row_data) {
        return '<td style="' . $this->table_td_style .'">' . $field->filterValue($field->getFieldName(), $row_data[$field->getFieldName()],
            $row_data) . '</td>';
    }

    /**
     * 渲染一行
     *
     * @param array $row_data
     * @return string
     */
    private function exportRow($row_data = []) {
        $row = '<tr style="' . $this->table_tr_style .'">';
        $fields = $this->getFields();

        foreach ($fields as $index => $field) {
            $row .= $this->exportCell($field, $row_data);
        }

        $row .= '</tr>';

        return $row;
    }

    /**
     * 渲染行
     *
     * @return string
     */
    private function exportRows() {
        $content_rows = '';
        $data = $this->getData();
        if (empty($data)) {
            $data = $this->getExportData();
            $this->setData($data);
        }
        foreach ($data as $index => $row_data) {
            $content_rows .= $this->exportRow($row_data);
        }

        return $content_rows;
    }

    /**
     * 渲染整个表格
     *
     * @return string
     */
    function exportTable() {
        $this->_content .= '<table style="' . $this->table_style . '">';
        $this->_content .= $this->exportHeaders($this->fields);
        $this->_content .= $this->exportRows();
        $this->_content .= '</table>';

        return $this->_content;
    }

    /**
     * 生成 XLS 文件
     */
    function exportXls() {
        //申明头部，生成excel类型文件
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header("Content-Disposition:filename=" . $this->filename . ".xls");

        $table = $this->exportTable();
        $tempalte = $this->getTemplate();
        echo str_replace('{{table}}', $table, $tempalte);
        exit();
    }

    /**
     * Execl 模板，解决部分情况下出现中文乱码问题
     * @return string
     */
    private function getTemplate(){
        $tpl = <<<EOT
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
xmlns:x="urn:schemas-microsoft-com:office:excel" 
xmlns="http://www.w3.org/TR/REC-html40"> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html> 
 <head> 
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" /> 
 </head> 
 <body> 
     <div align=center x:publishsource="Excel"> 
         {{table}}
     </div> 
 </body> 
</html>
EOT;
        return $tpl;
    }


    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields) {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getTableStyle() {
        return $this->table_style;
    }

    /**
     * @param string $table_style
     */
    public function setTableStyle($table_style) {
        $this->table_style = $table_style;
    }

    /**
     * @return string
     */
    public function getTableTrStyle() {
        return $this->table_tr_style;
    }

    /**
     * @param string $table_tr_style
     */
    public function setTableTrStyle($table_tr_style) {
        $this->table_tr_style = $table_tr_style;
    }

    /**
     * @return string
     */
    public function getTableTdStyle() {
        return $this->table_td_style;
    }

    /**
     * @param string $table_td_style
     */
    public function setTableTdStyle($table_td_style) {
        $this->table_td_style = $table_td_style;
    }

    /**
     * @return string
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model) {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getFilterString() {
        return $this->filterString;
    }

    /**
     * @param string $filterString
     */
    public function setFilterString($filterString) {
        $this->filterString = $filterString;
    }

    /**
     * @return array
     */
    public function getCondition() {
        return $this->condition;
    }

    /**
     * @param array $condition
     */
    public function setCondition($condition) {
        $this->condition = $condition;
    }
    /**
     * @return string
     */
    public function getTableThStyle() {
        return $this->table_th_style;
    }

    /**
     * @param string $table_th_style
     */
    public function setTableThStyle($table_th_style) {
        $this->table_th_style = $table_th_style;
    }


}