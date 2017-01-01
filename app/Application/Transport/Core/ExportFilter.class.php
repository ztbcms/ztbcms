<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Core;

/**
 * 导入导出筛选条件
 *
 * @package Transport\Core
 */
class ExportFilter {

    private $field;

    private $operator;

    private $value;

    public function __construct($field, $operator, $value) {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;

    }

    /**
     * @return mixed
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field) {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getOperator() {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator) {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value) {
        $this->value = $value;
    }




}
