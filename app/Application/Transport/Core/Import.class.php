<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

/**
 * 导入表格
 *
 * @package Transport\Core
 */
class Import {

    //模型名称(一般为不含前缀的表名)
    protected $model = '';

    //字段
    protected $fields = array();

    //文件名
    protected $filename = 'import';

    //导出表格内容
    protected $_content = '';

    protected $tpl_object = [];

    protected $header_fields = [];

    /**
     * 数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * Excel处理器
     *
     * @var null|\PHPExcel
     */
    private $phpexcel = null;
    /**
     * Excel数据
     *
     * @var array
     */
    private $excel_data = [];

    public function __construct() {
        include(APP_PATH . '/Transport/Libs/PHPExcel.php');

        $this->phpexcel = new \PHPExcel();
    }

    /**
     * 导入表格
     */
    function importTable() {
        $headers = $this->importHeaders();
        $header_fields = [];

        //构建单个模型数据属性
        foreach ($headers as $i => $header) {
            foreach ($this->fields as $_i => $field) {
                if ($field->getExportName() == $header) {
                    $this->tpl_object[$field->getFieldName()] = '';
                    $header_fields[] = $field;
                }
            }
        }

        //移除行头
        array_shift($this->excel_data);

        $this->header_fields = $header_fields;
        $this->importRows();

        $this->importData();

    }

    /**
     * 导入数据
     */
    private function importData() {
        $db = M($this->getModel());
        if (!empty($this->data)) {
            foreach ($this->data as $index => $data) {

                if (isset($data[$db->getPk()])) {
                    //有主键
                    $where[$db->getPk()] = $data[$db->getPk()];
                    unset($data[$db->getPk()]);
                    $res = $db->where($where)->save($data);
                } else {
                    $res = $db->add($data);
                }
            }
        }
    }

    /**
     * 处理导入表头
     * @return mixed
     */
    private function importHeaders() {
        return $this->excel_data[0];
    }

    /**
     * 处理导入一个单元格
     * @param ExportField $field
     * @param             $cell_data
     * @return mixed
     */
    private function importCell(ExportField $field, $cell_data) {
        return $field->filterValue($field->getFieldName(), $cell_data, $cell_data);
    }

    /**
     * 处理导入一行
     * @param array $row_data
     * @return array
     */
    private function importRow(array $row_data) {
        $result = $this->tpl_object;
        foreach ($this->header_fields as $index => $field) {
            $result[$field->getFieldName()] = $this->importCell($field, $row_data[$index]);
        }

        return $result;
    }

    /**
     * 处理导入多行
     * @return array
     */
    private function importRows() {
        foreach ($this->excel_data as $index => $row_data) {
            $this->data[] = $this->importRow($row_data);
        }

        return $this->data;
    }


    /**
     * 开始导入
     */
    function import() {

        $this->importTable();
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
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    /**
     * 获取数据
     *
     * @return array|mixed
     */
    private function getImportData() {
        return $this->excel_data;
    }

    /**
     * 设置导入数据
     * @param array $excel_data
     */
    public function setImportData(array $excel_data) {
        $this->excel_data = $excel_data;
    }


}