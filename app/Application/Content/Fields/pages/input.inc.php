<?php

/**
 * 分页选择方式字段处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return int
 */
function pages($field, $value) {
    $this->infoData[$this->ContentModel->getRelationName()]['paginationtype'] = !isset($value['paginationtype']) ? 2 : $value['paginationtype'];
    $this->infoData[$this->ContentModel->getRelationName()]['maxcharperpage'] = empty($value['maxcharperpage']) ? 10000 : $value['maxcharperpage'];
    return $value;
}