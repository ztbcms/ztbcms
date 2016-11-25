<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

/**
 * 导出字段
 *
 * @package Transport\Core
 */
class ExportField {

    private $fieldName = '';
    private $exportName = '';
    private $filter = '';

    /**
     * ExportField constructor.
     *
     * @param $fieldName string
     * @param $exportName string
     * @param $filter string
     */
    public function __construct($fieldName, $exportName, $filter) {
        $this->fieldName = $fieldName;
        $this->exportName = $exportName;
        $this->filter = $filter;
    }

    public function filterValue($key, $value, $row_data){
        $cls = '\\Transport\\FieldFilter\\' . $this->filter;

        if(!class_exists($cls)){
            return $value;
        }

        $FieldFilter = new $cls;
        return $FieldFilter->filter($key, $value, $row_data);
    }


    /**
     * @return mixed
     */
    public function getFieldName() {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     */
    public function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
    }

    /**
     * @return mixed
     */
    public function getExportName() {
        return $this->exportName;
    }

    /**
     * @param mixed $exportName
     */
    public function setExportName($exportName) {
        $this->exportName = $exportName;
    }

    /**
     * @return mixed
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }



}