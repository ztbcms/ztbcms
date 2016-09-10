<?php

/**
 * 万能字段字段类型内容获取
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return string
 */
function omnipotent($field, $value) {
    if (empty($value)) {
        return $value;
    }
    //字段配置
    $setting = unserialize($this->fields[$field]['setting']);
    if (in_array($setting['fieldtype'], array('text', 'mediumtext', 'longtext'))) {
        $_value = unserialize($value);
        if ($value && $_value) {
            $value = $_value;
        }
    }
    return $value;
}
