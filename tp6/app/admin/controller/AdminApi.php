<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;


use app\admin\model\MenuModel;
use app\admin\service\AccessService;
use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use think\facade\Db;

class AdminApi extends AdminController
{
    public $noNeedPermission = ['getPermissionInfo', 'getAdminUserInfo'];

    /**
     * 获取后台权限信息
     * - 显示的权限菜单
     * - 所有权限列表
     */
    function getPermissionInfo()
    {
        $adminUserInfo = AdminUserService::getInstance()->getInfo();

        $menuModel = new MenuModel();
        $menuList = $menuModel->getAdminUserMenuTree($adminUserInfo['role_id']);
        $accessService = new AccessService();
        $role_access_list = $accessService->getAccessListByRoleId($adminUserInfo['role_id'])['data'];
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
        $userInfo = AdminUserService::getInstance()->getInfo();
        $adminUser = Db::name('user')->where(['id' => $userInfo['id']])->withoutField('password,verify')->findOrEmpty();
        if ($adminUser) {
            $role = Db::name('role')->where(['id' => $adminUser['role_id']])->find();
            $adminUser['role_name'] = $role['name'];
        }

        return self::makeJsonReturn(true, $adminUser);
    }


}
