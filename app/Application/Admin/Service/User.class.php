<?php

// +----------------------------------------------------------------------
// |  后台用户服务
// +----------------------------------------------------------------------

namespace Admin\Service;

class User
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
     * @return \Admin\Service\User
     */
    static public function getInstance()
    {
        static $handier = NULL;
        if (empty($handier)) {
            $handier = new User();
        }
        return $handier;
    }

    /**
     * 魔术方法
     * @param string $name
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
            return NULL;
        }
    }

    /**
     * 获取当前登录用户资料
     * @return array
     */
    public function getInfo()
    {
        if (empty(self::$userInfo)) {
            self::$userInfo = $this->getUserInfo($this->isLogin());
        }
        return !empty(self::$userInfo) ? self::$userInfo : false;
    }

    /**
     * 检验用户是否已经登录
     * @return boolean|int 失败返回false，成功返回当前登录用户基本信息
     */
    public function isLogin()
    {
        $userId = \Libs\Util\Encrypt::authcode(session(self::userUidKey), 'DECODE');
        if (empty($userId)) {
            // TODO 适配 ztbcms tp6跳转到 v3
            $sessionId = $_COOKIE['PHPSESSID'];
            $token = M('user_token')->where(['session_id' => $sessionId])->find();
            if($token){
                return (int)$token['user_id'];
            }

            return false;
        }
        return (int)$userId;
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
     * @return boolean
     */
    public function logout()
    {
        // 删除凭证
        M('UserToken')->where([
            ['session_id' => $_COOKIE['PHPSESSID']],
        ])->delete();
        session('[destroy]');
        return true;
    }

    /**
     * 记录登录日志
     * @param string $identifier 登录方式，uid,username
     * @param string $password 密码
     * @param int $status
     */
    private function record($identifier, $password, $status = 0)
    {
        //登录日志
        D('Admin/Loginlog')->addLoginLogs(array(
            "username" => $identifier,
            "status" => $status,
            "password" => $status ? '密码保密' : $password,
            "info" => is_int($identifier) ? '用户ID登录' : '用户名登录',
        ));
    }

    /**
     * 注册用户登录状态
     * @param array $userInfo 用户信息
     */
    private function registerLogin(array $userInfo)
    {
        //写入session
        $token = \Libs\Util\Encrypt::authcode((int)$userInfo['id'], '');
        session(self::userUidKey, $token);
        M('UserToken')->add([
            'session_id' => session_id(),
            'token' => $token,
            'user_id' => (int)$userInfo['id'],
            'expire_time' => time() + 7 * 86400,
            'create_time' => time()
        ]);
        //更新状态
        D('Admin/User')->loginStatus((int)$userInfo['id']);
        //注册权限
        \Libs\System\RBAC::saveAccessList((int)$userInfo['id']);
    }

    /**
     * 获取用户信息
     * @param string $identifier 用户名或者用户ID
     * @return boolean|array
     */
    private function getUserInfo($identifier, $password = NULL)
    {
        if (empty($identifier)) {
            return false;
        }
        return D('Admin/User')->getUserInfo($identifier, $password);
    }

}
