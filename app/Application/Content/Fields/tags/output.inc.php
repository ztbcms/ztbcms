<?php

/**
 * 输出tags内容
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function tags($field, $value) {
    if (empty($value)) {
        return array();
    }
    //把Tags进行分割成数组
    $tags = strpos($value, ',') !== false ? explode(',', $value) : explode(' ', $value);
    $return = array();
    foreach ($tags as $k => $v) {
        $url = CMS()->Url->tags($v);
        $return[$k] = array(
            'url' => $url['url'],
            'tag' => $v,
        );
    }
    return $return;
}
