<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;


use app\admin\model\RoleModel;
use app\common\controller\AdminController;

class AdminApi extends AdminController
{

    /**
     * 获取后台权限信息
     * - 显示的权限菜单
     * - 所有权限列表
     */
    function getPermissionInfo(){
        $adminUserInfo = $this->user;

        $menuList = D("Admin/Menu")->getAdminUserMenuTree($adminUserInfo['role_id']);
        $roleModel = new RoleModel();
        $role_access_list = $roleModel->getAccessList($adminUserInfo['role_id']);
        $ret = [
            'menuList' => $menuList,
            'roleAccessList' => $role_access_list
        ];
        return self::makeReturn(true, $ret);
    }

    /**
     * 获取后台管理员信息
     */
    public function getAdminUserInfo()
    {
        $adminUser = M('user')->where(['id' => $this->uid])->field('password,verify', true)->find();
        if($adminUser){
            $role = M('role')->where(['id' => $adminUser['role_id']])->find();
            $adminUser['role_name'] = $role['name'];
        }

        $this->ajaxReturn(self::createReturn(true, $adminUser));
    }

    /**
     * 登出
     */
    public function logout()
    {
        User::getInstance()->logout();
        //手动登出时，清空forward
        cookie( NULL);
        $this->ajaxReturn(self::createReturn(true, [
            'redirect' => U("Admin/Public/login")
        ],'注销成功'));

    }
}