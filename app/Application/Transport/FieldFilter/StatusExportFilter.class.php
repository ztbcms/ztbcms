<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/**
 * 状态转换
 *
 * @package Transport\FieldFilter
 */
class StatusExportFilter extends FieldFilter {


    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        if($value == 99){
            return '审核通过';
        }
        if($value == 1){
            return '待审核';
        }
        if($value == 0){
            return '审核未通过';
        }
        return '无';
    }


}