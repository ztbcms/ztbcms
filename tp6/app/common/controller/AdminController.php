<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-08-26
 * Time: 17:34.
 */

namespace app\common\controller;


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
        $sessionId = cookie('PHPSESSID');
        if ($sessionId) {
            $userToken = UserTokenModel::where('session_id', $sessionId)->findOrEmpty();
            if (!$userToken->isEmpty()) {
                $this->user = UserModel::where('id', $userToken->user_id)->findOrEmpty();
            }
        }
    }
}