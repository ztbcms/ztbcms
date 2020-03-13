<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Controller;

use Admin\Service\MenuService;
use Admin\Service\RbacService;
use Common\Controller\AdminBase;
use Libs\System\RBAC;

class AccessGroupController extends AdminBase {

    /**
     * 设置角色权限组
     */
    function accessGroupRoleSetting(){
        $role_id = I('get.role_id');
        $role = M("role")->where(['id' => $role_id])->find();
        $this->assign('role', $role);
        $this->display();
    }

    function accessGroupRoleSettingNew(){
        $role_id = I('get.role_id');
        $role = M("role")->where(['id' => $role_id])->find();
        $this->assign('role', json_encode($role));
        $this->display();
    }

    /**
     * 权限组列表
     */
    function accessGroupList(){
        $this->display();
    }

    /**
     * 选择权限组
     */
    function selectAccessGroupList(){
        $this->display();
    }

    /**
     * 获取权限组列表操作
     */
    function getAccessGroupList(){
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray();
        $this->ajaxReturn(self::createReturn(true, $accessGroupTreeArray));
    }

    /**
     * 获取角色的权限组
     */
    function getRoleAccessGroup(){
        $role_id = I('get.role_id');

        $res = RbacService::getRoleAccessGroup($role_id);
        $this->ajaxReturn($res);
    }

    /**
     * 权限列表
     */
    function accessList(){
        $this->display();
    }

    /**
     *
     */
    function getAccessList(){
        $menuTreeArray = MenuService::getMenuTreeArray();
        $this->ajaxReturn(self::createReturn(true, $menuTreeArray));
    }

    function getAccessGroupById(){
        $id = I('get.id');
        $accessGroup = RbacService::getAccessGroupById($id)['data'];
        $this->ajaxReturn(self::createReturn(true, $accessGroup));
    }

    /**
     * 创建权限组页
     */
    function createAccessGroup(){
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray(0);
        $this->assign('accessGroupTreeArray', $accessGroupTreeArray);
        $this->display('createOrEditAccessGroup');
    }

    /**
     * 编辑权限组信息页
     */
    function editAccessGroup(){
        $id = I('get.id');
        $this->assign('id' , $id);
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray(0);
        $this->assign('accessGroupTreeArray', $accessGroupTreeArray);
        $this->display('createOrEditAccessGroup');
    }

    /**
     * 创建权限组操作
     */
    function doCreateAccessGroup(){
        $name = I('post.name');
        $parentid = I('post.parentid');
        $description = I('post.description', '');
        $status = I('post.status');
        $res = RbacService::createAccessGroup($name, $parentid, $description, $status);
        $this->ajaxReturn($res);
    }

    /**
     * 更新权限组信息操作
     */
    function doEditAccessGroup(){
        $id = I('post.id');
        $name = I('post.name');
        $parentid = I('post.parentid');
        $description = I('post.description', '');
        $status = I('post.status');
        $res = RbacService::editAccessGroup($id, $name, $parentid, $description, $status);
        $this->ajaxReturn($res);
    }

    /**
     * 保存权限组的权限项列表信息
     */
    function doSaveAccessGroupItem(){
        $group_id = I('post.group_id');
        $accessGroupItems = I('post.accessGroupItems');

        $res = RbacService::updateAccessGroupItems($group_id, $accessGroupItems);
        $this->ajaxReturn($res);

    }

    /**
     * 保存角色的用户权限组信息
     */
    function doSaveAccessGroupRole(){
        $role_id = I('post.role_id');
        $accessGroupList = I('post.accessGroupList', []);

        $res = RbacService::updateRoleAccessGroup($role_id, $accessGroupList);

        $this->ajaxReturn($res);
    }

    /**
     * 删除权限组操作
     */
    function deleteAccessGroup(){
        $group_id = I('post.group_id');
        $res = RbacService::deleteAccessGroup([$group_id]);
        $this->ajaxReturn($res);
    }

}