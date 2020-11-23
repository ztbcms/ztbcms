<?php
/**
 * User: jayinton
 */

namespace app\admin\service;


use app\admin\model\AccessModel;
use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\common\service\BaseService;
use think\exception\InvalidArgumentException;
use think\exception\ValidateException;
use think\facade\Db;

/**
 * 角色权限服务
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
            throw new InvalidArgumentException('请指定角色');
        }
        //检查角色
        $roleinfo = Db::name('role')->where('id', $role_id)->findOrEmpty();
        if (empty($roleinfo)) {
            throw new InvalidArgumentException('找不到角色');
        }
        //查询出该角色拥有的全部权限列表
        $accessService = new AccessService();
        $access = $accessService->getAccessListByRoleId($role_id)['data'];
        $accessList = [];
        foreach ($access as $acc) {
            $app = strtolower($acc['app']);
            $controller = strtolower($acc['controller']);
            $action = strtolower($acc['action']);
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
    function enableRoleAccess($role_id, $app, $controller, $action)
    {
        // 超级管理员
        if ($role_id === RoleModel::SUPER_ADMIN_ROLE_ID) {
            return self::createReturn(true, null, '权限检验通过');
        }
        $accessList = $this->getRoleAccessList($role_id)['data'];
        $app = strtolower($app);
        $controller = strtolower($controller);
        $action = strtolower($action);
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

            //action 为 % 的情况下可以直接通过
            if (isset($accessList[$app][$pass_controller]) &&
                (
                    isset($accessList[$app][$pass_controller][$action]) ||
                    isset($accessList[$app][$pass_controller]['%'])
                )
            ) {
                return self::createReturn(true, null, '权限检验通过');
            }
        }
        return self::createReturn(false, null, '无权限');
    }


    /**
     * 授权角色权限(菜单)
     *
     * @param  int|string  $role_id  角色ID
     * @param  array  $menuIdList  菜单Id列表
     *
     * @return array
     */
    function authorizeRoleAccess($role_id, $menuIdList = [])
    {
        //被选中的菜单项
        if (empty($menuIdList)) {
            throw new InvalidArgumentException('请选择授权菜单');
        }
        if (empty($role_id)) {
            throw new InvalidArgumentException('请选择授权角色');
        }

        //取得菜单数据
        $menuModel = new MenuModel();
        $menuList = $menuModel->where('id', 'in', $menuIdList)->select()->toArray();
        $accessList = [];
        //检测数据合法性
        foreach ($menuList as $menu) {
            $info = [
                'app'        => $menu['app'],
                'controller' => $menu['controller'],
                'action'     => $menu['action'],
                'role_id'    => $role_id,
                'status'     => 1
            ];
            //菜单项
            if (intval($menu['type']) === 0) {
                $info['controller'] = $info['controller'].$menu['id'];
                $info['action'] = $info['action'].$menu['id'];
            }
            $accessList[] = $info;
        }
        $accessModel = new AccessModel();
        $accessModel->startTrans();
        //删除旧的权限
        $accessModel->where("role_id", '=', $role_id)->delete();
        $res = $accessModel->insertAll($accessList);
        if (!$res) {
            $accessModel->rollback();
            return self::createReturn(false, null, '授权异常');
        }

        $accessModel->commit();
        return self::createReturn(true, null, '授权成功');
    }

}