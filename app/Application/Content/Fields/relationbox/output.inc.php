<?php

/**
 * 选项字段类型内容获取
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return string
 */
function box($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    if ($setting['outputtype']) {
        return $value;
    } else {
        $options = explode("\n", $setting['options']);
        foreach ($options as $_k) {
            $v = explode("|", $_k);
            $k = trim($v[1]);
            $option[$k] = $v[0];
        }
        $string = '';
        switch ($setting['boxtype']) {
            case 'radio':
                $string = $option[$value];
                break;
            case 'checkbox':
                $value_arr = explode(',', $value);
                foreach ($value_arr as $_v) {
                    if ($_v)
                        $string .= $option[$_v] . ' 、';
                }
                break;
            case 'select':
                $string = $option[$value];
                break;
            case 'multiple':
                $value_arr = explode(',', $value);
                foreach ($value_arr as $_v) {
                    if ($_v)
                        $string .= $option[$_v] . ' 、';
                }
                break;
        }
        return $string;
    }
}