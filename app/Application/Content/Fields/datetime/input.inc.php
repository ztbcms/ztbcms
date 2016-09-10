<?php

/**
 * 日期时间字段类型 数据获取
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function datetime($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    if ($setting['fieldtype'] == 'int') {
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }
    }
    return $value;
}