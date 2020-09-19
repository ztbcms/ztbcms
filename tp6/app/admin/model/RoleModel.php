<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;


use Admin\Service\User;
use think\Model;

/**
 * 角色
 * Class RoleModel
 *
 * @package app\admin\model
 */
class RoleModel extends Model
{
    protected $name = 'role';

    /**
     * 超级管理员ID
     */
    const SUPER_ADMIN_ROLE_ID = 1;

    /**
     * 根据角色ID返回全部权限
     *
     * @param string  $role_id  角色ID
     *
     * @return array
     */
    public function getAccessList($role_id)
    {
        $priv_data = array();
        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            //超级管理员返回全部
            return [
                [
                    'role_id'    => $role_id,
                    'app'        => '%',
                    'controller' => '%',
                    'action'     => '%',
                ]
            ];
        }
        $data = $this->getAccessList($role_id);
        if (empty($data)) {
            return $priv_data;
        }
        foreach ($data as $k => $rs) {
            $priv_data[$k] = array(
                'role_id'    => $rs['role_id'],
                'app'        => $rs['app'],
                'controller' => $rs['controller'],
                'action'     => $rs['action'],
            );
        }
        return $priv_data;
    }

    /**
     * 通过递归的方式获取该角色下的全部子角色
     *
     * @param string  $role_id
     *
     * @return string
     */
    public function getArrchildid($role_id) {
        $roleList = $this->getTreeArray();
        $arrchildid = $role_id;
        if (is_array($roleList)) {
            foreach ($roleList as $k => $cat) {
                if ($cat['parentid'] && $k != $role_id && $cat['parentid'] == $role_id) {
                    $arrchildid .= ',' . $this->getArrchildid($k);
                }
            }
        }
        return $arrchildid;
    }

    /**
     * 返回Tree使用的数组
     * @return array
     */
    function getTreeArray() {
        $roleList = array();
        $roleData = $this->order(array("listorder" => "asc", "id" => "desc"))->select();
        foreach ($roleData as $rs) {
            $roleList[$rs['id']] = $rs;
        }
        return $roleList;
    }
}