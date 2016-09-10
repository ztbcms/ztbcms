<?php

/**
 * 多图片字段类型更新回调
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function images($field, $value) {
    //取得图片列表
    $pictures = $_POST[$field . '_url'];
    //取得图片说明
    $pictures_alt = isset($_POST[$field . '_alt']) ? $_POST[$field . '_alt'] : array();
    $array = $temp = array();
    if (!empty($pictures)) {
        foreach ($pictures as $key => $pic) {
            $temp['url'] = $pic;
            $temp['alt'] = $pictures_alt[$key];
            $array[$key] = $temp;
        }
    }
    $array = serialize($array);
    
    //向提交的表单中注入序列化后的多图片(数据库保存为序列化后的字符串)
    $this->data[$field] = $array;
    
    return $array;
}