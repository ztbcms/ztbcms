<?php
/**
 * Author: jayinton
 */

namespace app\common\model\queue;

use think\Model;

/**
 * 队列失败任务模型
 */
class QueueFailedJobModel extends Model
{
    protected $name = 'queue_failed_jobs';
}
