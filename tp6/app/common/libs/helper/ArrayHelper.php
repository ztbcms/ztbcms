<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\helper;


class ArrayHelper
{
    /**
     * 数组转换为映射表
     *
     * @param $array array 数组
     * @param $key string 指定的key字段
     *
     * @return array
     */
    static function arrayToMap(array $array, $key)
    {
        $result = [];
        foreach ($array as $item) {
            $result[$item[$key]] = $item;
        }
        return $result;
    }
}