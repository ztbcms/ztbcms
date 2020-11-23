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
     * 格式如：（用children来做层级区分）
     * {
          id:1,
     *    level: 0
     *    children:[ {id:2, level:1, children: []}, {id:3, level:1, children: []} ]
     *  }
     *
     * @param  array  $array  待处理数组
     * @param  int|string  $pid  父节点ID
     * @param  array  $config  配置
     * @param  int  $level
     *
     * @return array 散列数组
     */
    static function arrayToTree(array $array, $pid, $config = [], $level = 0)
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
     * 根据给定的散列数组结构，以列表层级的形式来呈现树状结构(常用于列表页展示树状层级)
     * 格式如：
     * [
     *  {id:1, level: 0, pid: 0},
     *  {id:2, level: 1, pid: 1},
     *  {id:3, level: 1, pid: 1},
     *  {id:4, level: 2, pid: 3},
     * ]

     * @param  array  $array
     * @param $pid
     * @param  array  $config
     * @param  int  $level
     *
     * @return array
     */
    static function arrayToTreeList(array $array, $pid, $config = [], $level = 0)
    {
        static $result_list = [];
        $curConfig = [
            'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
            'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'pid', // 父节点的ID字段名
            'childrenKey' => isset($config['childrenKey']) ? $config['childrenKey'] : 'children', // 子列表的key名
            'maxLevel'    => isset($config['maxLevel']) ? $config['maxLevel'] : 0,// 最大层级，0为不限制。父节点算一层
            'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从0开始
        ];
        foreach ($array as $index => $item) {
            if ($item[$curConfig['parentKey']] == $pid) {
                // 寻找下一级
                if ($curConfig['maxLevel'] === 0 || $level + 1 <= $curConfig['maxLevel']) {
                    if (!empty($curConfig['levelKey'])) {
                        $item[$curConfig['levelKey']] = $level;
                    }
                    $result_list [] = $item;
                    unset($array[$index]);
                    self::arrayToTreeList($array, $item[$curConfig['idKey']], $config, $level + 1);
                }
            }
        }
        return $result_list;
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
            'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从0开始
        ];
        $result_list = [];
        // 下一层待处理列表
        $next_list = $array;
        // 当层的节点ID
        $current_id_list = [$pid];
        $level = 0;
        while (!empty($current_id_list)) {
            $_next_list = [];
            $_current_id_list = [];
            foreach ($next_list as $i => $item) {
                // 注入层级
                if(!empty($curConfig['levelKey'])){
                    $item[$curConfig['levelKey']] = $level;
                }
                if (in_array($item[$curConfig['parentKey']], $current_id_list)) {
                    $_current_id_list [] = $item[$curConfig['idKey']];
                    $result_list [] = $item;
                } else {
                    $_next_list [] = $item;
                }
            }
            $next_list = $_next_list;
            $current_id_list = $_current_id_list;
            $level++;
        }
        return $result_list;
    }

    /**
     * 递归实现无限极分类 根据给定的散列数组结构，整理成树状结构
     *
     * @param $array
     * @param  int  $pid  父ID
     * @param  int  $level  分类级别
     * @return array 分好类的数组 直接遍历即可 $level可以用来遍历缩进
     */
    static function getTreeShapeArray(array $array = [], $pid = 0, $level = 0,$config = [])
    {
        $curConfig = [
            'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
            'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'parentid', // 父节点的ID字段名
            'levelKey'    => isset($config['levelKey']) ? $config['levelKey'] : 'level',// 层级的key名，按从1开始
        ];

        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value) {
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value[$curConfig['parentKey']] == $pid) {
                //父节点为根节点的节点,级别为0，也就是第一级
                $value[$curConfig['levelKey']] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                self::getTreeShapeArray($array, $value[$curConfig['idKey']], $level + 1);
            }
        }
        return $list;
    }
}