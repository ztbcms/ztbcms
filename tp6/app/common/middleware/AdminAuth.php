<?php
declare (strict_types = 1);

namespace app\common\middleware;

use app\admin\model\AdminUserModel;
use app\admin\service\AdminUserService;
use app\common\model\UserModel;
use think\facade\Config;
use think\facade\View;
use think\Request;
use think\Response;

/**
 * 管理后台用户登录检测
 *
 * 注意：使用该中间件前，请确保session中间件 \think\middleware\SessionInit::class 已启用且先于此中间件启用
 *
 * @package app\common\middleware
 */
class AdminAuth
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        var_dump('AdminAuth..');
        // 该方法是否需要登录
        $noNeedLogin = $request->noNeedLogin ?? [];
        if($this->_checkActionMatch($request->action(), $noNeedLogin)){
            // 不需要验证登录
            return $next($request);
        } else {
            if (AdminUserService::getInstance()->isLogin()) {
                $info = AdminUserService::getInstance()->getInfo();
                $user = UserModel::where('id', $info['id'])->findOrEmpty()->toArray();

                // 账号是否被禁用
                if($user['status'] == AdminUserModel::STATUS_DISABLE){
                    return $this->_handleDisabled($request);
                }
            } else {
                return $this->_handleUnlogin($request);
            }
        }

        return $next($request);
    }

    /**
     * 检测控制器的方法是否匹配
     *
     * @param $action
     * @param $arr
     *
     * @return bool
     */
    function _checkActionMatch($action,array $arr){
        if (empty($arr)) {
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

    // 账号已被禁用
    private function _handleDisabled(Request $request){
        // 退出账户
        AdminUserService::getInstance()->logout();
        if (request()->isAjax()) {
            return json(createReturn(false, null, '账号已被禁用', 403));
        } else {
            $file = 'common/403';
            $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
            if (!file_exists($template_file)) {
                // 默认使用admin模块样式
                $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
            }
            return response(View::fetch($template_file, ['title' => '账号已被禁用']));
        }
    }

    // 处理未登录情况
    private function _handleUnlogin(Request  $request)
    {
        if ($request->isAjax()) {
            return json(createReturn(false, null, '请登录账号', 401));
        } else {
            $file = 'common/401';
            $template_file = app_path(Config::get('view.view_dir_name')).$file.'.'.Config::get('view.view_suffix');
            if (!file_exists($template_file)) {
                // 默认使用admin模块样式
                $template_file = base_path().'admin'.DIRECTORY_SEPARATOR.Config::get('view.view_dir_name').DIRECTORY_SEPARATOR.$file.'.'.Config::get('view.view_suffix');
            }
            return response(View::fetch($template_file));
        }
    }
}
