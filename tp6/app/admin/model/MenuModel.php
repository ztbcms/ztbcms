<?php
/**
 * Author: jayinton
 */

namespace app\admin\model;

use app\admin\service\MenuService;
use app\common\libs\helper\TreeHelper;
use app\common\service\BaseService;
use think\facade\Db;
use think\Model;

class MenuModel extends Model
{
    protected $name = 'menu';

    // 菜单类型
    // 无权限菜单
    const TYPE_MENU = 0;
    // 权限菜单
    const TYPE_PERMISSION_MENU = 1;

    // 展示菜单状态
    // 不展示
    const STATUS_UNSHOW = 0;
    // 展示
    const STATUS_SHOW = 1;

    /**
     * 获取后台管理员的菜单
     *
     * @param int|string $role_id 角色ID
     *
     * @return array
     */
    function getAdminUserMenuTree($role_id){
        //获取角色的所有权限菜单
        $menuList = MenuService::getMenuByRole($role_id)['data'];

        $list = [];
        // 数据格式化
        foreach ($menuList as $a) {
            if($a['status'] == MenuModel::STATUS_UNSHOW){
                continue;
            }
            $id = $a['id'];
            $name = $a['app'];
            $controller = $a['controller'];
            $action = $a['action'];
            //附带参数
            $fu = "";
            if ($a['parameter']) {
                $fu = "?" . $a['parameter'];
            }
            if (!empty($a['is_tp6'])) {
                //如果是tp6 返回 /home/module/controller/action 格式 TODO: 统一路由
                $url = build_url("/{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true);
            } else {
                $url = url("{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true)->build();
            }
            $array = array(
                "icon" => $a['icon'],
                "id" => $id . $name,
                "pid" => $a['parentid'],
                "name" => $a['name'],
                "url" => $url,
                "path" => "/{$id}{$name}/{$controller}/{$action}",
            );

            $list [] = $array;
        }

        return TreeHelper::arrayToTree($list, 0, [
            'parentKey' => 'pid',
            'childrenKey' => 'items',
            'maxLevel' => 3 //后台管理界面只支持三层，超出的不层级的不显示
        ]);
    }

    /**
     * 模块安装时进行菜单注册
     * @param array $data 菜单数据
     * @param array $config 模块配置
     * @param int $parentid 父菜单ID
     * @return array
     */
    function installModuleMenu(array $data, array $config, $parentid = 0)
    {
        if (empty($data) || !is_array($data)) {
            return BaseService::createReturn(false, null, '没有数据');
        }

        if (empty($config)) {
            return BaseService::createReturn(false, null, '模块配置信息为空');
        }
        //默认安装时父级ID
        $localMenu = Db::name('menu')->where([['app', '=', 'Admin'], ['controller', '=', 'Module'], ['action', '=', 'local']])->findOrEmpty();
        $defaultMenuParentid = $localMenu && isset($localMenu['id']) ? $localMenu['id'] : 42;
        foreach ($data as $index => $rs) {
            if (empty($rs['route'])) {
                return BaseService::createReturn(false, null, '菜单信息配置有误，route 不能为空');
            }
            $route = $this->menuRoute($rs['route']);
            $pid = $parentid ?: ((is_null($rs['parentid']) || !isset($rs['parentid'])) ? (int)$defaultMenuParentid : $rs['parentid']);
            $newData = array_merge(array(
                'name' => $rs['name'],
                'parentid' => $pid,
                'type' => isset($rs['type']) ? $rs['type'] : 1,
                'status' => isset($rs['status']) ? $rs['status'] : 0,
                'remark' => $rs['remark'] ?: '',
                'listorder' => isset($rs['listorder']) ? $rs['listorder'] : 0,
                'parameter' => isset($rs['parameter']) ? $rs['parameter'] : '',
                'icon' => isset($rs['icon']) ? $rs['icon'] : '',
                'is_tp6' => isset($rs['is_tp6']) ? $rs['is_tp6'] : '1',//默认是tp6
            ), $route);
            $newId = Db::name('menu')->insertGetId($newData);
            if (!$newId) {
                return BaseService::createReturn(false, null, 'Menu 安装异常，请检查菜单配置');
            }
            //是否有子菜单
            if (!empty($rs['child'])) {
                $this->installModuleMenu($rs['child'], $config, $newId);
            }
        }
        return BaseService::createReturn(true, null, '安装菜单完成');
    }

    /**
     * 把模块安装时，Menu.php中配置的route进行转换
     * @param $route
     * @return array
     */
    private function menuRoute($route)
    {
        $route = explode('/', $route, 3);
        if (count($route) < 3) {
            array_unshift($route, $route[0]);
        }
        $data = array(
            'app' => $route[0],
            'controller' => $route[1],
            'action' => $route[2],
            'is_tp6' => 1 // tp6模块的标示字段
        );
        return $data;
    }
}