<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

return [
    // 计划任务
    'cron:run' => 'app\common\command\cron\Run',
    'cron:exec' => 'app\common\command\cron\Exec',
    'cron:clean' => 'app\common\command\cron\Clean',
];