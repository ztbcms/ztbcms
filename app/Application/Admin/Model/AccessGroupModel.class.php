<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Model;

use Think\Model;

/**
 * 权限组
 */
class AccessGroupModel extends Model {
    /**
     * 启用
     */
    const STATUS_ENABLE = 1;
    /**
     * 取消
     */
    const STATUS_DISABLE = 0;
}