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

    //导出文件名
    protected $filename = 'export';

    //导出表格内容
    protected $_content = '';

    /**
     * 源数据
     * @var array
     */
    protected $data = [];

    /**
     * Excel处理器
     * @var null|\PHPExcel
     */
    private $phpexcel = null;
    /**
     * Excel数据
     * @var array
     */
    private $excel_data = [];

    public function __construct() {
        include(APP_PATH . '/Transport/Libs/PHPExcel.php');

        $this->phpexcel = new \PHPExcel();
    }
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
        return $field->getExportName();
    }

    /**
     * 表格头
     *
     * @param $fields array
     * @return string
     */
    function exportHeaders($fields = []) {
        $content_header = '<tr>';
        $excel_headers = [];
        foreach ($fields as $index => $field) {
            $content_header .= '<th>' . $this->exportHeader($field) . '</th>';
            $excel_headers[] = $this->exportHeader($field);
        }

        $content_header .= '</tr>';

        $this->excel_data[] = $excel_headers;
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
        return $field->filterValue($field->getFieldName(), $row_data[$field->getFieldName()],
            $row_data) ;
    }

    /**
     * 渲染一行
     *
     * @param array $row_data
     * @return string
     */
    private function exportRow($row_data = []) {
        $row = '<tr>';
        $fields = $this->getFields();

        $excel_row = [];
        foreach ($fields as $index => $field) {
            $row .= '<td>' . $this->exportCell($field, $row_data). '</td>';
            $excel_row[] = $this->exportCell($field, $row_data);
        }

        $row .= '</tr>';
        $this->excel_data[] = $excel_row;

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
        //先提取数据
        $data = $this->getData();
        if (empty($data)) {
            $data = $this->getExportData();
            $this->setData($data);
        }

        $this->_content .= '<table>';
        $this->_content .= $this->exportHeaders($this->fields);
        $this->_content .= $this->exportRows();
        $this->_content .= '</table>';

        return $this->_content;
    }

    /**
     * 生成 XLS 文件
     */
    function exportXls() {

        $this->exportTable();

        //设置表格
        $this->phpexcel->getProperties()->setCreator($this->filterString)
            ->setLastModifiedBy('ZTBCMS')
            ->setTitle("Office 2007 XLSX Document")
            ->setSubject("Office 2007 XLSX Document")
            ->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("ZTBCMS");

        //填充数据
        foreach ($this->excel_data as $key => $row) {
            $num = $key + 1;
            $i=0;
            foreach ($row as $key2 => $value2) {
                $value2 = ' ' . $value2; //处理XLS自动把该行纯数字并且比较长，自动转为客服计数，会自动补全0
                $this->phpexcel->setActiveSheetIndex(0)->setCellValue( \PHPExcel_Cell::stringFromColumnIndex($i). ($num), $value2);
                $i++;
            }
        }

        //设置表格并输出
        $this->phpexcel->getActiveSheet()->setTitle($this->filename);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$this->filename}.xls");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); // HTTP/1.0
        $objWriter =  \PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
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
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param array $data
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

}