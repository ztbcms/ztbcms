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
     * 校验操作者角色是否有权管理目标角色
     *
     * @param int|string $operator_role_id         操作者角色ID
     * @param int|string $target_role_id           目标角色ID
     * @param bool       $allow_super_admin_target 是否允许目标为超级管理员角色（仅超管有效）
     *
     * @return array
     */
    public function canManageRole($operator_role_id, $target_role_id, $allow_super_admin_target = false): array
    {
        $operator_role_id = (int) $operator_role_id;
        $target_role_id = (int) $target_role_id;
        if ($operator_role_id <= 0 || $target_role_id <= 0) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }

        $roleModel = new RoleModel();
        $targetRole = $roleModel->where('id', $target_role_id)->findOrEmpty();
        if ($targetRole->isEmpty()) {
            return self::createReturn(false, null, '该角色不存在');
        }

        if ($operator_role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            if ($target_role_id === RoleModel::SUPER_ADMIN_ROLE_ID && !$allow_super_admin_target) {
                return self::createReturn(false, null, '超级管理员角色不能被操作');
            }
            return self::createReturn(true, $targetRole->toArray(), '校验通过');
        }

        if ($target_role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }

        $compare = $this->compareRoleLevel($operator_role_id, $target_role_id);
        if ((int) $compare['data'] !== self::LEVEL_COMPARE_HIGHER) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }

        return self::createReturn(true, $targetRole->toArray(), '校验通过');
    }

    /**
     * 校验并规范化角色的父角色ID
     *
     * @param int|string $operator_role_id 操作者角色ID
     * @param int|string $parent_role_id   目标父角色ID
     * @param int|string $editing_role_id  正在编辑的角色ID（新增时为0）
     *
     * @return array
     */
    public function validateParentRole($operator_role_id, $parent_role_id, $editing_role_id = 0): array
    {
        $operator_role_id = (int) $operator_role_id;
        $parent_role_id = (int) $parent_role_id;
        $editing_role_id = (int) $editing_role_id;

        if ($operator_role_id <= 0) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }

        if ($editing_role_id > 0) {
            if ($parent_role_id === $editing_role_id) {
                return self::createReturn(false, null, '父角色不能为当前角色自身');
            }
            $childrenIds = array_map('intval', (new RoleModel())->getChildrenRoleIdList($editing_role_id, false));
            if (in_array($parent_role_id, $childrenIds, true)) {
                return self::createReturn(false, null, '父角色不能为当前角色的子角色');
            }
        }

        if ($operator_role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            if ($parent_role_id === 0) {
                return self::createReturn(true, 0, '校验通过');
            }
            if ($parent_role_id < 0) {
                return self::createReturn(false, null, '父角色不存在');
            }
            $parentRole = (new RoleModel())->where('id', $parent_role_id)->findOrEmpty();
            if ($parentRole->isEmpty()) {
                return self::createReturn(false, null, '父角色不存在');
            }
            return self::createReturn(true, $parent_role_id, '校验通过');
        }

        if ($parent_role_id === 0) {
            $parent_role_id = $operator_role_id;
        }
        if ($parent_role_id < 0) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }
        if ($parent_role_id !== $operator_role_id) {
            if ((int) $this->compareRoleLevel($operator_role_id, $parent_role_id)['data'] !== self::LEVEL_COMPARE_HIGHER) {
                return self::createReturn(false, null, '当前登录用户无权限操作');
            }
        }

        return self::createReturn(true, $parent_role_id, '校验通过');
    }

    /**
     * 添加/编辑角色
     *
     * @param array    $roleData         角色数据
     * @param int|null $operator_role_id 操作者角色ID，默认取当前登录用户角色
     *
     * @return array
     */
    function addOrEditRole($roleData, $operator_role_id = null)
    {
        if ($operator_role_id === null) {
            $operator_role_id = AdminUserService::getInstance()->getInfo()['role_id'] ?? 0;
        }

        $editingRoleId = isset($roleData['id']) && !empty($roleData['id']) ? (int) $roleData['id'] : 0;
        if ($editingRoleId > 0) {
            if ($editingRoleId === RoleModel::SUPER_ADMIN_ROLE_ID) {
                return self::createReturn(false, null, '超级管理员角色不能被修改');
            }
            $manageCheck = $this->canManageRole($operator_role_id, $editingRoleId, false);
            if (!$manageCheck['status']) {
                return $manageCheck;
            }
        }

        $parentCheck = $this->validateParentRole($operator_role_id, $roleData['parentid'] ?? 0, $editingRoleId);
        if (!$parentCheck['status']) {
            return $parentCheck;
        }

        $data = [
            'name'     => $roleData['name'],
            'remark'   => $roleData['remark'],
            'status'   => $roleData['status'],
            'parentid' => $parentCheck['data'],
        ];

        $roleModel = new RoleModel();
        if ($editingRoleId > 0) {
            // 编辑
            $data['update_time'] = time();
            $res = $roleModel->where('id', $editingRoleId)->save($data);
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
     * @param int|string $role_id          目标角色ID
     * @param int|null   $operator_role_id 操作者角色ID，默认取当前登录用户角色
     *
     * @return array
     */
    function deleteRole($role_id, $operator_role_id = null)
    {
        if (empty($role_id)) {
            throw new InvalidArgumentException('请指定角色');
        }

        if ((int) $role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            return self::createReturn(false, null, '超级管理员角色不能被删除');
        }

        //角色信息
        $roleModel = new RoleModel();
        $info = $roleModel->where('id', $role_id)->find();
        if (empty($info) || !isset($info)) {
            throw new InvalidArgumentException('该角色不存在');
        }

        if ($operator_role_id === null) {
            $operator_role_id = AdminUserService::getInstance()->getInfo()['role_id'] ?? 0;
        }
        if ((int) $role_id === (int) $operator_role_id) {
            return self::createReturn(false, null, '您不能删除自己使用的角色');
        }
        $manageCheck = $this->canManageRole($operator_role_id, $role_id, false);
        if (!$manageCheck['status']) {
            return $manageCheck;
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
        if ((int) $role_id1 === (int) $role_id2) {
            return self::createReturn(true, self::LEVEL_COMPARE_SAME);
        }
        $roleList = $this->getRoleList()['data'];
        $son_list1 = TreeHelper::getSonNodeFromArray($roleList, $role_id1, ['parentKey' => 'parentid']);
        foreach ($son_list1 as $item) {
            if ((int) $item['id'] === (int) $role_id2) {
                return self::createReturn(false, self::LEVEL_COMPARE_HIGHER);
            }
        }
        $son_list2 = TreeHelper::getSonNodeFromArray($roleList, $role_id2, ['parentKey' => 'parentid']);
        foreach ($son_list2 as $item) {
            if ((int) $item['id'] === (int) $role_id1) {
                return self::createReturn(false, self::LEVEL_COMPARE_LOWER);
            }
        }
        // 对比结果，默认无交集
        return self::createReturn(false, self::LEVEL_COMPARE_NO_MIX);
    }
}