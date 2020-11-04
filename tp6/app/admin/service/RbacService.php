<?php
/**
 * User: jayinton
 */

namespace app\admin\service;


use app\admin\model\AccessModel;
use app\admin\model\RoleModel;
use app\common\service\BaseService;
use think\exception\ErrorException;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;

/**
 * 角色权限管理
 * Class RbacService
 *
 * @package app\admin\service
 */
class RbacService extends BaseService
{
    /**
     * 获取用户的访问列表
     *
     * @param $user_id
     *
     * @return array|false|null
     */
    function getUserAccessList($user_id)
    {
        if (empty($user_id)) {
            throw new ValidateException('请指定用户');
        }
        //用户信息
        $userInfo = Db::name('user')->where('id', $user_id)->findOrEmpty();
        if (empty($userInfo)) {
            return self::createReturn(false, null, '找不到用户');
        }

        return $this->getRoleAccessList($userInfo['role_id']);
    }

    /**
     * 获取角色的访问列表
     *
     * @param  int|string  $role_id  角色ID
     *
     * @return array
     */
    function getRoleAccessList($role_id)
    {
        if (empty($role_id)) {
            throw new ValidateException('请指定角色');
        }
        //检查角色
        $roleinfo = Db::name('role')->where('id', $role_id)->findOrEmpty();
        if (empty($roleinfo) || empty($roleinfo['status'])) {
            return self::createReturn(false, null, '找不到角色');
        }
        //该角色全部权限
        $accessModel = new AccessModel();
        $access = $accessModel->getAccessList($role_id);
        $accessList = array();
        foreach ($access as $acc) {
            $app = strtoupper($acc['app']);
            $controller = strtoupper($acc['controller']);
            $action = strtoupper($acc['action']);
            $accessList[$app][$controller][$action] = $action;
        }
        return self::createReturn(true, $accessList);
    }

    /**
     * 检测用户是否可以访问权限菜单
     *
     * @param $user_id
     * @param $app
     * @param $controller
     * @param $action
     *
     * @return array|null
     */
    function enableUserAccess($user_id, $app, $controller, $action)
    {
        $res = AdminUserService::getInstance()->getAdminUserInfoById($user_id);
        if (!$res['status']) {
            return $res;
        }
        $userInfo = $res['data'];
        return $this->enableRoleAccess($userInfo['role_id'], $app, $controller, $action);
    }

    /**
     * 检测角色是否可以访问权限菜单
     *
     * @param $role_id
     * @param $app
     * @param $controller
     * @param $action
     *
     * @return array
     */
    function enableRoleAccess($role_id, $app, $controller, $action){
        // 超级管理员
        if ($role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            return self::createReturn(true, null, '权限检验通过');
        }
        $accessList = $this->getRoleAccessList($role_id)['data'];
        $app = strtoupper($app);
        $controller = strtoupper($controller);
        $action = strtoupper($action);
        // app
        if (isset($accessList['%'])) {
            return self::createReturn(true, null, '权限检验通过');
        }
        if (!isset($accessList[$app])) {
            return self::createReturn(false, null, '无权限');
        }
        // controller+action
        if (isset($accessList[$app]['%'])) {
            return self::createReturn(true, null, '权限检验通过');
        }
        $controllers = explode('.', $controller);
        $c = [];
        // 计算可能得controller模式，如 a.b.c 、a.b.%、a.%.%
        foreach ($controllers as $i => $v) {
            $c [] = $v;
            $pass_controller = trim(join('.', $c).'.'.trim(str_repeat('%.', count($controllers) - ($i + 1)), '.'), '.');
            if (isset($accessList[$app][$pass_controller]) && isset($accessList[$app][$pass_controller][$action])) {
                return self::createReturn(true, null, '权限检验通过');
            }
        }
        return self::createReturn(false, null, '无权限');
    }

}