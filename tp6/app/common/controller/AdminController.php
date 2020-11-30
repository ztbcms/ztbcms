<?php
/**
 * User: zhlhuang
 * Date: 2020-08-26
 */

namespace app\common\controller;


use app\admin\model\AdminUserModel;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\BaseController;
use app\common\model\UserModel;
use think\App;
use think\facade\Config;
use think\facade\View;

/**
 * 管理后台基础控制器
 *  - 权限、登录校验
 *  但凡涉及到管理后台的，请务必集成此类
 * @package app\common\controller
 */
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

    //引入中间件
    protected $middleware = [
        // 启用sessionn
        \think\middleware\SessionInit::class,
        // 用户登录验证
        \app\common\middleware\AdminAuth::class,
        // 用户权限验证
        \app\common\middleware\AdminPermission::class,
        //操作日志记录
        \app\admin\middleware\OperationLog::class,

    ];

    public function __construct(App $app)
    {
        parent::__construct($app);

        // 该方法是否需要登录
//        if($this->_checkActionMatch($app->request->action(), $this->noNeedLogin)){
//            // 不需要
//            return;
//        } else {
//            if (AdminUserService::getInstance()->isLogin()) {
//                $info = AdminUserService::getInstance()->getInfo();
//                $this->user = UserModel::where('id', $info['id'])->findOrEmpty();
//
//                // 账号是否被禁用
//                if($this->user['status'] == AdminUserModel::STATUS_DISABLE){
//                    $this->_handleDisabled();
//                    return;
//                }
//            } else {
//                $this->_handleUnlogin();
//                return;
//            }
//        }

        // 权限检测
        // 该方法是否需要权限检测
//        if($this->_checkActionMatch($app->request->action(), $this->noNeedPermission)){
//            // 不需要
//            return;
//        } else {
//            $hasPremission = $this->hasAccessPermission($this->user->id, $this->request->baseUrl());
//            if (!$hasPremission) {
//                $this->_handleNoPermiassion();
//            }
//        }
    }

    // 适配 $this->user
    public function __get($name)
    {
        var_dump('__get:'.$name);
        if($name == 'user'){
            if(empty($this->user)){
                $this->user = AdminUserService::getInstance()->getInfo();
            }
            return $this->user;
        }
        return null;
    }


    // 处理未登录情况
    private function _handleUnlogin()
    {
        if (request()->isAjax()) {
            self::makeJsonReturn(false, null, '请登录账号', 401)->send();
            exit;
        } else {
            $file = 'common/401';
            $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
            if (!file_exists($template_file)) {
                // 默认使用admin模块样式
                $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
            }
            response(View::fetch($template_file))->send();
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
            $file = 'common/403';
            $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
            if (!file_exists($template_file)) {
                // 默认使用admin模块样式
                $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
            }
            response(View::fetch($template_file))->send();
            exit;
        }
    }

    // 账号已被禁用
    private function _handleDisabled(){
        // 退出账户
        AdminUserService::getInstance()->logout();
        if (request()->isAjax()) {
            self::makeJsonReturn(false, null, '账号已被禁用', 403)->send();
            exit;
        } else {
            $file = 'common/403';
            $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
            if (!file_exists($template_file)) {
                // 默认使用admin模块样式
                $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
            }
            response(View::fetch($template_file, ['title' => '账号已被禁用']))->send();
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
        if (!empty($base_url)) {
            // 格式：/app/controller/action

            //去除参数的校验
            $base_url = explode('&', $base_url)[0];

            $items = explode('/', $base_url);
            $app = $items[1];
            $controller = $items[2];
            $action = $items[3];
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
        $file = 'common/error';
        $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
        if (!file_exists($template_file)) {
            // 默认使用admin模块样式
            $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
        }
        view($template_file, [
            'msg' => $msg
        ])->send();
    }
}