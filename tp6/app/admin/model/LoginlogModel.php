<?php

// +----------------------------------------------------------------------
// |  后台登录日志
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;
use app\admin\service\AdminLogService;
use app\common\libs\helper\ClientIpHelper;

/**
 * 登录日志
 * Class LoginlogModel
 *
 * @package app\admin\model
 */
class LoginlogModel extends Model
{
    /**
     * 默认日志保留天数
     */
    const DEFAULT_RETENTION_DAYS = AdminLogService::DEFAULT_RETENTION_DAYS;

    /**
     * 日志清理最少保留天数
     */
    const MIN_RETENTION_DAYS = AdminLogService::MIN_RETENTION_DAYS;

    /**
     * 日志清理最大保留天数
     */
    const MAX_RETENTION_DAYS = AdminLogService::MAX_RETENTION_DAYS;

    protected $name = 'login_log';

    /**
     * 删除登录日志(X天前的数据)
     *
     * @param  mixed  $day  保留天数
     *
     * @return bool
     */
    public function deleteLoginLog($day = self::DEFAULT_RETENTION_DAYS)
    {
        $retentionDays = AdminLogService::parseRetentionDays($day);
        if ($retentionDays === null) {
            return false;
        }

        $limit_time = time() - $retentionDays * 24 * 60 * 60;
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
        $data['loginip'] = ClientIpHelper::getClientIp();
        return $this->insert($data) !== false ? true : false;
    }
}
