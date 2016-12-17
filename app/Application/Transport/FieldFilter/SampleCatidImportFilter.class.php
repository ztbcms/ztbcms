<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/**
 * 导入时默认的栏目
 *
 * @package Transport\FieldFilter
 */
class SampleCatidImportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        //网页教程 - HTML/XHTML
        return 10;
    }


}