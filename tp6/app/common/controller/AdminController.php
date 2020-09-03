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

    /**
     * 创建统一的Service返回结果
     *
     * @param boolean $status 返回状态
     * @param array $data 返回数据
     * @param string $msg 返回提示
     * @param string $code 错误码
     * @param string $url 下一跳地址
     *
     * @return array
     */
    static function createReturn($status, $data = [], $msg = '', $code = null, $url = '')
    {
        //默认成功则为200 错误则为400
        if (empty($code)) {
            $code = $status ? 200 : 400;
        }
        return [
            'status' => $status,
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
            'url' => $url,
        ];
    }
}