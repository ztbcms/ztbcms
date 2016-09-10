<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Area\Model;

/**
 * 省份级别,如北京, 广东省
 *
 * @package Area\Model
 */
class ProviceModel extends BaseModel{

    protected $tableName = 'area_province';

    public function getProvinces() {
        return $this->get(0, self::LEVEL_PROVINCE);
    }


}