<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

namespace app\admin\service;


use app\admin\libs\system\Rbac;
use app\admin\model\AdminUserModel;
use app\admin\model\LoginlogModel;
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
    //存储用户uid的Key
    const userUidKey = 'spf_userid';
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
        if (empty($userId)) {
            // TODO 适配 ztbcms tp3登录跳转到 tp6
            $sessionId = $_COOKIE['PHPSESSID'];
            $token = Db::name('user_token')->where([
                ['session_id', '=', $sessionId],
                ['expire_time', '>', time()],
            ])->find();
            if($token){
                return (int)$token['user_id'];
            }
            return false;
        }
        return (int) $userId;
    }

    //登录后台
    public function login($identifier, $password)
    {
        if (empty($identifier) || empty($password)) {
            return false;
        }
        //验证
        $userInfo = $this->getUserInfo($identifier, $password);
        if (false == $userInfo) {
            //记录登录日志
            $this->record($identifier, $password, 0);
            return false;
        }
        //记录登录日志
        $this->record($identifier, $password, 1);
        //注册登录状态
        $this->registerLogin($userInfo);
        return true;
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
        // 删除凭证
        Db::name('UserToken')->where([
            ['session_id'  ,'=', cookie('PHPSESSID')],
        ])->delete();
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

        Db::name('UserToken')->insert([
            'session_id'  => cookie('PHPSESSID'),
            'token'       => $token,
            'user_id'     => (int) $userInfo['id'],
            'expire_time' => time() + 7 * 86400,
            'create_time' => time()
        ]);
        //更新状态
        $adminUserModel = new AdminUserModel();
        $adminUserModel->loginStatus((int) $userInfo['id']);
        //注册权限
        Rbac::saveAccessList((int) $userInfo['id']);
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
}