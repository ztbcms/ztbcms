<?php

/**
 * Author: Jayin
 */

namespace app\admin\service;

use app\admin\model\AdminUserModel;
use app\admin\model\RoleModel;
use app\common\libs\helper\PasswordHelper;
use app\common\libs\helper\StringHelper;
use app\common\service\BaseService;

class AdminManagerService extends BaseService
{
    /**
     * 更新管理员个人基础资料（仅允许 nickname / email / remark）
     *
     * @param int|string $user_id   管理员ID
     * @param array      $user_data 资料数据
     *
     * @return array
     */
    public function updateBasicInfo($user_id, array $user_data): array
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return self::createReturn(false, null, '参数异常');
        }

        $adminUserModel = new AdminUserModel();
        $targetUser = $adminUserModel->where('id', $user_id)->find();
        if (empty($targetUser)) {
            return self::createReturn(false, null, '该管理员不存在');
        }

        $data = [];
        isset($user_data['nickname']) && $data['nickname'] = $user_data['nickname'];
        isset($user_data['email']) && $data['email'] = $user_data['email'];
        isset($user_data['remark']) && $data['remark'] = $user_data['remark'];

        if (isset($data['nickname']) && empty($data['nickname'])) {
            return self::createReturn(false, null, '昵称不能为空');
        }

        if (isset($data['email'])) {
            $validate = \think\facade\Validate::rule('email', 'email');
            if (!$validate->check($data)) {
                return self::createReturn(false, null, '邮箱格式错误');
            }
            $check_email = $adminUserModel->where('email', $data['email'])->find();
            if ($check_email && (int) $check_email['id'] !== $user_id) {
                return self::createReturn(false, null, '邮箱已存在');
            }
        }

        $data['update_time'] = time();
        $res = $adminUserModel->where('id', $user_id)->save($data);
        if ($res !== false) {
            return self::createReturn(true, null, '更新成功');
        }

        return self::createReturn(false, null, '操作失败');
    }

    /**
     * 添加、编辑管理员
     *
     * @param array      $user_data     管理员数据
     * @param array|null $operator_user 操作者信息，默认取当前登录管理员
     *
     * @return array|bool
     */
    function addOrEditAdminManager($user_data, $operator_user = null)
    {
        if (empty($user_data) || !is_array($user_data)) {
            return self::createReturn(false, null, '参数异常');
        }
        $operator = AdminUserService::getInstance()->getRealtimeOperatorInfo($operator_user);
        $operator_role_id = (int) ($operator['role_id'] ?? 0);
        if ($operator_role_id <= 0) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }

        $data = [];
        isset($user_data['username']) && $data['username'] = $user_data['username'];
        isset($user_data['password']) && $data['password'] = $user_data['password'];
        isset($user_data['email']) && $data['email'] = $user_data['email'];
        isset($user_data['nickname']) && $data['nickname'] = $user_data['nickname'];
        isset($user_data['remark']) && $data['remark'] = $user_data['remark'];
        isset($user_data['role_id']) && $data['role_id'] = $user_data['role_id'];
        isset($user_data['status']) && $data['status'] = $user_data['status'];
        isset($user_data['info']) && $data['info'] = $user_data['info'];

        $adminUserModel = new AdminUserModel();
        $roleService = new RoleService();
        $user_id = $user_data['id'] ?? null;

        if (!empty($user_id)) {
            $targetUser = $adminUserModel->where('id', $user_id)->find();
            if (empty($targetUser)) {
                return self::createReturn(false, null, '该管理员不存在');
            }
            if ($operator_role_id !== RoleModel::SUPER_ADMIN_ROLE_ID) {
                if ((int) $targetUser['id'] === 1 || (int) $targetUser['role_id'] === RoleModel::SUPER_ADMIN_ROLE_ID) {
                    return self::createReturn(false, null, '当前登录用户无权限操作');
                }
                $targetRoleCheck = $roleService->canManageRole($operator_role_id, $targetUser['role_id'], false);
                if (!$targetRoleCheck['status']) {
                    return self::createReturn(false, null, '当前登录用户无权限操作');
                }
                if (isset($data['role_id'])) {
                    $newRoleCheck = $roleService->canManageRole($operator_role_id, $data['role_id'], false);
                    if (!$newRoleCheck['status']) {
                        return $newRoleCheck;
                    }
                }
            } else {
                if ((int) $targetUser['id'] === 1 && isset($data['role_id']) && (int) $data['role_id'] !== RoleModel::SUPER_ADMIN_ROLE_ID) {
                    return self::createReturn(false, null, '初始超级管理员角色不能被修改');
                }
                if (isset($data['role_id'])) {
                    $newRoleCheck = $roleService->canManageRole($operator_role_id, $data['role_id'], true);
                    if (!$newRoleCheck['status']) {
                        return $newRoleCheck;
                    }
                }
            }
        } else {
            if (!isset($data['role_id']) || empty($data['role_id'])) {
                return self::createReturn(false, null, '请选择角色');
            }
            $roleCheck = $roleService->canManageRole(
                $operator_role_id,
                $data['role_id'],
                $operator_role_id === RoleModel::SUPER_ADMIN_ROLE_ID
            );
            if (!$roleCheck['status']) {
                return $roleCheck;
            }
        }

        // 校验用户名/邮箱
        $check_username = null;
        if (isset($data['username'])) {
            $check_username = $adminUserModel->where('username', $data['username'])->find();
        }
        $check_email = null;
        if (isset($data['email'])) {
            $check_email = $adminUserModel->where('email', $data['email'])->find();

            $validate = \think\facade\Validate::rule('email', 'email');
            if(!$validate->check($data)){
                return self::createReturn(false, null, '邮箱格式错误');
            }
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $verify = StringHelper::genRandomString(6);
            $data['verify'] = $verify;
            $data['password'] = PasswordHelper::hashPassword($data['password'], $verify);
        } else {
            unset($data['password']);
        }

        if (isset($data['nickname']) && empty($data['nickname'])) {
            return self::createReturn(false, null, '昵称不能为空');
        }

        if (!empty($user_id)) {
            // 编辑
            if (isset($data['username'])) {
                if ($check_username && $check_username['id'] != $user_id) {
                    return self::createReturn(false, null, '用户名已存在');
                }
            }
            if (isset($data['email'])) {
                if ($check_email && $check_email['id'] != $user_id) {
                    return self::createReturn(false, null, '邮箱已存在');
                }
            }
            $data['update_time'] = time();
            $res = $adminUserModel->where('id', $user_id)->save($data);
            if ($res) {
                return self::createReturn(true, null, '更新成功');
            }
        } else {
            // 新增
            if ($check_username) {
                return self::createReturn(false, null, '用户名已存在');
            }
            if ($check_email) {
                return self::createReturn(false, null, '邮箱已存在');
            }
            if (!isset($data['role_id']) || empty($data['role_id'])){
                return self::createReturn(false, null, '请选择角色');
            }
            $data['create_time'] = $data['update_time'] = time();

            //用户表指定不能为空
            $data['info'] = '';
            $data['avatar'] = '';
            $data['phone'] = '';

            $res = $adminUserModel->insert($data);
            if ($res) {
                return self::createReturn(true, null, '操作成功');
            }
        }

        return self::createReturn(true, null, '操作失败');
    }

    /**
     * 删除管理员
     *
     * @param int|string $user_id       目标管理员ID
     * @param array|null $operator_user 操作者信息，默认取当前登录管理员
     *
     * @return array
     */
    function deleteAdminManager($user_id, $operator_user = null)
    {
        if (empty($user_id)) {
            return self::createReturn(false, null, '请指定需要删除的用户ID');
        }
        if ($user_id == 1) {
            return self::createReturn(false, null, '该管理员不能被删除');
        }

        $operator = AdminUserService::getInstance()->getRealtimeOperatorInfo($operator_user);
        $operator_id = (int) ($operator['id'] ?? 0);
        $operator_role_id = (int) ($operator['role_id'] ?? 0);
        if ($operator_role_id <= 0) {
            return self::createReturn(false, null, '当前登录用户无权限操作');
        }
        if ((int) $user_id === $operator_id) {
            return self::createReturn(false, null, '不能删除自己');
        }

        $adminUserModel = new AdminUserModel();
        $targetUser = $adminUserModel->where('id', $user_id)->find();
        if (empty($targetUser)) {
            return self::createReturn(false, null, '该管理员不存在');
        }
        if ($operator_role_id !== RoleModel::SUPER_ADMIN_ROLE_ID) {
            if ((int) $targetUser['role_id'] === RoleModel::SUPER_ADMIN_ROLE_ID) {
                return self::createReturn(false, null, '当前登录用户无权限操作');
            }
            $roleCheck = (new RoleService())->canManageRole($operator_role_id, $targetUser['role_id'], false);
            if (!$roleCheck['status']) {
                return $roleCheck;
            }
        }

        $res = $adminUserModel->where('id', $user_id)->delete();
        if ($res) {
            return self::createReturn(true, null, '删除成功');
        } else {
            return self::createReturn(false, null, '删除失败');
        }
    }
}