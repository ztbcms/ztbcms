<?php
/**
 * User: jayinton
 * Date: 2018/12/21
 * Time: 16:17
 */

namespace Admin\Controller;


use Admin\Service\User;
use Common\Controller\AdminBase;

/**
 * 后台通用接口【无需检查权限】
 * @package Admin\Controller
 */
class AdminApiController extends AdminApiBaseController
{
    /**
     * 获取后台用户的菜单
     */
    public function getMenuList()
    {
        $adminUserInfo = $this->userInfo;

        $menuList = D("Admin/Menu")->getAdminUserMenuTree($adminUserInfo['role_id']);
        $this->ajaxReturn(self::createReturn(true, $menuList));
    }

    /**
     * 获取后台权限信息
     * - 显示的权限菜单
     * - 所有权限列表
     */
    public function getPermissionInfo(){
        $adminUserInfo = $this->userInfo;

        $menuList = D("Admin/Menu")->getAdminUserMenuTree($adminUserInfo['role_id']);
        $role_access_list = D("Admin/Role")->getAccessList($adminUserInfo['role_id']);
        $ret = [
            'menuList' => $menuList,
            'roleAccessList' => $role_access_list
        ];
        $this->ajaxReturn(self::createReturn(true, $ret));
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