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
            if ($sessionId) {
                $userToken = UserTokenModel::where('session_id', $sessionId)->findOrEmpty();
                if (!$userToken->isEmpty()) {
                    $this->user = UserModel::where('id', $userToken->user_id)->findOrEmpty();
                }
            } else {
                //TODO 展示未登录提示页面
                echo '请登录';
                exit;
            }
        }

    }
}