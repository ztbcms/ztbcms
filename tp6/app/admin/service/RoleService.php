<?php
/**
 * Author: jayinton
 */

namespace app\admin\service;


use app\admin\model\RoleModel;
use app\common\service\BaseService;

class RoleService extends BaseService
{
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

    function deleteRole($role_id)
    {

    }
}