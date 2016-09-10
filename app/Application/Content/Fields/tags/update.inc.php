<?php

/**
 * Tags处理回调
 * @param type $field 字段名
 * @param type $value 字段值
 */
function tags($field, $value) {
    if (!empty($value)) {
        //添加时如果是未审核，直接不处理
        if (ACTION_NAME == 'add' && $this->data['status'] != 99) {
            return false;
        } else if (ACTION_NAME == 'edit' && $this->data['status'] != 99) {
            //如果是编辑状态，且未审核，直接清除已有的tags
            D('Content/Tags')->deleteAll($this->data['id'], $this->data['catid'], $this->modelid);
            return false;
        }
        if (strpos($value, ',') === false) {
            $keyword = explode(' ', $value);
        } else {
            $keyword = explode(',', $value);
        }
        $keyword = array_unique($keyword);
        //新增
        if (ACTION_NAME == 'add') {
            D('Content/Tags')->addTag($keyword, $this->id, $this->catid, $this->modelid, array(
                'url' => $this->data['url'],
                'title' => $this->data['title'],
            ));
        } else {
            D('Content/Tags')->updata($keyword, $this->id, $this->catid, $this->modelid, array(
                'url' => $this->data['url'],
                'title' => $this->data['title'],
            ));
        }
    } else {
        //删除全部tags信息
        D('Content/Tags')->deleteAll($this->id, $this->catid, $this->modelid);
    }
}
