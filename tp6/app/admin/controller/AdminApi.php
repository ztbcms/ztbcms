<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;


use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\common\controller\AdminController;
use think\facade\Db;

class AdminApi extends AdminController
{

    /**
     * 获取后台权限信息
     * - 显示的权限菜单
     * - 所有权限列表
     */
    function getPermissionInfo()
    {
        $adminUserInfo = $this->user;

        $menuModel = new MenuModel();
        $menuList = $menuModel->getAdminUserMenuTree($adminUserInfo['role_id']);
        $roleModel = new RoleModel();
        $role_access_list = $roleModel->getAccessList($adminUserInfo['role_id']);
        $ret = [
            'menuList' => $menuList,
            'roleAccessList' => $role_access_list
        ];
        return self::makeJsonReturn(true, $ret);
    }

    /**
     * 获取后台管理员信息
     */
    public function getAdminUserInfo()
    {
        $adminUser = Db::name('user')->where(['id' => $this->user->id])->withoutField('password,verify')->findOrEmpty();
        if ($adminUser) {
            $role = Db::name('role')->where(['id' => $adminUser['role_id']])->find();
            $adminUser['role_name'] = $role['name'];
        }

        return self::makeJsonReturn(true, $adminUser);
    }


}