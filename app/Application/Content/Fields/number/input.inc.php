<?php

/**
 * 数字字段类型数据获取
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return int
 */
function number($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    //小数位
    $decimaldigits = $setting['decimaldigits'];
    //取值范围
    $minnumber = $setting['minnumber'];
    if ($minnumber != '') {
        if ($value < $minnumber) {
            $value = $minnumber;
        }
    }
    $maxnumber = $setting['maxnumber'];
    if ($maxnumber != '') {
        if ($value > $maxnumber) {
            $value = $maxnumber;
        }
    }
    return $value;
}
