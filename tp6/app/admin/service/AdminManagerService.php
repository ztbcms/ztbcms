<?php

/**
 * Author: Jayin
 */

namespace app\admin\service;

use app\admin\model\AdminUserModel;
use app\common\libs\helper\PasswordHelper;
use app\common\libs\helper\StringHelper;
use app\common\service\BaseService;

class AdminManagerService extends BaseService
{
    /**
     * 添加、编辑管理员
     *
     * @param $user_data
     *
     * @return array|bool
     */
    function addOrEditAdminManager($user_data)
    {
        if (empty($user_data) || !is_array($user_data)) {
            return self::createReturn(false, null, '参数异常');
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
        $user_id = $user_data['id'] ?? null;
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
     * @param $user_id
     *
     * @return array
     */
    function deleteAdminManager($user_id)
    {
        if (empty($user_id)) {
            return self::createReturn(false, null, '请指定需要删除的用户ID');
        }
        if ($user_id == 1) {
            return self::createReturn(false, null, '该管理员不能被删除');
        }
        $adminUserModel = new AdminUserModel();
        $res = $adminUserModel->where('id', $user_id)->delete();
        if ($res) {
            return self::createReturn(true, null, '删除成功');
        } else {
            return self::createReturn(false, null, '删除失败');
        }
    }
}