<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Controller;

use Admin\Service\MenuService;
use Admin\Service\RbacService;
use Common\Controller\AdminBase;

class AccessGroupController extends AdminBase {

    /**
     * 权限组列表
     */
    function accessGroupList(){
        $this->display();
    }

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

}