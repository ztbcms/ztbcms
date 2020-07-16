<?php

/**
 * author: Devtool
 * created: 2020-07-14 11:33:11
 */

namespace Admin\Model;

use Common\Model\Model;

/**
 * 后台消息管理模型
 * Class AdminMessageModel
 * @package AdminMessage\Model|
 */
class AdminMessageModel extends Model {

    protected $tableName = 'admin_message';
    // 已读
    const READED = 1;
    // 未读
    const WAIT_READ = 0;
    // 通知类型：系统消息
    const SYSTEM_TYPE = 'system';
}
