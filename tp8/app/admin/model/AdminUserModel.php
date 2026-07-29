<?php
/**
 * Author: jayinton
 */

namespace app\admin\model;

use think\facade\Db;
use think\Model;

/**
 *  管理后台用户
 *
 * @package app\admin\model
 */
class AdminUserModel extends Model
{
    protected $name = 'user';

    // 账号状态
    /**
     * 账号状态: 禁用
     */
    const STATUS_DISABLE = 0;
    /**
     * 账号状态: 正常
     */
    const STATUS_ENABLE = 1;

}