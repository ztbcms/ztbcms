<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\helper;

/**
 * 树状结构
 * Class TreeHelper
 *
 * @package app\common\libs\helper
 */
class TreeHelper
{
    static function arrayToTree($array, $pid, $config, $level = 1)
    {
        $curConfig = [
            'idKey'       => isset($config['idKey']) ? $config['idKey'] : 'id',// 节点的ID字段名
            'parentKey'   => isset($config['parentKey']) ? $config['parentKey'] : 'pid', // 父节点的ID字段名
            'childrenKey' => isset($config['childrenKey']) ? $config['childrenKey'] : 'children', // 子列表的key名
            'maxLevel'    => isset($config['maxLevel']) ? $config['maxLevel'] : 0,// 最大层级，0为不限制。父节点算一层
        ];
        $children = [];

        foreach ($array as $index => $item) {
            if ($item[$curConfig['parentKey']] == $pid) {
                // 寻找下一级
                if ($curConfig['maxLevel'] === 0 || $level + 1 <= $curConfig['maxLevel']) {
                    $item[$curConfig['childrenKey']] = self::arrayToTree($array, $item[$curConfig['idKey']], $config, $level + 1);
                }

                $children[] = $item;
            }
        }

        return $children;

    }

    static function treeToArray()
    {

    }
}