<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 计划任务
        'cron:run' => 'app\common\command\cron\Run',
        'cron:exec' => 'app\common\command\cron\Exec',
        'cron:clean' => 'app\common\command\cron\Clean',
    ],
];
