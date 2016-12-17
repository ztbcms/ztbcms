<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

class PassStatusImportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        return 99;
    }


}