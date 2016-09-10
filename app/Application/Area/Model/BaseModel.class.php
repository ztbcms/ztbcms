<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Area\Model;


use Think\Model;

/**
 * 区域模型
 *
 * @package Area\Model
 */
class BaseModel extends Model {



    const LEVEL_PROVINCE = '1';//省份
    const LEVEL_CITY = '2';//市
    const LEVEL_DISTRICT = '3';//县区
    const LEVEL_STREET = '4';//街道





    /**
     * @param int    $parentid
     * @param string $level
     * @return array
     */
    protected function get($parentid = 0, $level = '') {
        $condition = array();
        if (!empty($parentid)) {
            $condition['parentid'] = $parentid;
        }
        if (!empty($level)) {
            $condition['level'] = $level;
        }

        $result = $this->where($condition)->order('sort ASC')->select();
//        echo $this->getLastSql();
        if (empty($result)) {
            $result = array();
        }

        return $result;
    }

}