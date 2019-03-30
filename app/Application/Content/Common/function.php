<?php

// +----------------------------------------------------------------------
// | 内容模块自定义函数
// +----------------------------------------------------------------------

function getBoxOptionsName($value, $catid, $field) {
    $category = getCategory($catid);

    $model = getModel($category['modelid']);
    $tableName = $model['tablename'] . '_box_' . $field;
    $box = M($tableName)->where(['value' => $value])->find();

    return $box['label'];
}