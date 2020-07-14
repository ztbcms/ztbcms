<?php

/**
 * author: Devtool
 * created: 2020-07-14 11:33:11
 */

namespace AdminMessage\Model;

use Common\Model\Model;

/**
 * 后台消息中心
 *
 */
class AdminMessageModel extends Model {

    protected $tableName = 'admin_message';

    // 已读
    const READED = 1;
    // 未读
    const WAIT_READ = 0;

    // 通知类型：
    const SYSTEM_TYPE = 'system';


}
