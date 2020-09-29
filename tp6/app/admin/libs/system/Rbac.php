<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

namespace app\admin\libs\system;

use app\admin\model\AccessModel;
use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
use think\facade\Db;

/**
 * RBAC后台权限控制
 * Class Rbac
 *
 * @package app\admin\libs\system
 */
class Rbac
{
    /**
     * 当前登录下权限检查
     * @param string $map [模块/控制器/方法]，没有时，自动获取当前进行判断
     * @return boolean
     */
    static public function authenticate($map = '') {
        if (self::checkLogin() == false) {
            return false;
        }
        //是否超级管理员
        if (User::getInstance()->isAdministrator() === true) {
            return true;
        }
        //查询是否有权限
        return D('Admin/Access')->isCompetence($map);
    }

    //用于检测用户权限的方法,并保存到Session中，登录成功以后，注册有权限
    static function saveAccessList($authId = null) {
        if (null === $authId) {
            $authId = AdminUserService::getInstance()->id;
        }

        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if (AdminUserService::getInstance()->isAdministrator() !== true) {
            session("_ACCESS_LIST", RBAC::getAccessList($authId));
        }

        return;
    }

    //检查当前操作是否需要认证 第二步
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if (C('USER_AUTH_ON')) {
            //控制器
            $_controller = array();
            //动作
            $_action = array();
            if ("" != C('REQUIRE_AUTH_CONTROLLER')) {
                //需要认证的控制器
                $_controller['yes'] = explode(',', strtoupper(C('REQUIRE_AUTH_CONTROLLER')));
            } else {
                //无需认证的控制器
                $_controller['no'] = explode(',', strtoupper(C('NOT_AUTH_CONTROLLER')));
            }
            //检查当前模块是否需要认证
            if ((!empty($_controller['no']) && !in_array(strtoupper(CONTROLLER_NAME), $_controller['no'])) || (!empty($_controller['yes']) && in_array(strtoupper(CONTROLLER_NAME), $_controller['yes']))) {
                if ("" != C('REQUIRE_AUTH_ACTION')) {
                    //需要认证的操作
                    $_action['yes'] = explode(',', strtoupper(C('REQUIRE_AUTH_ACTION')));
                } else {
                    //无需认证的操作
                    $_action['no'] = explode(',', strtoupper(C('NOT_AUTH_ACTION')));
                }
                //检查当前操作是否需要认证
                if ((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME), $_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME), $_action['yes']))) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }

    // 登录检查
    static public function checkLogin() {
        //检查当前操作是否需要认证
        if (RBAC::checkAccess()) {
            //检查认证识别号
            if (User::getInstance()->isLogin() == false) {
                return false;
            }
        }
        return true;
    }

    //权限认证的过滤器方法 第一步
    static public function AccessDecision($appName = MODULE_NAME) {
        //检查是否需要认证
        if (RBAC::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid = md5($appName . CONTROLLER_NAME . ACTION_NAME);
            //判断是否超级管理员，是无需进行权限认证
            if (User::getInstance()->isAdministrator() !== true) {
                //认证类型 1 登录认证 2 实时认证
                if (C('USER_AUTH_TYPE') == 2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = RBAC::getAccessList(User::getInstance()->id);
                } else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if (session($accessGuid)) {
                        return true;
                    }
                    //登录验证模式，登录后保存的可访问权限列表
                    $accessList = session("_ACCESS_LIST");
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                $controller = defined('P_CONTROLLER_NAME') ? P_CONTROLLER_NAME : CONTROLLER_NAME;
                if (!isset($accessList[strtoupper($appName)][strtoupper($controller)][strtoupper(ACTION_NAME)])) {

                    if($accessList[strtoupper($appName)][strtoupper($controller)]['%']){
                        //如果控制器下有 % 则该控制器下方法权限都拥有
                        return true;
                    }

                    if($accessList[strtoupper($appName)]['%']){
                        //如果模块下有 % 则该模块的控制器权限都拥有
                        return true;
                    }

                    //验证登录
                    if (self::checkLogin() == true) {
                        //做例外处理，只要有管理员帐号，都有该项权限
                        if ($appName == "Admin" && in_array(CONTROLLER_NAME, array("Index", "Main")) && in_array(ACTION_NAME, array("index"))) {
                            session($accessGuid, true);
                            return true;
                        }
                        //如果是public_开头的验证通过。
                        if (substr(ACTION_NAME, 0, 7) == 'public_') {
                            session($accessGuid, true);
                            return true;
                        }
                        //内容模块特殊处理
                        if ($appName == 'Content' && CONTROLLER_NAME == 'Content') {
                            session($accessGuid, true);
                            return true;
                        }
                    }
                    session($accessGuid, false);
                    return false;
                } else {
                    session($accessGuid, true);
                }
            } else {
                //超级管理员直接验证通过，且检查是否登录
                if (self::checkLogin()) {
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * 取得当前认证号的所有权限列表
     *
     * @param $user_id
     *
     * @return array|bool
     */
    static public function getAccessList($user_id)
    {
        //用户信息
        $userInfo = Db::name('user')->where('id', $user_id)->findOrEmpty();
        if (empty($userInfo)) {
            return null;
        }
        //角色ID
        $role_id = $userInfo['role_id'];
        //检查角色
        $roleinfo = Db::name('role')->where('id' ,$role_id)->findOrEmpty();
        if (empty($roleinfo) || empty($roleinfo['status'])) {
            return false;
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
        return $accessList;
    }

    /**
     * 检测登陆用户对某个接口的访问权限
     * @param $moduelName
     * @param $controllerName
     * @param $actionName
     * @return bool
     */
    static function ableAccess($moduelName, $controllerName, $actionName){
        $accessList = session("_ACCESS_LIST");

        if(!empty($accessList[$moduelName]) && !empty($accessList[$moduelName][$controllerName]) && !empty($accessList[$moduelName][$controllerName][$actionName])){
            return true;
        }
        return false;
    }
}