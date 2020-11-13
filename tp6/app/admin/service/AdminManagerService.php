<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace app\admin\service;

use app\admin\model\AdminUserModel;
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
            'info' => isset($user_data['info'] ) ? $user_data['info'] : '',
        ];
        $adminUserModel = new AdminUserModel();
        $id = null;
        if (isset($user_data['id']) && !empty($user_data['id'])) {
            $id = $user_data['id'];
        } else {
            // 新增
            $record = $adminUserModel->where('username', $data['username'])->findOrEmpty();
            if ($record) {
                return self::createReturn(false, null, '账户已经存在');
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


    function deleteAdminManager($user_id)
    {

    }

    static function hashPassword($password, $verify = "")
    {
        return md5($password . md5($verify));
    }
}