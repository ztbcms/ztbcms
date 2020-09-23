<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-08-26
 * Time: 17:34.
 */

namespace app\common\controller;


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
        if(!$hasPremission){
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
    private function _handleNoPermiassion(){
        if (request()->isAjax()) {
            self::makeJsonReturn(false, null, '无权限', 403)->send();
            exit;
        } else {
            response(View::fetch('common/403'))->send();
            exit;
        }
    }

    private function hasAccessPermission($user_id, string $baseUrl)
    {
        $user = AdminUserService::getInstance()->getAdminUserInfoById($user_id)['data'];
        if (empty($user)) {
            return false;
        }
        if(strpos($baseUrl, '/home')===0){
            $baseUrl = str_replace('/home', '', $baseUrl);
        }

        return true;
    }
}