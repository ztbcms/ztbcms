<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/**
 * 栏目ID -> 栏目名
 *
 * @package Transport\FieldFilter
 */
class CatnameExportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        return getCategory($value, 'catname');
    }


}