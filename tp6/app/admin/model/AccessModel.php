<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;

use think\Model;

/**
 * 角色权限
 * Class AccessModel
 *
 * @package app\admin\model
 */
class AccessModel extends Model
{
    protected $name = 'access';

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
}