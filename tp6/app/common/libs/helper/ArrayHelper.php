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

    /**
     * 对数组按指定字段升序排序
     * @param $data
     * @param $key
     * @return void
     */
    static function sortByKey(&$data, $key)
    {
        usort($data, function ($a, $b) use ($key) {
            return $a[$key] <=> $b[$key];
        });
    }
    /**
     * 对一组数组根据指定的key进行去重
     * 示例：（按 uid 去重）
     * $a = [['uid'=>1,'name'=>'1'],['uid'=>2,'name'=>'2'],['uid'=>1,'name'=>'1']];
     * array_unique_by_key($a, 'uid') ==> [['uid'=>1,'name'=>'1'],['uid'=>2,'name'=>'2']]
     * @param mixed $list
     * @param mixed $key
     * @return array
     */
    static function array_unique_by_key($list, $key)
    {
        $map = [];
        $result = [];
        foreach ($list as $item) {
            if (isset($map[$item[$key]])) {
                continue;
            }
            $result[] = $item;
            $map[$item[$key]] = true;
        }
        return $result;
    }
}
