<?php
/**
 * User: Cycle3
 * Date: 2020/9/25
 */

namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\common\controller\AdminController;
use app\admin\service\RbacService;

class AccessGroup extends AdminController
{

    /**
     * 权限组设置
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accessGroupRoleSetting()
    {
        $role_id = input('role_id', '', 'trim');
        $RoleModel = new RoleModel();
        $info = $RoleModel->where('id', $role_id)->find();
        return view('accessGroupRoleSetting', [
            'info' => $info
        ]);
    }

    /**
     * 获取角色拥有的权限组
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoleAccessGroup()
    {
        $role_id = input('role_id', '', 'trim');
        $res = RbacService::getRoleAccessGroup($role_id);
        return json($res);
    }

    /**
     * 为用户添加权限组
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function doSaveAccessGroupRole()
    {
        $role_id = input('role_id');
        $accessGroupList = input('post.accessGroupList', []);
        $res = RbacService::updateRoleAccessGroup($role_id, $accessGroupList);
        return json($res);
    }

    /**
     * 选择权限组
     */
    public function selectAccessGroupList(){
        return view('selectAccessGroupList');
    }

    /**
     * 获取权限组列表操作
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccessGroupList(){
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray();
        return json(self::createReturn(true, $accessGroupTreeArray));
    }

}