<?php

// +----------------------------------------------------------------------
// |  后台登录日志
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;

/**
 * 登录日志
 * Class LoginlogModel
 *
 * @package app\admin\model
 */
class LoginlogModel extends Model
{

    protected $name = 'login_log';

    /**
     * 删除登录日志(X天前的数据)
     *
     * @param  int  $day  N
     *
     * @return bool
     */
    public function deleteLoginLog($day = 30)
    {
        $limit_time = time() - $day * 24 * 60 * 60;
        $this->where('logintime', '<=', $limit_time)->delete();
        return true;
    }

    /**
     * 添加登录日志
     *
     * @param  array  $data
     *
     * @return boolean
     */
    public function addLoginLog($data)
    {
        unset($data['password']);
        $data['logintime'] = time();
        $data['loginip'] = request()->ip();
        return $this->insert($data) !== false ? true : false;
    }

    /**
     * 清理历史登录日志中的密码字段数据
     *
     * @return int 受影响行数
     */
    public static function cleanHistoryPasswords(): int
    {
        try {
            return (int) self::where('password', '<>', '')->update(['password' => '']);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
