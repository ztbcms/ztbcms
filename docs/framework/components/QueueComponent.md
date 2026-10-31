# 队列任务组件

本文档介绍 ZTBCMS 中队列任务的使用方法和开发规范。

## 目录

- [概述](#概述)
- [配置说明](#配置说明)
- [快速开始](#快速开始)
- [队列任务开发](#队列任务开发)
- [内置任务列表](#内置任务列表)
- [队列命令](#队列命令)
- [事件监听](#事件监听)
- [运维部署](#运维部署)
- [常见问题排查](#常见问题排查)
- [最佳实践](#最佳实践)

## 概述

ZTBCMS 基于 ThinkPHP 6.0 的 `think-queue` 组件实现队列功能，任务类统一继承 `app\common\libs\queue\BaseQueueJob`。

### 支持的驱动

| 驱动 | 说明 | 适用场景 |
|------|------|----------|
| **sync** | 同步执行 | 开发调试 |
| **database** | 数据库存储（默认） | 小规模部署 |
| **redis** | Redis 存储 | 生产环境推荐 |

### 工作流程

```
业务代码调用 Queue::push()
         ↓
任务数据存入队列（数据库/Redis）
         ↓
队列监听进程取出任务
         ↓
执行任务的 fire() 方法
         ↓
成功: 调用 $job->delete() 删除任务
失败: 调用 $job->release() 延迟重试
超过重试次数: 调用 failed() 回调
```

## 配置说明

### 队列配置文件

配置文件位置：`tp6/config/queue.php`

```php
return [
    // 默认驱动，可通过 .env 配置
    'default'     => env('queue.driver', 'database'),
    'connections' => [
        // 同步执行
        'sync'     => [
            'type' => 'sync',
        ],
        // 数据库驱动
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'queue_jobs',      // 队列任务表
            'connection' => null,
        ],
        // Redis 驱动
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.database', 0),
            'timeout'    => env('redis.timeout', 5),
            'persistent' => true,
        ],
    ],
    // 失败任务存储
    'failed'      => [
        'type'  => 'database',
        'table' => 'queue_failed_jobs',        // 失败任务表
    ],
];
```

### 数据库表结构

表结构定义位于：`tp6/app/common/install/Common.sql`

| 表名 | 说明 |
|------|------|
| `cms_queue_jobs` | 队列任务表 |
| `cms_queue_failed_jobs` | 失败任务表 |

> 实际表名会叠加数据库前缀（`DB_PREFIX`），例如默认前缀为 `cms_` 时，表名即 `cms_queue_jobs`。

### 环境变量配置

在 `.env` 文件中配置：

```ini
# 队列驱动 (sync/database/redis)
queue.driver = database

# Redis 配置（使用 Redis 驱动时）
redis.host = 127.0.0.1
redis.port = 6379
redis.password =
redis.database = 0
redis.timeout = 5
```

## 快速开始

### 1. 创建队列任务类

在模块的 `job/` 目录下创建任务类，继承 `BaseQueueJob`：

```php
<?php

namespace app\yourmodule\job;

use app\common\libs\queue\BaseQueueJob;
use think\facade\Log;
use think\queue\Job;

class YourJob extends BaseQueueJob
{
    // 队列名称（建议使用常量定义）
    public const QUEUE_NAME = 'YourQueueName';

    // 最大重试次数
    private const MAX_ATTEMPTS = 3;

    // 重试延迟时间（秒）
    private const RETRY_DELAY = 5;

    /**
     * 任务执行
     */
    public function fire(Job $job, $data)
    {
        try {
            // 执行业务逻辑
            $this->doSomething($data);

            // 成功后删除任务（必须调用）
            $job->delete();

        } catch (\Exception $e) {
            Log::error('任务执行异常: ' . $e->getMessage());

            // 重试
            $this->retry($job);
        }
    }

    /**
     * 重试逻辑
     */
    private function retry(Job $job)
    {
        if ($job->attempts() > self::MAX_ATTEMPTS) {
            // 超过重试次数，删除任务
            $job->delete();
            return;
        }

        // 延迟重试
        $job->release(self::RETRY_DELAY);
    }

    /**
     * 任务最终失败回调
     */
    public function failed($data)
    {
        Log::error('任务最终失败', ['data' => $data]);
    }

    private function doSomething($data)
    {
        // 业务逻辑
    }
}
```

### 2. 推送任务到队列

```php
use think\facade\Queue;
use app\yourmodule\job\YourJob;

// 推送任务
Queue::push(YourJob::class, [
    'param1' => 'value1',
    'param2' => 'value2',
], YourJob::QUEUE_NAME);

// 延迟推送（延迟 60 秒执行）
Queue::later(60, YourJob::class, [
    'param1' => 'value1',
], YourJob::QUEUE_NAME);
```

### 3. 启动队列监听

```bash
# 进入项目目录
cd tp6

# 启动监听
php think queue:listen --queue YourQueueName
```

> **注意**：任务执行成功后必须调用 `$job->delete()`，否则会反复重试直到失败。

## 队列任务开发

### 基类说明

所有队列任务必须继承 `app\common\libs\queue\BaseQueueJob`：

文件位置：`tp6/app/common/libs/queue/BaseQueueJob.php`

```php
abstract class BaseQueueJob
{
    /**
     * 任务执行
     * 如果任务执行成功后，务必删除任务，否则任务会重复执行
     */
    abstract function fire(Job $job, $data);

    /**
     * 任务达到最大重试次数后，失败回调
     */
    abstract function failed($data);
}
```

### Job 对象常用方法

| 方法 | 说明 |
|------|------|
| `$job->delete()` | 删除任务（任务成功后必须调用） |
| `$job->release($delay)` | 重新发布任务，可设置延迟秒数 |
| `$job->attempts()` | 获取当前重试次数 |
| `$job->getJobId()` | 获取任务 ID |
| `$job->getQueue()` | 获取队列名称 |

## 内置任务列表

### Bark 消息推送任务

| 属性 | 值 |
|------|------|
| 任务类 | `tp6/app/bark/job/PushMsgJob.php` |
| 队列名 | `BarkPushMsg` |
| 触发入口 | `tp6/app/bark/controller/Api.php` 的 `pushMsg` |
| 重试策略 | 最大重试 3 次，失败后延迟 5 秒重试 |

```php
use think\facade\Queue;
use app\bark\job\PushMsgJob;

Queue::push(PushMsgJob::class, [
    'title' => '消息标题',
    'body' => '消息内容',
    'url' => 'https://example.com',
    'config' => ['level' => 'active'],
], PushMsgJob::QUEUE_NAME);
```

启动命令：

```bash
# 生产环境
php think queue:listen --queue BarkPushMsg --sleep 3

# 开发环境
php think queue:listen --queue BarkPushMsg --sleep 3 -vvv
```

### 公众号二维码自动回复任务

| 属性 | 值 |
|------|------|
| 任务类 | `tp6/app/wxqrcode/job/OfficeAutoReplyQrcodeProcessJob.php` |
| 队列名 | `OfficeAutoReplyQrcodeProcessJob` |
| 触发入口 | `tp6/app/wxqrcode/service/OfficeAutoReplyQrcodeService.php` 的 `handle` |
| 重试策略 | 超过 1 次尝试即删除任务，避免重复执行 |

启动命令：

```bash
# 生产环境
php think queue:listen --timeout 60 --delay 1 --tries 1 --sleep 5 --queue OfficeAutoReplyQrcodeProcessJob

# 开发环境
php think queue:listen --timeout 60 --delay 1 --tries 1 --sleep 10 --queue OfficeAutoReplyQrcodeProcessJob -vvv
```

### 下载中心任务

| 属性 | 值 |
|------|------|
| 任务类 | `tp6/app/common/job/downloader/ImplementDownloaderTaskJop.php` |
| 队列名 | `downloader` |
| 触发入口 | `tp6/app/common/service/downloader/DownloaderService.php` |
| 重试策略 | 执行完成或异常都会删除任务 |

启动命令：

```bash
php think queue:work --queue downloader
```

## 队列命令

### 监听队列

```bash
# 基本用法
php think queue:listen

# 指定队列名称
php think queue:listen --queue YourQueueName

# 常用参数
php think queue:listen \
    --queue BarkPushMsg \     # 队列名称
    --sleep 3 \               # 无任务时休眠秒数
    --timeout 60 \            # 任务超时时间（秒）
    --tries 3 \               # 最大重试次数
    --delay 5                 # 失败后延迟重试秒数

# 开发环境（显示详细输出）
php think queue:listen --queue BarkPushMsg -vvv
```

### 其他命令

| 命令 | 说明 |
|------|------|
| `php think queue:work` | 处理单个队列任务 |
| `php think queue:restart` | 重启队列工作进程 |
| `php think queue:table` | 创建队列任务表迁移 |
| `php think queue:failed-table` | 创建失败队列表迁移 |
| `php think queue:retry {id}` | 重试指定的失败任务 |
| `php think queue:list-failed` | 列出所有失败任务 |
| `php think queue:forget-failed {id}` | 删除指定失败任务 |
| `php think queue:flush-failed` | 清空所有失败任务 |

## 事件监听

系统提供队列事件订阅机制，可用于日志记录、监控等。

### 订阅类

文件位置：`tp6/app/common/subscribe/queue/QueueSubscribe.php`

| 事件 | 方法 | 说明 |
|------|------|------|
| `JobProcessing` | `onJobProcessing` | 任务开始执行 |
| `JobProcessed` | `onJobProcessed` | 任务执行完成 |
| `JobFailed` | `onJobFailed` | 任务执行失败 |
| `JobExceptionOccurred` | `onJobExceptionOccurred` | 任务异常 |
| `WorkerStopping` | `onWorkerStopping` | Worker 停止 |

```php
class QueueSubscribe
{
    public function onJobProcessing(JobProcessing $event)
    {
        Log::info($event->job->getJobId() . ' starting');
    }

    public function onJobProcessed(JobProcessed $event)
    {
        Log::info($event->job->getJobId() . ' success');
    }

    public function onJobFailed(JobFailed $event)
    {
        Log::info($event->job->getJobId() . ' failed');
    }

    public function onJobExceptionOccurred(JobExceptionOccurred $event)
    {
        Log::info($event->job->getJobId() . ' exception');
    }

    public function onWorkerStopping(WorkerStopping $event)
    {
        Log::info('worker stop, status=' . $event->status);
    }
}
```

### 注册订阅

在 `tp6/app/event.php` 中注册：

```php
return [
    'subscribe' => [
        'app\common\subscribe\queue\QueueSubscribe'
    ],
];
```

> 当前日志默认注释，如需记录可打开注释并按需扩展。

## 运维部署

### Supervisor 配置

推荐使用 Supervisor 管理队列进程，确保进程常驻：

```ini
[program:ztbcms-queue-bark]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/ztbcms/tp6/think queue:listen --queue BarkPushMsg --sleep 3 --tries 3
directory=/path/to/ztbcms/tp6
autostart=true
autorestart=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/ztbcms/runtime/log/queue-bark.log
```

### systemd 配置

```ini
[Unit]
Description=ztbcms queue worker (downloader)
After=network.target

[Service]
Type=simple
WorkingDirectory=/path/to/ztbcms/tp6
ExecStart=/usr/bin/php /path/to/ztbcms/tp6/think queue:work --queue downloader
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

> 建议将路径替换为实际部署路径，并根据队列名称拆分多个服务。每个队列单独起一个进程，便于隔离与定位问题。

## 常见问题排查

| 问题 | 排查方向 |
|------|----------|
| 队列未执行 | 确认 worker 是否启动，检查 `queue_jobs` 表是否有积压任务 |
| 任务执行失败 | 检查 `queue_failed_jobs` 表及任务类的 `failed` 逻辑日志 |
| 任务重复执行 | 确认 `fire()` 方法中成功后是否调用了 `$job->delete()` |
| Worker 异常退出 | 检查 Supervisor/systemd 日志，确认是否配置了自动重启 |

## 最佳实践

### 1. 任务设计原则

- **幂等性**：任务应设计为可重复执行，避免重复执行产生副作用
- **原子性**：一个任务只做一件事，保持任务的简单和独立
- **快速失败**：参数校验放在最前面，无效任务尽早删除

### 2. 重试策略

```php
// 推荐的重试模式
private function retry(Job $job)
{
    if ($job->attempts() > self::MAX_ATTEMPTS) {
        $job->delete();
        return;
    }

    // 指数退避策略
    $delay = self::BASE_DELAY * pow(2, $job->attempts() - 1);
    $job->release($delay);
}
```

### 3. 错误处理

```php
public function fire(Job $job, $data)
{
    try {
        // 业务逻辑
        $this->process($data);
        $job->delete();

    } catch (RetryableException $e) {
        // 可重试的异常
        Log::warning('任务执行失败，准备重试', ['error' => $e->getMessage()]);
        $this->retry($job);

    } catch (\Exception $e) {
        // 不可恢复的异常，直接删除
        Log::error('任务执行失败', ['error' => $e->getMessage()]);
        $job->delete();
    }
}
```

### 4. 日志记录

```php
public function fire(Job $job, $data)
{
    $jobId = $job->getJobId();

    Log::info("任务开始 [{$jobId}]", ['data' => $data]);

    try {
        // 业务逻辑
        $job->delete();
        Log::info("任务完成 [{$jobId}]");

    } catch (\Exception $e) {
        Log::error("任务失败 [{$jobId}]", [
            'error' => $e->getMessage(),
            'attempts' => $job->attempts(),
        ]);
        $this->retry($job);
    }
}
```

### 5. 队列命名规范

- 使用有意义的名称，如 `BarkPushMsg`、`OrderProcess`
- 建议在任务类中使用常量定义队列名称
- 不同类型的任务使用不同的队列，便于独立监控和扩展

### 6. 数据传递

```php
// 只传递必要的标识符，不传递大对象
Queue::push(YourJob::class, [
    'order_id' => $orderId,  // 正确：传递 ID
], 'OrderProcess');

// 在任务中重新查询数据
public function fire(Job $job, $data)
{
    $order = Order::find($data['order_id']);
    if (!$order) {
        $job->delete();
        return;
    }
    // 处理订单
}
```
