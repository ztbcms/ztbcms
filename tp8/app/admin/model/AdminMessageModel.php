<?php
/**
 * User: jayinton
 * Date: 2020/9/19
 */

namespace app\admin\model;


use think\Model;

class AdminMessageModel extends Model
{
    protected $name = 'admin_message';
    // 已读
    const READ_STATUS_READED = 1;
    // 未读
    const READ_STATUS_UNREAD = 0;
    // 通知类型：系统消息
    const SYSTEM_TYPE = 'system';
}