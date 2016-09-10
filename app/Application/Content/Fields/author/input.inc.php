<?php

/**
 * 作者字段类型表单获取数据处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return string 字段内容
 */
function author($field, $value) {
    return \Input::forTag($value);
}
