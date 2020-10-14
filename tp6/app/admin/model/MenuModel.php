<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;

use app\common\service\BaseService;
use think\facade\Db;
use think\Model;

class MenuModel extends Model
{
    protected $name = 'menu';

    /**
     * 获取后台管理员的菜单
     *
     * @param $role_id
     * @param  int $parentid
     * @param  int $level
     *
     * @return array
     */
    function getAdminUserMenuTree($role_id, $parentid = 0, $level = 1)
    {
        $data = $this->adminMenu2($role_id, $parentid);
        $level++;
        $ret = array();
        if (is_array($data)) {
            foreach ($data as $a) {
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
                    //如果是tp6 返回 /home/module/controller/action 格式 TODO
                    $url = build_url("{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true);
                } else {
                    $url = url("{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true)->build();
                }
                $array = array(
                    "icon" => $a['icon'],
                    "id" => $id . $name,
                    "name" => $a['name'],
                    "url" => $url,
                    "path" => "/{$id}{$name}/{$controller}/{$action}",
                    "items" => []
                );

                $child = $this->getAdminUserMenuTree($role_id, $a['id'], $level);
                //由于后台管理界面只支持三层，超出的不层级的不显示
                if ($child && $level <= 3) {
                    $array['items'] = $child;
                }

                $ret [] = $array;
            }
        }
        return $ret;
    }


    /**
     * 按父ID查找菜单子项
     *
     * @param $role_id
     * @param  integer $parentid 父菜单ID
     * @param  boolean $with_self 是否包括他自己
     *
     * @return array
     */
    public function adminMenu2($role_id, $parentid, $with_self = false)
    {
        //父节点ID
        $parentid = (int)$parentid;
        // 只获取tp6菜单
        $result = $this->where(array('parentid' => $parentid, 'status' => 1, 'is_tp6' => 1))->order('listorder ASC,id ASC')->select()->toArray();
        if (empty($result)) {
            $result = array();
        }
        if ($with_self) {
            $parentInfo = $this->where(array('id' => $parentid))->findOrEmpty();
            $result2[] = $parentInfo ? $parentInfo : array();
            $result = array_merge($result2, $result);
        }
        //是否超级管理员
        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            //如果角色为超级管理员 直接通过
            return $result;
        }
        $array = array();
        //子角色列表
        $roleModel = new RoleModel();
        $child = explode(',', $roleModel->getArrchildid($role_id));
        foreach ($result as $v) {
            //方法
            $action = $v['action'];
            //条件
            $where = [['app', '=', $v['app']], ['controller', '=', $v['controller']], ['action', '=', $action], ['role_id', 'in', join(',', $child)]];
            //如果是菜单项
            if ($v['type'] == 0) {
                $where[1] = ['controller', '=', $v['controller'] . $v['id']];
                $where[2] = ['action', '=', $v['action'] . $v['id']];
            }
            //public开头的通过
            if (preg_match('/^public_/', $action)) {
                $array[] = $v;
            } else {
                //是否有权限
                $accessModel = new AccessModel();
                if ($accessModel->hasPermission($role_id, $where)) {
                    $array[] = $v;
                }
            }
        }
        return $array;
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