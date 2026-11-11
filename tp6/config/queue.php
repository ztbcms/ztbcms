<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

// 队列配置
return [
    // 默认驱动
    'default'     => env('queue.driver', 'database'),
    'connections' => [
        'sync'     => [
            'type' => 'sync',
        ],
        'database' => [
            // 继承 think-queue database 驱动，在 payload 中注入队列监控字段（monitor_uuid / pushed_at）
            'type'        => \app\common\libs\queue\connector\MonitorDatabase::class,
            'queue'       => 'default',
            'table'       => 'queue_jobs',
            'connection'  => null,
            // 任务被领取后超过该秒数未完成会被视为超时并重新领取，必须大于 worker 的最大 --timeout
            'retry_after' => (int)env('queue.retry_after', 180),
        ],
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.database', 0),
            'timeout'    => env('redis.timeout', 5),
            'persistent' => true, // 数据持久化
        ],
    ],
    'failed'      => [
        'type'  => 'database',
        'table' => 'queue_failed_jobs',
    ],
    // 队列监控（仅支持 database 驱动）
    'monitor'     => [
        // 是否记录任务执行情况
        'enable'                => filter_var(env('queue.monitor_enable', true), FILTER_VALIDATE_BOOLEAN),
        // 执行记录表
        'table'                 => 'queue_monitor',
        // 执行记录保留天数
        'keep_days'             => 7,
        // 异常信息（含堆栈）最大保存长度（字节）
        'exception_max_length'  => 8000,
        // payload 最大保存长度（字节）
        'payload_max_length'    => 16000,
        // 执行中超过该秒数仍未结束的记录判定为丢失
        'lost_after_seconds'    => 3600,
        // 活跃 worker 判定窗口（秒）
        'worker_active_seconds' => 300,
    ],
];