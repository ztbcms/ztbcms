<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Area\Model;

/**
 * 市级别, 如北京市, 广东佛山
 *
 * @package Area\Model
 */
class CityModel extends BaseModel{

    protected $tableName = 'area_city';


    public function getCitiesByProvinceId($id) {
        return $this->get($id, self::LEVEL_CITY);
    }



}