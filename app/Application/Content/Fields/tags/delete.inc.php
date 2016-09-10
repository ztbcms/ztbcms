<?php

/**
 * 删除内容时，回调进行tags处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @return type
 */
function tags($field, $value) {
    //删除对应的tags记录
    return D('Content/Tags')->deleteAll($this->id, $this->catid, $this->modelid);
}