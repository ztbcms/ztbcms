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
use app\admin\service\RbacService;
use app\BaseController;
use app\common\model\UserModel;
use app\common\model\UserTokenModel;
use think\App;
use think\facade\Db;
use think\facade\View;

class AdminController extends BaseController
{

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedPermission = [];

    /**
     * @var UserModel|null
     */
    protected $user;

    //是否为超级管理员
    protected $is_administrator;

    //引入中间件
    protected $middleware = [
        //操作日志记录
        \app\admin\middleware\OperationLog::class
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);

        // 该方法是否需要登录
        if($this->_checkActionMatch($app->request->action(), $this->noNeedLogin)){
            // 不需要
            return;
        }
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

        //判断是否为超级管理员
        $this->is_administrator = $this->user['role_id'] == RoleModel::SUPER_ADMIN_ROLE_ID;

        // 权限检测
        // 该方法是否需要权限检测
        if($this->_checkActionMatch($app->request->action(), $this->noNeedPermission)){
            // 不需要
            return;
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
        // TODO: 适配过渡版本
        if (strpos($base_url, '/home/') === 0) {
            $base_url = str_replace('/home/', '', $base_url);
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
        $rbacService = new RbacService();
        $res = $rbacService->enableUserAccess($user_id, $app, $controller, $action);
        return $res['status'];
    }

    /**
     * 检测控制器的方法是否匹配
     *
     * @param $action
     * @param $arr
     *
     * @return bool
     */
    function _checkActionMatch($action, $arr){
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($action), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    // 错误展示
    function showError($msg){
        view('common/error', [
            'msg' => $msg
        ])->send();
    }
}