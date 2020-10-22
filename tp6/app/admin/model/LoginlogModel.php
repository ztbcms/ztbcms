<?php

// +----------------------------------------------------------------------
// |  后台登录日志
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;

class LoginlogModel extends Model
{

    protected $name = 'loginlog';

    /**
     * 删除一个月前的日志
     * @return boolean
     */
    public function deleteAMonthago()
    {
        $status = $this->where(array("logintime" => array("lt", time() - (86400 * 30))))->delete();
        return $status !== false ? true : false;
    }

    /**
     * 添加登录日志
     * @param  array  $data
     * @return boolean
     */
    public function addLoginLog($data)
    {
        $data['logintime'] = time();
        $data['loginip'] = request()->ip();
        return $this->insert($data) !== false ? true : false;
    }
}
