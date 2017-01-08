<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\FieldFilter;


use Transport\Core\FieldFilter;

/**
 * userid => username
 *
 * @package Transport\FieldFilter
 */
class UseridExportFilter extends FieldFilter {

    public function filter($field, $value, $row_data) {
        parent::filter($field, $value, $row_data);

        $user = M('member')->where(['userid' => $value])->find();
        return $user['username'];

    }


}