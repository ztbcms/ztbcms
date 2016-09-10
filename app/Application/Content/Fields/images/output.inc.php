<?php

/**
 * 获取字段内容处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function images($field, $value) {
    return unserialize($value);
}