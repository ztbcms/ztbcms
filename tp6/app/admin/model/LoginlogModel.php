<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

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
    protected $name = 'loginlog';

    /**
     * 删除一个月前的日志
     *
     * @return boolean
     */
    public function deleteAMonthago()
    {
        $limit_time = time() - 86400 * 30;
        return $this->where('logintime', 'lt', $limit_time)->delete();
    }

    /**
     * 添加登录日志
     *
     * @param  array  $data
     *
     * @return boolean
     */
    function addLoginLog($data)
    {
        $data['logintime'] = time();
        $data['loginip'] = request()->ip();
        return $this->insert($data, true) ? true : false;
    }
}