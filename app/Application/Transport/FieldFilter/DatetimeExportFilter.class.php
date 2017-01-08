<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/**
 * 时间戳格式化
 *
 * @package Transport\FieldFilter
 */
class DatetimeExportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        return date('Y-m-d H:i:s', $value);
    }


}