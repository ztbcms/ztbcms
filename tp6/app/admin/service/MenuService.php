<?php
/**
 * Author: Cycle3
 */

namespace app\admin\service;

use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\admin\validate\Menu;
use app\common\libs\helper\TreeHelper;
use app\common\service\BaseService;
use think\exception\ValidateException;

/**
 * 菜单管理
 * Class MenuService
 * @package app\admin\service
 */
class MenuService extends BaseService
{

    /**
     * 获取菜单列表
     * @return array
     */
    static function getMenuList()
    {
        $MenuModel = new MenuModel();
        $menu = $MenuModel->order(array("listorder" => "ASC"))->select()->toArray();
        $menu = TreeHelper::arrayToTreeList($menu, 0, [
            'parentKey' => 'parentid',
        ]);
        return self::createReturn(true, $menu, '获取成功');
    }

    /**
     * 获取详情
     * @param $id
     * @return array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getDetails($id = 0){
        $MenuModel = new MenuModel();
        $info = $MenuModel->where('id', $id)->find();
        $info['parentid'] = (string)$info['parentid'];
        $info['status'] = (string)$info['status'];
        $info['type'] = (string)$info['type'];
        return self::createReturn(true,$info);
    }

    /**
     * 删除菜单详情
     * @param $id
     * @return array
     */
    static function doDelete($id)
    {
        $MenuModel = new MenuModel();
        $count = $MenuModel->where(array("parentid" => $id))->count();
        if ($count > 0) {
            return self::createReturn(false, '', '该菜单下还有子菜单，无法删除！');
        }
        if ($MenuModel->where(['id' => $id])->delete() !== false) {
            return self::createReturn(true, '', '删除菜单成功！');
        } else {
            return self::createReturn(false, '', '删除失败！');
        }
    }

    /**
     * 添加或者编辑菜单
     * @param  array  $posts
     * @return array
     */
    static function addEditDetails($posts = []){
        $MenuModel = new MenuModel();

        try {
            validate(Menu::class)->check($posts);
            if($posts['id']) {
                //编辑菜单
                if ($MenuModel->where('id',$posts['id'])->save($posts) !== false) {
                    return self::createReturn(true,'','操作成功');
                } else {
                    return self::createReturn(false,'',$MenuModel->getError());
                }
            } else {
                //添加菜单
                if($MenuModel->create($posts)) {
                    return self::createReturn(true,'','操作成功');
                } else {
                    return self::createReturn(false,'',$MenuModel->getError());
                }
            }
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return self::createReturn(false,'',$e->getError());
        }
    }


    /**
     * 获取含有层次(level)树状
     * @param  int  $parentid
     * @param  int  $level
     * @param  array  $ret
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getMenuTreeArray($parentid = 0, $level = 0, $ret = []){
        $MenuModel = new MenuModel();
        $menus = $MenuModel->where(['parentid' => $parentid])->order('listorder ASC')->select();
        foreach ($menus as $index => $menu){
            $menu['level'] = $level;

            $ret[] = $menu;
            $children = $MenuModel->where(['parentid' => $menu['id']])->select();
            if($children){
                $ret = self::getMenuTreeArray($menu['id'], $level + 1, $ret);
            }
        }
        return $ret;
    }

    /**
     * 获取角色的权限菜单
     * TODO 优化，不用每次都检测权限
     * @param $role_id
     *
     * @return array
     */
    static function getMenuByRole($role_id){
        // 只获取tp6菜单
        $menuList = MenuModel::order('listorder ASC,id ASC')->select()->toArray();
        $array = [];
        $rbacService = new RbacService();
        foreach ($menuList as $v) {
            //方法
            $app = $v['app'];
            $controller = $v['controller'];
            $action = $v['action'];
            //如果是菜单项
            if ($v['type'] == 0) {
                $controller = $v['controller'].$v['id'];
                $action = $v['action'].$v['id'];
            }
            //是否有权限
            $res = $rbacService->enableRoleAccess($role_id, $app, $controller, $action);
            if ($res['status']) {
                $array[] = $v;
            }
        }
        return self::createReturn(true, $array);
    }
}
