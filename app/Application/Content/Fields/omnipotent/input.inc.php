<?php

/**
 * 万能字段字段类型数据获取
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return int
 */
function omnipotent($field, $value) {
    if (empty($value)) {
        return $value;
    }
    //字段配置
    $setting = unserialize($this->fields[$field]['setting']);
    if (in_array($setting['fieldtype'], array('text', 'mediumtext', 'longtext'))) {
        //如果值提交的是数组，进行序列化
        if ($value && is_array($value)) {
            $value = serialize($value);
        }
    }
    return $value;
}
