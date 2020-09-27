<?php
/**
 * User: Cycle3
 * Date: 2020/9/25
 */

namespace app\admin\controller;

use app\admin\model\AccessGroupModel;
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
    public function selectAccessGroupList()
    {
        return view('selectAccessGroupList');
    }

    /**
     * 获取权限组列表操作
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccessGroupList()
    {
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray();
        return json(self::createReturn(true, $accessGroupTreeArray));
    }

    /**
     * 权限组列表
     * @return \think\response\View
     */
    public function accessGroupList()
    {
        return view('accessGroupList');
    }

    /**
     * 删除权限组
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteAccessGroup()
    {
        $group_id = input('group_id', '', 'trim');
        $res = RbacService::deleteAccessGroup([$group_id]);
        return json($res);
    }

    /**
     * 权限组详情
     * @return \think\response\View
     */
    public function accessGroupDetails()
    {
        $id = input('id', '', 'trim');
        if($id){
            $AccessGroupModel = new AccessGroupModel();
            $info = $AccessGroupModel->where('id', $id)->find();
            $info['parentid'] = (int) $info['parentid'];
        } else {
            $info['id'] = '';
            $info['name'] = '';
            $info['description'] = '';
            $info['status'] = '1';
            $info['parentid'] = 0;
        }
        return view('accessGroupDetails', [
            'info' => $info
        ]);
    }

    /**
     * 父级权限组
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccessGroupTreeArray()
    {
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray(0);
        foreach ($accessGroupTreeArray as $k => $v) {
            $accessGroupTreeArray[$k]['view_name'] = '|—'.str_repeat('—', $v['level'] * 1).$v['name'];
        }
        return json(self::createReturn(true, $accessGroupTreeArray));
    }

    /**
     * 创建权限组
     * @return \think\response\Json
     */
    public function doCreateAccessGroup()
    {
        $id = input('id', '', 'trim');
        $name = input('name', '', 'trim');
        $parentid = input('parentid', '', 'trim');
        $description = input('description', '', 'trim');
        $status = input('status', '', 'trim');
        $res = RbacService::createAccessGroup($id,$name, $parentid, $description, $status);
        return json($res);
    }

    /**
     * 编辑权限组权限
     * @return \think\response\Json
     */
    public function doSaveAccessGroupItem()
    {
        $group_id = input('group_id', '', 'trim');
        $accessGroupItems = input('accessGroupItems');
        $res = RbacService::updateAccessGroupItems($group_id, $accessGroupItems);
        return json($res);
    }

    /**
     * 获取权限组详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccessGroupById()
    {
        $id = input('id', '', 'trim');
        $accessGroup = RbacService::getAccessGroupById($id);
        return json($accessGroup);
    }
}