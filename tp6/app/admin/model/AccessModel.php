<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;

/**
 * 角色权限
 * Class AccessModel
 *
 * @package app\admin\model
 */
class AccessModel extends \think\Model
{
    protected $name = 'access';


    /**
     * 检查用户是否有对应权限
     * @param $role_id
     * @param $map
     * @return bool
     */
    public function hasPermission($role_id, $map){
        //超级管理员
        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            return true;
        }
        if (!is_array($map)) {
            //子角色列表
            $roleModel = new RoleModel();
            $child = explode(',', $roleModel->getArrchildid($role_id));
            if (!empty($map)) {
                $map = trim($map, '/');
                $map = explode('/', $map);
                if (empty($map)) {
                    return false;
                }
            } else {
                $map = array(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME,);
            }
            if (count($map) >= 3) {
                list($app, $controller, $action) = $map;
            } elseif (count($map) == 1) {
                $app = MODULE_NAME;
                $controller = CONTROLLER_NAME;
                $action = $map[0];
            } elseif (count($map) == 2) {
                $app = MODULE_NAME;
                list($controller, $action) = $map;
            }
            $map = array('role_id' => array('IN', $child), 'app' => $app, 'controller' => $controller, 'action' => $action);
        }
        $count = $this->where($map)->count();
        return $count ? true : false;
    }

    /**
     * 根据角色ID返回全部权限
     *
     * @param string  $role_id  角色ID
     *
     * @return array|boolean
     */
    public function getAccessList($role_id) {
        if (empty($role_id)) {
            return [];
        }
        //子角色列表
        $roleModel = new RoleModel();
        $child = explode(',', $roleModel->getArrchildid($role_id));
        if (empty($child)) {
            return [];
        }
        //查询出该角色拥有的全部权限列表
        $data = $this->where('role_id', 'IN', $child)->select()->toArray();
        if (empty($data)) {
            return [];
        }
        $accessList = array();
        foreach ($data as $info) {
            unset($info['status']);
            $accessList[] = $info;
        }
        return $accessList;
    }

    /**
     * 角色授权
     * @param array $addauthorize 授权数据
     * @param string|int $roleid 角色id
     * @return boolean
     */
    public function batchAuthorize($addauthorize, $roleid = 0) {

        if (empty($addauthorize)) {
            $this->error = '没有需要授权的权限！';
            return false;
        }

        if (empty($roleid)) {
            if (empty($addauthorize[0]['role_id'])) {
                $this->error = '角色ID不能为空！';
                return false;
            }
            $roleid = $addauthorize[0]['role_id'];
        }

        foreach ($addauthorize as $k => $rs) {
            unset($addauthorize[$k]['type']);
        }

        //删除旧的权限
        $this->where(array("role_id" => $roleid))->delete();
        return $this->insertAll($addauthorize) !== false ? true : false;
    }
}