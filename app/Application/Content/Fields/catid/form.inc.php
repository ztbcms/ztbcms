<?php

/**
 * 栏目字段
 * @param type $field 字段名
 * @param type $value 字段值
 * @param type $fieldinfo 该字段的配置信息
 * @return type
 */
function catid($field, $value, $fieldinfo) {
    if (empty($value)) {
        //当值为空时，获取当前添加的栏目ID
        $value = $this->catid;
    }
    //后台管理员搞高级选项
    $publish_str = '';
    if (ACTION_NAME == 'add' && defined("IN_ADMIN") && IN_ADMIN) {
        $publish_str = "<a href='javascript:;' onclick=\"omnipotent('selectid','" . U("Content/Content/public_othors", array("catid" => $this->catid)) . "','同时发布到其他栏目',1);return false;\" style='color:#B5BFBB'>[同时发布到其他栏目]</a>
            <ul class='three_list cc' id='add_othors_text'></ul>";
    }
    $publish_str = '<input type="hidden" name="info[' . $field . ']" value="' . $value . '"/>' . getCategory($value, 'catname') . $publish_str;
    return $publish_str;
}
