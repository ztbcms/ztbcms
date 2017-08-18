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

    function accessGroupSetting(){
        $role_id = I('get.role_id');
        $role = M("role")->where(['id' => $role_id])->find();
        $this->assign('role', $role);
        $this->display();
    }

    /**
     * 权限组列表
     */
    function accessGroupList(){
        $this->display();
    }

    /**
     * 获取权限组列表
     */
    function getAccessGroupList(){
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray();
        $this->ajaxReturn(self::createReturn(true, $accessGroupTreeArray));
    }

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
     * 
     */
    function createAccessGroup(){
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray(0);
        $this->assign('accessGroupTreeArray', $accessGroupTreeArray);
        $this->display('createOrEditAccessGroup');
    }

    function editAccessGroup(){
        $id = I('get.id');
        $this->assign('id' , $id);
        $accessGroupTreeArray = RbacService::getAccessGroupTreeArray(0);
        $this->assign('accessGroupTreeArray', $accessGroupTreeArray);
        $this->display('createOrEditAccessGroup');
    }

    function doCreateAccessGroup(){
        $name = I('post.name');
        $parentid = I('post.parentid');
        $description = I('post.description', '');
        $status = I('post.status');
        $res = RbacService::createAccessGroup($name, $parentid, $description, $status);
        $this->ajaxReturn($res);
    }

    function doEditAccessGroup(){
        $id = I('post.id');
        $name = I('post.name');
        $parentid = I('post.parentid');
        $description = I('post.description', '');
        $status = I('post.status');
        $res = RbacService::editAccessGroup($id, $name, $parentid, $description, $status);
        $this->ajaxReturn($res);
    }

    function doSaveAccessGroupItem(){
        $group_id = I('post.group_id');
        $accessGroupItems = I('post.accessGroupItems');

        $res = RbacService::updateAccessGroupItems($group_id, $accessGroupItems);
        $this->ajaxReturn($res);

    }

    function doSaveAccessGroupRole(){
        $role_id = I('post.role_id');
        $accessGroupList = I('post.accessGroupList');

        $res = RbacService::updateRoleAccessGroup($role_id, $accessGroupList);

        $this->ajaxReturn($res);
    }

}