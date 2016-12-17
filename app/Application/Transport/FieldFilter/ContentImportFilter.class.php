<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

class ContentImportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        if(empty($value)){
            return ' ';
        }

        return $value;
    }


}