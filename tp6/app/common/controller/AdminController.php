<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-08-26
 * Time: 17:34.
 */

namespace app\common\controller;


use app\admin\libs\system\Rbac;
use app\admin\model\AdminUserModel;
use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
use app\BaseController;
use app\common\model\UserModel;
use app\common\model\UserTokenModel;
use think\App;
use think\facade\Db;
use think\facade\View;

class AdminController extends BaseController
{
    /**
     * @var UserModel|null
     */
    protected $user;

    public function __construct(App $app)
    {
        parent::__construct($app);
        if (AdminUserService::getInstance()->isLogin()) {
            //tp6 直接登录
            $info = AdminUserService::getInstance()->getInfo();
            $this->user = UserModel::where('id', $info['id'])->findOrEmpty();
        } else {
            // 适配 ztbcms v3跳转到 tp6
            $sessionId = cookie('PHPSESSID');
            if (!$sessionId) {
                $this->_handleUnlogin();
                return;
            }
            $userToken = UserTokenModel::where('session_id', $sessionId)->findOrEmpty();
            if ($userToken->isEmpty()) {
                $this->_handleUnlogin();
                return;
            }

            $this->user = UserModel::where('id', $userToken->user_id)->findOrEmpty();
        }
        $hasPremission = $this->hasAccessPermission($this->user->id, $this->request->baseUrl());
        if (!$hasPremission) {
            $this->_handleNoPermiassion();
        }
    }

    // 处理未登录情况
    private function _handleUnlogin()
    {
        if (request()->isAjax()) {
            self::makeJsonReturn(false, null, '请登录账号', 401)->send();
            exit;
        } else {
            response(View::fetch('common/401'))->send();
            exit;
        }
    }

    // 无权限情况
    private function _handleNoPermiassion()
    {
        if (request()->isAjax()) {
            self::makeJsonReturn(false, null, '无权限', 403)->send();
            exit;
        } else {
            response(View::fetch('common/403'))->send();
            exit;
        }
    }

    /**
     * 权限检测
     * @param  int|string  $user_id  用户ID
     * @param  string  $base_url  路由
     *
     * @return bool
     */
    private function hasAccessPermission($user_id, string $base_url = '')
    {
        $user = AdminUserService::getInstance()->getAdminUserInfoById($user_id)['data'];
        if (empty($user)) {
            return false;
        }
        // TODO: 适配过渡版本
        if (strpos($base_url, '/home/') === 0) {
            $base_url = str_replace('/home/', '', $base_url);
        }
        // 超级管理员
        if ($user['role_id'] === RoleModel::SUPER_ADMIN_ROLE_ID) {
            return true;
        }
        if (!empty($base_url)) {
            $items = explode('/', $base_url);
            $app = $items[0];
            $controller = $items[1];
            $action = $items[2];
        } else {
            $app = strtoupper(app('http')->getName());
            $controller = strtoupper(request()->controller());
            $action = strtoupper(request()->action());
        }

        $accessList = Rbac::getAccessList($user_id);
        // app
        if (isset($accessList['%'])) {
            return true;
        }
        if (!isset($accessList[$app])) {
            return false;
        }
        // controller+action
        if (isset($accessList[$app]['%'])) {
            return true;
        }
        $controllers = explode('.', $controller);
        $c = [];
        foreach ($controllers as $i => $v) {
            $c [] = $v;
            $pass_controller = trim(join('.', $c).'.'.trim(str_repeat('%.', count($controllers) - ($i + 1)), '.'), '.');
            if (isset($accessList[$app][$pass_controller]) && isset($accessList[$app][$pass_controller][$action])) {
                return true;
            }
        }
        return false;
    }
}