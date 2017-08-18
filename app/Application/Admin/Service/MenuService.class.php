<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Service;


use System\Service\BaseService;

class MenuService extends BaseService {

    /**
     * 获取含有层次(level)树状
     *
     * @param int   $parentid
     * @param int   $level
     * @param array $ret
     * @return array
     */
    static function getMenuTreeArray($parentid = 0, $level = 0, $ret = []){
        $menus = M('Menu')->where(['parentid' => $parentid])->order('listorder ASC')->select();

        foreach ($menus as $index => $menu){
            $menu['level'] = $level;

            $ret[] = $menu;
            $children = M('Menu')->where(['parentid' => $menu['id']])->select();
            if($children){
                $ret = self::getMenuTreeArray($menu['id'], $level + 1, $ret);
            }
        }

        return $ret;
    }

}