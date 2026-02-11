# 计划任务（Cron）组件文档

本文档面向 ZTBCMS 框架使用者，介绍 Cron 组件的使用方式、开发规范和部署建议。

## 目录

- [概述](#概述)
- [组件能力](#组件能力)
- [快速开始](#快速开始)
- [开发一个 Cron 任务](#开发一个-cron-任务)
- [调度规则说明](#调度规则说明)
- [命令行用法](#命令行用法)
- [HTTP 触发模式](#http-触发模式)
- [数据表说明](#数据表说明)
- [常见问题排查](#常见问题排查)
- [最佳实践](#最佳实践)

## 概述

ZTBCMS 内置了轻量级 Cron 调度能力，用于执行周期性任务（如日志清理、消息处理、下载任务巡检等）。

Cron 组件由以下部分组成：

- 任务定义：`app/<module>/cronscript/*.php`
- 调度命令：`cron:run`、`cron:exec`、`cron:clean`
- 调度入口（HTTP）：`/common/cron.index/index/cron_secret_key/{secret_key}`
- 配置与日志：`cms_cron*` 相关表

## 组件能力

- 支持多种循环类型：`month`、`week`、`day`、`hour`、`now`
- 支持命令行和 HTTP 两种调度入口
- 支持后台可视化管理（启停、密钥、任务列表、运行日志）
- 内置并发锁（`runtime/cron.lock`）避免重复调度
- 自动记录任务执行日志和调度日志

## 快速开始

### 1. 确认安装与启用

1. 已执行 `common` 模块安装 SQL（会创建 `cms_cron`、`cms_cron_log`、`cms_cron_scheduling_log`、`cms_cron_config`）。
2. 后台进入 `系统管理 -> 计划任务`，确认 `启用状态` 为启用。
3. 设置 `secret_key`（HTTP 触发模式需要）。

### 2. 创建任务类

在模块下创建 `cronscript` 目录，例如：`tp6/app/demo/cronscript/SyncOrderScript.php`。

```php
<?php

namespace app\demo\cronscript;

use app\common\cronscript\CronScript;
use think\facade\Log;

class SyncOrderScript extends CronScript
{
    public function run($cronId)
    {
        try {
            // 在这里编写你的业务逻辑
            $count = 0;

            return self::createReturn(true, [
                'cron_id' => $cronId,
                'count' => $count,
            ], '同步完成');
        } catch (\Throwable $e) {
            Log::error('SyncOrderScript 执行失败: ' . $e->getMessage());
            throw $e;
        }
    }
}
```

### 3. 在后台新增计划任务

在 `系统管理 -> 计划任务 -> 任务列表 -> 新增编辑任务` 中配置：

- 任务标题：如“订单同步”
- 执行时间：选择循环类型和时间参数
- 开启计划：选择“开启”
- 执行文件：选择 `app\demo\cronscript\SyncOrderScript`

### 4. 配置系统 Crontab 调度

推荐每分钟触发一次调度器：

```bash
* * * * * cd /path/to/ztbcms/tp6 && php think cron:run >> /tmp/ztbcms-cron.log 2>&1
```

> 建议优先使用命令行模式，稳定性更好。

## 开发一个 Cron 任务

### 目录与命名

- 目录：`tp6/app/<module>/cronscript/`
- 命名：`PascalCase`，如 `CleanDataScript`
- 命名空间：`app\<module>\cronscript`

### 基类与方法

- 推荐继承：`app\common\cronscript\CronScript`
- 必须实现：`run($cronId)`
- 推荐返回：`self::createReturn($status, $data, $msg)`

### 失败判定说明

当前框架在调度日志中主要以“抛异常/报错”作为失败依据。  
如果你希望该次执行被标记为失败并进入错误统计，建议在失败场景主动 `throw` 异常，而不是只返回 `status=false`。

### 任务发现机制

后台“执行文件”下拉框会自动扫描模块下的 `cronscript` 目录。  
新增类后若未显示，先确认：

- 文件路径和命名空间是否正确
- 模块是否已安装/可被系统识别
- 类名是否可自动加载

## 调度规则说明

Cron 任务通过 `loop_type + loop_daytime` 表达循环规则，`loop_daytime` 格式固定为 `日-时-分`。

| loop_type | 含义 | 参数来源（后台） | loop_daytime 示例 |
|---|---|---|---|
| `month` | 每月 | `month_day` + `month_hour` | `15-2-0`（每月15日2点） |
| `week` | 每周 | `week_day` + `week_hour` | `1-10-0`（每周一10点） |
| `day` | 每日 | `day_hour` | `0-3-0`（每天3点） |
| `hour` | 每小时 | `hour_minute` | `0-0-30`（每小时30分） |
| `now` | 每隔 | `now_time` + `now_type` | `0-1-0`（每隔1小时） |

补充说明：

- `week_day` 取值：`0~6`，其中 `0` 表示周日。
- `month_day=99` 表示“每月最后一天”。

## 命令行用法

命令定义在 `tp6/app/common/config/console.php`。

### 启动调度

```bash
cd tp6
php think cron:run
```

可选参数：

```bash
php think cron:run --progress 1
```

`--progress 1` 会输出调度进度信息。

### 执行指定任务

按任务 ID 执行：

```bash
cd tp6
php think cron:exec --id 1
```

按类名执行：

```bash
cd tp6
php think cron:exec --class "app\\common\\cronscript\\DeleteCronLogScript"
```

### 清理锁文件

```bash
cd tp6
php think cron:clean
```

用于清理 `runtime/cron.lock`（例如异常中断后遗留锁文件）。

## HTTP 触发模式

HTTP 入口：

```text
/common/cron.index/index/cron_secret_key/{secret_key}
```

可用以下方式触发：

```bash
curl "https://your-domain/common/cron.index/index/cron_secret_key/你的密钥"
```

返回示例：

```json
{"used_time":2,"msg":"Cron status: finish"}
```

说明：

- `secret_key` 不匹配会返回 `Secret key invalidated`
- 调度被锁定时返回 `Cron is Locked`
- 功能关闭时返回 `Cron status: stop`

## 数据表说明

表结构定义位于：`tp6/app/common/install/Common.sql`

| 表名 | 说明 |
|---|---|
| `cms_cron` | 任务定义（执行类、循环规则、下次执行时间） |
| `cms_cron_log` | 单个任务执行日志 |
| `cms_cron_scheduling_log` | 每次调度汇总日志 |
| `cms_cron_config` | 组件配置（是否启用、密钥） |

## 常见问题排查

### 1. 任务不执行

- 检查 `cms_cron_config.enable_cron` 是否为 `1`
- 检查任务是否开启（`isopen=1`）
- 检查 `next_time` 是否小于当前时间
- 检查系统 crontab 是否实际触发 `php think cron:run`

### 2. 提示已锁定

出现 `Cron is Locked` 或 `已存在文件锁 cron.lock` 时：

1. 先确认是否有任务正在执行
2. 若确认无执行进程，运行 `php think cron:clean` 清理锁

### 3. 类找不到

- 检查类名是否完整（如 `app\demo\cronscript\SyncOrderScript`）
- 检查文件名、命名空间与目录是否一致
- 检查自动加载是否正常（必要时执行 `composer dump-autoload`）

### 4. 执行失败但日志显示成功

框架当前以异常作为失败判定。  
业务失败时请主动抛出异常，确保错误计数和失败日志准确。

## 最佳实践

- 任务逻辑尽量短小，避免长时间阻塞调度器。
- 保证任务幂等，允许重复触发时结果可控。
- 重任务（大文件下载、批量第三方调用）建议拆到队列组件。
- `cron:run` 建议每分钟触发一次，由组件内部判断哪些任务到期执行。
- 定期清理日志（可使用内置 `DeleteCronLogScript`，配合 `config/cron.php` 的 `delete_log_days`）。
