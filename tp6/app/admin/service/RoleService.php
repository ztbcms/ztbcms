<?php
/**
 * Author: jayinton
 */

namespace app\admin\service;


use app\admin\model\AccessModel;
use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\common\service\BaseService;
use think\exception\InvalidArgumentException;

/**
 * 角色服务
 *
 * @package app\admin\service
 */
class RoleService extends BaseService
{
    /**
     * 添加/编辑角色
     *
     * @param $roleData
     *
     * @return array
     */
    function addOrEditRole($roleData)
    {
        $data = [
            'name'     => $roleData['name'],
            'remark'   => $roleData['remark'],
            'status'   => $roleData['status'],
            'parentid' => $roleData['parentid'],
        ];

        $roleModel = new RoleModel();
        if (isset($roleData['id']) && !empty($roleData['id'])) {
            // 编辑
            $data['update_time'] = time();
            $res = $roleModel->where('id', $roleData['id'])->save($data);
        } else {
            // 新增
            $data['create_time'] = $data['update_time'] = time();
            $res = $roleModel->insert($data);
        }
        if (!$res) {
            return self::createReturn(false, null, '操作失败');
        }
        return self::createReturn(true, null, '操作成功');
    }

    /**
     * 删除角色
     * @param $role_id
     *
     * @return array
     */
    function deleteRole($role_id)
    {
        if (empty($role_id)) {
            throw new InvalidArgumentException('请指定角色');
        }

        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            return self::createReturn(false, null, '超级管理员角色不能被删除');
        }

        //角色信息
        $roleModel = new RoleModel();
        $info = $roleModel->where('id', $role_id)->find();
        if (empty($info) || !isset($info)) {
            throw new InvalidArgumentException('该角色不存在');
        }

        //子角色列表
        $child = $roleModel->getChildrenRoleIdList($role_id);
        if (count($child) > 1) {
            return self::createReturn(false, null, '该角色下有子角色，请删除子角色才可以删除');
        }
        $res = $roleModel->where('id', $role_id)->delete();
        if ($res) {
            $accessModel = new AccessModel();
            $accessModel->where('role_id', $role_id)->delete();
            return self::createReturn(true, null, '操作完成');
        }

        return self::createReturn(false, null, '操作失败');
    }
}