<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;

use think\Model;

class MenuModel extends Model
{
    protected $name = 'menu';

    /**
     * 获取后台管理员的菜单
     *
     * @param $role_id
     * @param  int  $parentid
     * @param  int  $level
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
                    $fu = "?".$a['parameter'];
                }
                if (!empty($a['is_tp6'])) {
                    //如果是tp6 返回 /home/module/controller/action 格式 TODO
                    $url = build_url("{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true);
                } else {
                    $url = url("{$name}/{$controller}/{$action}{$fu}", ["menuid" => $id], '', true)->build();
                }
                $array = array(
                    "icon"  => $a['icon'],
                    "id"    => $id.$name,
                    "name"  => $a['name'],
                    "url"   => $url,
                    "path"  => "/{$id}{$name}/{$controller}/{$action}",
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
     * @param  integer  $parentid  父菜单ID
     * @param  boolean  $with_self  是否包括他自己
     *
     * @return array
     */
    public function adminMenu2($role_id, $parentid, $with_self = false)
    {
        //父节点ID
        $parentid = (int) $parentid;
        $result = $this->where(array('parentid' => $parentid, 'status' => 1))->order('listorder ASC,id ASC')->select()->toArray();
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
                $where[1] = ['controller', '=', $v['controller'].$v['id']];
                $where[2] = ['action', '=', $v['action'].$v['id']];
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
}