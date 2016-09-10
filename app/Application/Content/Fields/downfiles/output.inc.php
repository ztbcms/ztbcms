<?php

/**
 * 获取多文件上传内容
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function downfiles($field, $value) {
    $setting = unserialize($this->fields[$field]['setting']);
    $list_str = array();
    $file_list = unserialize($value);
    if (is_array($file_list)) {
        foreach ($file_list as $_k => $_v) {
            if ($_v['fileurl']) {
                if ($setting['downloadlink']) {
                    //链接到跳转页面
                    $fileurl = U('Content/Download/index', array('catid' => $this->catid, 'id' => $this->id, 'f' => $field, 'k' => $_k));
                } else {
                    $fileurl = $_v['fileurl'];
                }
                $filename = $_v['filename'] ? $_v['filename'] : "点击下载";
                $groupid = $_v['groupid'] ? $_v['groupid'] : 0;
                $point = $_v['point'] ? $_v['point'] : 0;
                $list_str[$_k]['fileurl'] = $fileurl;
                $list_str[$_k]['filename'] = $filename;
                $list_str[$_k]['groupid'] = $groupid;
                $list_str[$_k]['point'] = $point;
            }
        }
    }
    return $list_str;
}