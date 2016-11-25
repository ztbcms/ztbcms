<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/*
 * 发布时间转换
 */
class InputtimeFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);


        return date('Y-m-d H:i:s', $value);
    }


}