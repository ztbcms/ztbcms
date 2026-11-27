<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

namespace app\admin\service;

use think\facade\Session;

use app\admin\model\AdminUserModel;
use app\admin\model\LoginlogModel;
use app\admin\model\RoleModel;
use app\common\libs\helper\ClientIpHelper;
use app\common\libs\helper\PasswordHelper;
use app\common\libs\helper\StringHelper;
use app\common\service\BaseService;
use app\common\util\Encrypt;

/**
 * 后台管理员服务
 * Class AdminUserService
 *
 * @package app\admin\service
 */
class AdminUserService extends BaseService
{
    //存储后台用户id的Key
    const KEY_USER_ID = 'admin_user_id';
    //存储后台用户信息的Key
    const KEY_USER_INFO = 'admin_user_info';

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
     * 获取当前登录用户资料
     *
     * @return array|null
     */
    public function getInfo()
    {
        $sessionUserInfo = session(self::KEY_USER_INFO);
        if (empty($sessionUserInfo) || !is_array($sessionUserInfo)) {
            self::$userInfo = [];
            return null;
        }

        if (!empty($sessionUserInfo['id'])) {
            try {
                $freshUser = (new AdminUserModel())->where('id', (int) $sessionUserInfo['id'])->findOrEmpty();
                if (!$freshUser->isEmpty()) {
                    $sessionUserInfo = $this->syncSessionUserInfo($sessionUserInfo, $freshUser->toArray());
                } else {
                    $sessionUserInfo['role_id'] = 0;
                    $sessionUserInfo['status'] = AdminUserModel::STATUS_DISABLE;
                    session(self::KEY_USER_INFO, $sessionUserInfo);
                }
            } catch (\Throwable $e) {
                // 兼容未初始化用户表的轻量测试场景
            }
        }

        self::$userInfo = $sessionUserInfo;
        return !empty(self::$userInfo) ? self::$userInfo : null;
    }

    /**
     * 按管理员 ID 从数据库实时获取最新资料（不捕获数据库异常，确保 fail-closed）
     *
     * @param int $userId 管理员 ID
     *
     * @return array|null
     */
    public function getRealtimeUserInfoById(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $freshUser = (new AdminUserModel())
            ->where('id', $userId)
            ->withoutField('password,verify')
            ->findOrEmpty();
        if ($freshUser->isEmpty()) {
            return null;
        }

        $freshData = $freshUser->toArray();
        $sessionUserInfo = session(self::KEY_USER_INFO);
        if (!is_array($sessionUserInfo) || (int) ($sessionUserInfo['id'] ?? 0) !== $userId) {
            $sessionUserInfo = [];
        }

        if ((int) ($freshData['status'] ?? AdminUserModel::STATUS_DISABLE) === AdminUserModel::STATUS_ENABLE) {
            $this->syncSessionUserInfo($sessionUserInfo, $freshData);
        }

        return $freshData;
    }

    /**
     * 从数据库获取当前操作者的实时有效信息（校验存在性与启用状态）
     *
     * @param array|null $operatorUser 指定操作者数组，默认从当前登录会话获取
     *
     * @return array|null
     */
    public function getRealtimeOperatorInfo(?array $operatorUser = null): ?array
    {
        $sessionInfo = $operatorUser ?? session(self::KEY_USER_INFO);
        $userId = (int) ($sessionInfo['id'] ?? $this->isLogin());
        if ($userId <= 0) {
            return null;
        }

        $adminUserModel = new AdminUserModel();
        $freshUser = $adminUserModel->where('id', $userId)->findOrEmpty();
        if ($freshUser->isEmpty()) {
            return null;
        }

        $freshData = $freshUser->toArray();
        if ((int) ($freshData['status'] ?? AdminUserModel::STATUS_DISABLE) !== AdminUserModel::STATUS_ENABLE) {
            return null;
        }

        if (is_array($sessionInfo) && !empty($sessionInfo['id']) && (int) $sessionInfo['id'] === $userId) {
            $this->syncSessionUserInfo($sessionInfo, $freshData);
        }

        return $freshData;
    }

    /**
     * 从数据库获取当前操作者的实时有效角色ID
     *
     * @param array|null $operatorUser 指定操作者数组，默认从当前登录会话获取
     *
     * @return int
     */
    public function getRealtimeOperatorRoleId(?array $operatorUser = null): int
    {
        $operator = $this->getRealtimeOperatorInfo($operatorUser);
        return (int) ($operator['role_id'] ?? 0);
    }

