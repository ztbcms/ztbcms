<?php

/**
 * 获取单文件上传字段内容
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function downfile($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    if ($setting['downloadlink']) {
        return U('Content/Download/index', array('catid' => $this->catid, 'id' => $this->id, 'f' => $field));
    } else {
        return $value;
    }
}