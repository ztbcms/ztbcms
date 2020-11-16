<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\helper;

/**
 * 树状结构工具类
 * Class TreeHelper
 *
 * @package app\common\libs\helper
 */
class TreeHelper
{
    /**
     * 根据给定的散列数组结构，整理成树状结构
     *
     * @param  array  $array  待处理数组
     * @param  int|string  $pid  父节点ID
     * @param  array  $config  配置
     * @param  int  $level
     *
     * @return array 散列数组
     */
    static function arrayToTree(array $array, $pid, $config = [], $level = 1)
    {
        $curConfig = [
            'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
            'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'pid', // 父节点的ID字段名
            'childrenKey' => isset($config['childrenKey']) ? $config['childrenKey'] : 'children', // 子列表的key名
            'maxLevel'    => isset($config['maxLevel']) ? $config['maxLevel'] : 0,// 最大层级，0为不限制。父节点算一层
            'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从1开始
        ];
        $nodeList = [];
        foreach ($array as $index => $item) {
            if ($item[$curConfig['parentKey']] == $pid) {
                // 寻找下一级
                if ($curConfig['maxLevel'] === 0 || $level + 1 <= $curConfig['maxLevel']) {
                    if (!empty($curConfig['levelKey'])) {
                        $item[$curConfig['levelKey']] = $level;
                    }
                    $item[$curConfig['childrenKey']] = self::arrayToTree($array, $item[$curConfig['idKey']], $config, $level + 1);
                }
                $nodeList[] = $item;
            }
        }
        return $nodeList;
    }

    /**
     * 树状结构数组转为散列数组
     *
     * @param  array  $tree_array  树状结构数组
     * @param  array  $config  配置
     *
     * @return array
     */
    static function treeToArray(array $tree_array, $config = [])
    {
        $curConfig = [
            'childrenKey' => isset($config['childrenKey']) ? $config['childrenKey'] : 'children', // 子列表的key名
        ];
        $result_list = [];
        while (!empty($tree_array)) {
            // 下一层待处理列表
            $next_list = [];
            foreach ($tree_array as $i => $item) {
                if (!empty($item[$curConfig['childrenKey']])) {
                    foreach ($item[$curConfig['childrenKey']] as $j => $value) {
                        $next_list [] = $value;
                    }
                }
                unset($item[$curConfig['childrenKey']]);
                $result_list [] = $item;
            }
            $tree_array = $next_list;
        }

        return $result_list;
    }

    /**
     * 从散列表中获取子节点
     *
     * @param  array  $array
     * @param  int  $pid
     * @param  array  $config
     *
     * @return array
     */
    static function getSonNodeFromArray(array $array = [], $pid = 0, $config = [])
    {
        $curConfig = [
            'idKey'     => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
            'parentKey' => isset($config['parentKey']) ? $config['parentKey'] : 'pid', // 父节点的ID字段名
        ];
        $result_list = [];
        // 下一层待处理列表
        $next_list = $array;
        // 当层的节点ID
        $current_id_list = [$pid];
        while (!empty($current_id_list)) {
            $_next_list = [];
            $_current_id_list = [];
            foreach ($next_list as $i => $item) {
                if (in_array($item[$curConfig['parentKey']], $current_id_list)) {
                    $_current_id_list [] = $item[$curConfig['idKey']];
                    $result_list [] = $item;
                } else {
                    $_next_list [] = $item;
                }
            }
            $next_list = $_next_list;
            $current_id_list = $_current_id_list;
        }
        return $result_list;
    }
}