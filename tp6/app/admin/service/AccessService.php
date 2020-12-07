<?php
/**
 * Author: jayinton
 */

namespace app\admin\service;


use app\admin\model\AccessModel;
use app\admin\model\RoleModel;
use app\common\service\BaseService;
use think\exception\InvalidArgumentException;
use think\facade\Db;

/**
 * 角色权限管理
 *
 * @package app\admin\service
 */
class AccessService extends BaseService
{
    /**
     * 根据角色ID获取全部权限列表
     *
     * @param $role_id
     *
     * @return array
     */
    function getAccessListByRoleId($role_id)
    {
        //检查角色
        $roleinfo = Db::name('role')->where('id', $role_id)->findOrEmpty();
        if (empty($roleinfo)) {
            throw new InvalidArgumentException('找不到角色');
        }
        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            //超级管理员返回全部
            $accessList = [
                [
                    'app'        => '%',
                    'controller' => '%',
                    'action'     => '%',
                ]
            ];
        } else {
            //该角色全部权限
            $accessModel = new AccessModel();
            //子角色列表
            $roleModel = new RoleModel();
            $role_ids = $roleModel->getChildrenRoleIdList($role_id, true);
            //查询出该角色拥有的全部权限列表
            $accessList = $accessModel->where('role_id', 'IN', $role_ids)->field('app,controller,action')->select()->toArray() ?: [];
        }


        return self::createReturn(true, $accessList);
    }
}