    /**
     * 将数据库最新管理员字段同步回 Session 与静态缓存
     *
     * @param array $sessionUserInfo 原会话信息
     * @param array $freshData       数据库最新管理员记录
     *
     * @return array
     */
    private function syncSessionUserInfo(array $sessionUserInfo, array $freshData): array
    {
        $merged = array_merge($sessionUserInfo, [
            'id'       => $freshData['id'],
            'username' => $freshData['username'] ?? ($sessionUserInfo['username'] ?? ''),
            'nickname' => $freshData['nickname'] ?? ($sessionUserInfo['nickname'] ?? ''),
            'email'    => $freshData['email'] ?? ($sessionUserInfo['email'] ?? ''),
            'role_id'  => $freshData['role_id'],
            'info'     => $freshData['info'] ?? ($sessionUserInfo['info'] ?? ''),
            'avatar'   => $freshData['avatar'] ?? ($sessionUserInfo['avatar'] ?? ''),
            'phone'    => $freshData['phone'] ?? ($sessionUserInfo['phone'] ?? ''),
            'status'   => $freshData['status'] ?? AdminUserModel::STATUS_DISABLE,
            'remark'   => $freshData['remark'] ?? ($sessionUserInfo['remark'] ?? ''),
        ]);
        session(self::KEY_USER_INFO, $merged);
        self::$userInfo = $merged;

        return $merged;
    }

    /**
     * 检验用户是否已经登录
     *
     * @return boolean|int 失败返回false，成功返回当前登录用户基本信息
     */
    public function isLogin()
    {
        $token = session(self::KEY_USER_ID);
        if (!is_string($token) || $token === '') {
            return 0;
        }

        $userId = Encrypt::authcode($token, Encrypt::OPERATION_DECODE);
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
            $this->record($identifier, 0);
            return false;
        }
        $userInfo = $res['data'];
        if ((int) ($userInfo['status'] ?? AdminUserModel::STATUS_DISABLE) !== AdminUserModel::STATUS_ENABLE) {
            // 账号未启用，记录登录失败日志并拒绝登录
            $this->record($identifier, 0);
            return false;
        }
        //记录登录日志
        $this->record($identifier, 1);
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
    public function checkUserPassword($username, $password = '')
    {
        if (empty($username)) {
            return false;
        }
        $adminUserModel = new AdminUserModel();
        $userInfo = $adminUserModel->where('username', '=', $username)->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return self::createReturn(false, null, '用户未注册');
        }
        //密码验证
        if (!empty($password) && PasswordHelper::hashPassword($password, $userInfo['verify']) != $userInfo['password']) {
            return self::createReturn(false, null, '密码不正确');
        }
        $info = $userInfo->toArray();
        return self::createReturn(true, [
            'id'       => $info['id'],
            'username' => $info['username'],
            'nickname' => $info['nickname'],
            'email'    => $info['email'],
            'role_id'  => $info['role_id'],
            'info'     => $info['info'],
            'avatar'   => $info['avatar'],
            'phone'    => $info['phone'],
            'status'   => $info['status'],
            'remark'   => $info['remark'],
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
        if (!empty($userInfo)
            && (int) ($userInfo['status'] ?? AdminUserModel::STATUS_DISABLE) === AdminUserModel::STATUS_ENABLE
            && (int) $userInfo['role_id'] === RoleModel::SUPER_ADMIN_ROLE_ID
        ) {
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
        self::$userInfo = [];
        Session::destroy();
        return true;
    }

    /**
     * 记录登录日志
     *
     * @param  string|int  $identifier  登录方式，uid,username
     * @param  int|mixed   $status      登录状态（兼容旧版三参数签名时为第三参数）
     */
    private function record($identifier, $status = 0)
    {
        if (func_num_args() >= 3) {
            $status = func_get_arg(2);
        }
        //登录日志
        $loginLogModel = new LoginlogModel();
        $loginLogModel->addLoginLog([
            "username" => $identifier,
            "status"   => (int) $status,
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
        // 重新生成 Session ID 并清空登录前会话数据，防范会话固定攻击
        Session::regenerate(true);
        Session::clear();
        self::$userInfo = $userInfo;

        //写入session
        $token = Encrypt::authcode((int) $userInfo['id'], '');
        session(self::KEY_USER_ID, $token);
        session(self::KEY_USER_INFO, $userInfo);

        //更新状态
        $adminUserModel = new AdminUserModel();
        $adminUserModel->where('id', $userInfo['id'])->update([
            'last_login_time' => time(),
            'last_login_ip'   => ClientIpHelper::getClientIp(),
        ]);
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
        if (!$res->isEmpty()) {
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
        if (empty($userInfo) || empty($userInfo['id'])) {
            return false;
        }
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
            'password' => PasswordHelper::hashPassword($newPassword, $verify),
            'verify' => $verify
        ]);
        if($res){
            return self::createReturn(true, null, '密码修改成功');
        }
        return self::createReturn(false, null, '更新密码异常');
    }


}
