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

    /**
     * 在对象数组中提取某个字段来创建新的数组
     * 示例：
     *  [['uid'=>1],['uid'=>2],['uid'=>3]] === arrayTakeKeyValue(array,'uid') ==> [1,2,3]
     * @param array $array
     * @param $key
     * @return array
     */
    static function arrayTakeKeyValue(array $array, $key)
    {
        $result = [];
        foreach ($array as $item) {
            if (isset($item[$key])) {
                $result[] = $item[$key];
            }
        }
        return $result;
    }
}