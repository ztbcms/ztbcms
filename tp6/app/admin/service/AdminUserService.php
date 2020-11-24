<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

namespace app\admin\service;


use app\admin\model\AdminUserModel;
use app\admin\model\LoginlogModel;
use app\common\libs\helper\StringHelper;
use app\common\service\BaseService;
use app\common\util\Encrypt;
use think\facade\Db;

/**
 * 后台管理员服务
 * Class AdminUserService
 *
 * @package app\admin\service
 */
class AdminUserService extends BaseService
{
    //存储后台用户uid的Key
    const userUidKey = 'admin_user_id';
    //超级管理员角色id
    const administratorRoleId = 1;

    //当前登录会员详细信息
    private static $userInfo = array();

    /**
     * 连接后台用户服务
     * @staticvar \Admin\Service\Cache $systemHandier
     *
     * @return AdminUserService
     */
    static public function getInstance()
    {
        static $handier = null;
        if (empty($handier)) {
            $handier = new AdminUserService();
        }
        return $handier;
    }

    /**
     * 魔术方法
     *
     * @param  string  $name
     *
     * @return null
     */
    public function __get($name)
    {
        //从缓存中获取
        if (isset(self::$userInfo[$name])) {
            return self::$userInfo[$name];
        } else {
            $userInfo = $this->getInfo();
            if (!empty($userInfo)) {
                return $userInfo[$name];
            }
            return null;
        }
    }

    /**
     * 获取当前登录用户资料
     *
     * @return array
     */
    public function getInfo()
    {
        if (empty(self::$userInfo)) {
            self::$userInfo = $this->getUserInfo($this->isLogin());
        }

        return !empty(self::$userInfo) ? self::$userInfo : null;
    }

    /**
     * 检验用户是否已经登录
     *
     * @return boolean|int 失败返回false，成功返回当前登录用户基本信息
     */
    public function isLogin()
    {
        $userId = Encrypt::authcode(session(self::userUidKey), 'DECODE');
        return (int) $userId;
    }

    //登录后台
    public function login($identifier, $password)
    {
        if (empty($identifier) || empty($password)) {
            return false;
        }
        //验证
        $res = $this->checkUserPassword($identifier, $password);
        if(!$res['status']){
            //记录登录日志
            $this->record($identifier, $password, 0);
            return false;
        }
        $userInfo = $res['data'];
        //记录登录日志
        $this->record($identifier, $password, 1);
        //注册登录状态
        $this->registerLogin($userInfo);
        return true;
    }

    /**
     * 校验用户名+密码
     * @param $username string 用户名
     * @param $password string 密码, 密码为空时不校验密码
     *
     * @return array|false
     */
    function checkUserPassword($username, $password = '')
    {
        if (empty($username)) {
            return false;
        }
        $adminUserModel = new AdminUserModel();
        $userInfo = $adminUserModel->where('username', '=', $username)->findOrEmpty();
        if (empty($userInfo)) {
            return self::createReturn(false, null, '用户未注册');
        }
        //密码验证
        if (!empty($password) && $adminUserModel->hashPassword($password, $userInfo['verify']) != $userInfo['password']) {
            return self::createReturn(false, null, '密码不正确');
        }
        $info = $userInfo->toArray();
        return self::createReturn(true, [
            'id'       => $info['id'],
            'username' => $info['username'],
            'nickname' => $info['nickname'],
            'email'    => $info['email'],
            'role_id'  => $info['role_id'],
            'avatar'   => $info['avatar'],
            'phone'    => $info['phone'],
        ], '登录成功');
    }

    /**
     * 检查当前用户是否超级管理员
     *
     * @return boolean
     */
    public function isAdministrator()
    {
        $userInfo = $this->getInfo();
        if (!empty($userInfo) && $userInfo['role_id'] == self::administratorRoleId) {
            return true;
        }
        return false;
    }

    /**
     * 注销登录状态
     *
     * @return boolean
     */
    public function logout()
    {
        session(null);
        return true;
    }

    /**
     * 记录登录日志
     *
     * @param  string  $identifier  登录方式，uid,username
     * @param  string  $password  密码
     * @param  int  $status
     */
    private function record($identifier, $password, $status = 0)
    {
        //登录日志
        $loginLogModel = new LoginlogModel();
        $loginLogModel->addLoginLog([
            "username" => $identifier,
            "status"   => $status,
            "password" => $status ? '密码保密' : $password,
            "info"     => is_int($identifier) ? '用户ID登录' : '用户名登录',
        ]);
    }

    /**
     * 注册用户登录状态
     *
     * @param  array $userInfo 用户信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function registerLogin(array $userInfo)
    {
        //写入session
        $token = Encrypt::authcode((int) $userInfo['id'], '');
        session(self::userUidKey, $token);

        //更新状态
        $adminUserModel = new AdminUserModel();
        $adminUserModel->where('id', $userInfo['id'])->update([
            'last_login_time' => time(),
            'last_login_ip'   => request()->ip(),
        ]);
    }

    /**
     * 获取用户信息
     *
     * @param  string $identifier 用户名或者用户ID
     *
     * @param null $password
     * @return boolean|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getUserInfo($identifier, $password = null)
    {
        if (empty($identifier)) {
            return false;
        }
        $adminUserModel = new AdminUserModel();
        return $adminUserModel->getUserInfo($identifier, $password);
    }

    /**
     * 获取后台用户信息
     * @param $user_id
     *
     * @return array|null
     */
    function getAdminUserInfoById($user_id)
    {
        if (empty($user_id)) {
            return null;
        }
        $adminUserModel = new AdminUserModel();
        $res = $adminUserModel->where('id', $user_id)->findOrEmpty();
        if($res){
            return self::createReturn(true, $res->toArray());
        }

        return self::createReturn(false, null, '找不到信息');
    }

    /**
     * 登录用户是否有权限
     * @param $app
     * @param $controller
     * @param $action
     *
     * @return mixed
     */
    function hasPermission($app, $controller, $action){
        $userInfo = $this->getInfo();
        $rbacService = new RbacService();
        return $rbacService->enableUserAccess($userInfo['id'], $app, $controller, $action)['status'];
    }

    /**
     * 修改密码
     *
     * @param $user_id int|string 用户ID
     * @param $newPassword string 新密码
     * @param  string|null  $password 旧密码
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function changePassword($user_id, $newPassword, $password = null)
    {
        //获取会员信息
        $adminUserModel = new AdminUserModel();
        $res = $this->getAdminUserInfoById($user_id);
        if(!$res['status']){
            return self::createReturn(false, null, '该用户不存在');
        }
        $userInfo = $res['data'];
        // 旧密码校验, 旧密码为null时不作校验
        if($password !== null && !$this->checkUserPassword($userInfo['username'], $password)['status']){
            return self::createReturn(false, null, '旧密码错误');
        }
        $verify = StringHelper::genRandomString(6);
        $res = $adminUserModel->where('id', $userInfo['id'])->save([
            'password' => $adminUserModel->hashPassword($newPassword, $verify),
            'verify' => $verify
        ]);
        if($res){
            return self::createReturn(true, null, '密码修改成功');
        }
        return self::createReturn(false, null, '更新密码异常');
    }


}