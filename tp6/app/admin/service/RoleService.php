<?php
/**
 * Author: jayinton
 */

namespace app\admin\service;


use app\admin\model\AccessModel;
use app\admin\model\AdminUserModel;
use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\common\libs\helper\TreeHelper;
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
     * 角色层级对比，层级无交集
     */
    const LEVEL_COMPARE_NO_MIX = -2;
    /**
     * 角色层级对比，层级较低
     */
    const LEVEL_COMPARE_LOWER = -1;
    /**
     * 角色层级对比，同一角色
     */
    const LEVEL_COMPARE_SAME = 0;
    /**
     * 角色层级对比，层级较高
     */
    const LEVEL_COMPARE_HIGHER = 1;

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
     *
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
        if (count($child) > 0) {
            return self::createReturn(false, null, '该角色下有子角色，无法删除');
        }

        $adminUserModel = new AdminUserModel();
        $admin = $adminUserModel->where('role_id', $role_id)->find();
        if ($admin) {
            return self::createReturn(false, null, '该角色下有成员，无法删除');
        }

        $res = $roleModel->where('id', $role_id)->delete();
        if ($res) {
            $accessModel = new AccessModel();
            $accessModel->where('role_id', $role_id)->delete();
            return self::createReturn(true, null, '操作完成');
        }

        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 获取全部角色
     * TODO 添加缓存
     *
     * @param  array  $where
     *
     * @return array
     */
    function getRoleList($where = [])
    {
        $roleModel = new RoleModel();
        $list = $roleModel->where($where)->select()->toArray();
        return self::createReturn(true, $list);
    }

    /**
     * 比较两个会员层级
     * 请参考
     *
     * @param $role_id1
     * @param $role_id2
     *
     * @return array
     */
    function compareRoleLevel($role_id1, $role_id2)
    {
        if ($role_id1 === $role_id2) {
            return self::createReturn(true, self::LEVEL_COMPARE_SAME);
        }
        $roleList = $this->getRoleList()['data'];
        $son_list1 = TreeHelper::getSonNodeFromArray($roleList, $role_id1, ['parentKey' => 'parentid']);
        foreach ($son_list1 as $item) {
            if ($item['id'] == $role_id2) {
                return self::createReturn(false, self::LEVEL_COMPARE_HIGHER);
            }
        }
        $son_list2 = TreeHelper::getSonNodeFromArray($roleList, $role_id2, ['parentKey' => 'parentid']);
        foreach ($son_list2 as $item) {
            if ($item['id'] == $role_id1) {
                return self::createReturn(false, self::LEVEL_COMPARE_LOWER);
            }
        }
        // 对比结果，默认无交集
        return self::createReturn(false, self::LEVEL_COMPARE_NO_MIX);
    }
}