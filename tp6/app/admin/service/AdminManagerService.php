<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace app\admin\service;

use app\admin\model\AdminUserModel;
use app\admin\model\RoleModel;
use app\admin\validate\User;
use app\common\libs\helper\StringHelper;
use app\common\service\BaseService;
use think\exception\ValidateException;

class AdminManagerService extends BaseService
{
    /**
     * 添加、编辑管理员
     * @param $user_data
     * @return array|bool
     */
    function addOrEditAdminManager($user_data)
    {
        if (empty($user_data) || !is_array($user_data)) {
            return self::createReturn(false, null, '参数异常');
        }
        $data = [
            'username' => $user_data['username'],
            'password' => $user_data['password'],
            'email' => $user_data['email'],
            'nickname' => $user_data['nickname'],
            'remark' => $user_data['remark'],
            'role_id' => $user_data['role_id'],
            'status' => $user_data['status'],
            'info' => isset($user_data['info']) ? $user_data['info'] : '',
        ];
        $adminUserModel = new AdminUserModel();
        $id = null;
        $check_username = $adminUserModel->where('username', $data['username'])->find();
        $check_email = $adminUserModel->where('email', $data['email'])->find();
        if (isset($user_data['id']) && !empty($user_data['id'])) {
            $id = $user_data['id'];
            if($check_username['id'] != $id){
                return self::createReturn(false, null, '用户名已存在');
            }
            if($check_email['id'] != $id){
                return self::createReturn(false, null, '邮箱已存在');
            }
        } else {
            // 新增
            if ($check_username) {
                return self::createReturn(false, null, '用户名已存在');
            }
            if ($check_email) {
                return self::createReturn(false, null, '邮箱已存在');
            }
        }
        if (!empty($data['password'])) {
            $verify = StringHelper::genRandomString(6);
            $data['verify'] = $verify;
            $data['password'] = self::hashPassword($data['password'], $verify);
        } else {
            unset($data['password']);
        }
        try {
            validate(User::class)->check($data);
            if (empty($id)) {
                $res = $adminUserModel->insert($data);
                if ($res) {
                    return self::createReturn(true, null, '添加管理员成功');
                }
            } else {
                $res = $adminUserModel->where('id', $id)->save($data);
                if ($res) {
                    return self::createReturn(true, null, '更新成功');
                }
            }
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return self::createReturn(false, null, $e->getError());
        }
    }

    /**
     * 删除管理员
     * @param $user_id
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

    /**
     * 密码hash
     * @param $password
     * @param string $verify
     * @return string
     */
    static function hashPassword($password, $verify = "")
    {
        return md5($password . md5($verify));
    }
}