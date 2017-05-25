<?php

/**
 * 日期时间字段类型 数据获取
 * @param string $field 字段名
 * @param string $value 字段内容
 * @return int
 */
function datetime($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    if ($setting['fieldtype'] == 'int') {
        if (!is_numeric($value)) {
            $value = strtotime($value);
        }
        if(empty($value)){
            $value = 0;
        }
    }
    return $value;
}