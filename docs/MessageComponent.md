# Message 组件文档

## 概述

Message 组件用于统一管理“消息记录 + 多发送器投递 + 发送日志”流程，适合站内信、短信、微信通知等场景。

核心能力：
- 统一消息模型：消息内容先落库，再异步/手动处理
- 多发送器编排：同一条消息可按顺序调用多个发送器
- 失败可重试：支持计划任务自动重试和后台手动重试
- 发送去重：默认不重复执行已成功的发送器

核心代码位置：
- `tp6/app/common/libs/message/MessageUnit.php`
- `tp6/app/common/libs/message/SenderUnit.php`
- `tp6/app/common/model/message/MessageModel.php`
- `tp6/app/common/model/message/MessageSendLogModel.php`
- `tp6/app/common/controller/message/Message.php`
- `tp6/app/common/cronscript/HandleMessageScript.php`

## 组件结构

Message 组件由 3 层组成：

1. 消息定义层（MessageUnit）
- 负责定义消息内容字段、消息类型、发送器列表
- 通过 `createMessage()` 创建消息记录

2. 发送执行层（SenderUnit）
- 每个发送器只做一件事（如短信、微信）
- 通过 `doSend(MessageModel $message): bool` 返回发送成功/失败

3. 消息模型层（MessageModel / MessageSendLogModel）
- 负责调度发送器、记录日志、处理重试状态

## 数据表

安装 SQL 在 `tp6/app/common/install/Common.sql`，涉及两张表：

1. `cms_message_msg`（消息主表）
- 保存消息内容、发送/接收方、处理状态、处理次数等

2. `cms_message_send_log`（发送日志表）
- 保存每个发送器的执行结果

`cms_message_msg` 关键字段：
- `process_status`：0 未处理，1 已处理，2 处理中（当前代码主要使用 0/1）
- `process_num`：处理次数
- `class`：消息类名（例如 `app\common\message\units\SimpleMessage`）
- `send_time`：本次处理全部成功时写入时间戳，否则为 0

## 快速开始

### 1. 定义发送器

```php
<?php

namespace app\common\message\senders;

use app\common\libs\message\SenderUnit;
use app\common\model\message\MessageModel;

class CustomSmsSender extends SenderUnit
{
    public function doSend(MessageModel $message): bool
    {
        // 读取消息字段执行发送逻辑
        // 成功返回 true；失败返回 false，并可调用 $this->setError('失败原因')
        return true;
    }
}
```

### 2. 定义消息类

```php
<?php

namespace app\common\message\units;

use app\common\libs\message\MessageUnit;
use app\common\message\senders\CustomSmsSender;

class OrderPaidMessage extends MessageUnit
{
    public function getSenders(): array
    {
        return [
            new CustomSmsSender(),
        ];
    }
}
```

### 3. 创建消息记录

```php
use app\common\message\units\OrderPaidMessage;
use app\common\model\message\MessageModel;

$message = new OrderPaidMessage();
$message->setTitle('订单支付成功');
$message->setContent('订单#20260211001 已支付');
$message->setTarget('order:20260211001');
$message->setTargetType('order');
$message->setSender('system');
$message->setSenderType('system');
$message->setReceiver('user:10001');
$message->setReceiverType('member');
$message->setType(MessageModel::TYPE_NOTICE);
$message->createMessage();
```

创建后消息仅入库，不会立刻调用发送器。处理方式见下文“消息处理流程”。

## 消息处理流程

### 自动处理（推荐）

使用计划任务执行 `app\common\cronscript\HandleMessageScript`：

- 脚本只处理 `process_status = 0` 且 `process_num < 3` 的消息
- 每次处理调用 `MessageModel::handMessage(false)`

在后台进入“系统管理 -> 计划任务 -> 任务列表”，新增任务并选择：
- `cron_file`: `app\common\cronscript\HandleMessageScript`

### 手动处理

后台“系统管理 -> 消息管理 -> 消息列表”点击“执行”，会调用强制处理（`handMessage(true)`）。

强制处理与自动处理差异：
- 自动处理（`force=false`）：已成功发送器会跳过（不重复发送）
- 强制处理（`force=true`）：忽略去重，发送器会重新执行

## 关键机制

### 发送器串行执行

`handMessage()` 会遍历 `getSenders()` 返回的发送器数组，顺序执行。

### 成功去重

`sendMessage()` 默认会检查日志：
- 同一消息 + 同一发送器只要存在成功日志，就不再重复执行

### 失败策略

只要任一发送器返回 `false` 或抛异常：
- 当前消息会保持未处理状态（`process_status=0`）
- `send_time` 置为 0
- 由后续计划任务继续重试（最多 3 次）

### 类名约束

`cms_message_msg.class` 必须可实例化，且应实现 `getSenders()`（建议继承 `MessageUnit`）。

注意：
- 运行时会 `new $this->class`，不支持必须参数的构造函数
- `getSenders()` 不应依赖运行态上下文，建议只返回发送器实例列表

## 后台接口（供二次开发）

控制器：`tp6/app/common/controller/message/Message.php`

1. 消息列表
- `GET /common/message.message/index?_action=getMessageList`
- 参数：
  - `search_message`（数组，支持 target/target_type/sender/sender_type/receiver/receiver_type 模糊查询）
  - `datetime`（数组，开始/结束时间，格式 `Y-m-d H:i:s`）
  - `page`

2. 强制执行消息
- `POST /common/message.message/index`
- 参数：
  - `_action=handMessage`
  - `message_id`

3. 发送日志列表
- `GET /common/message.message/sendLog?_action=getSendLogList`
- 参数：
  - `message_id`（可选）
  - `page`

4. 再次执行某条发送日志对应发送器
- `POST /common/message.message/sendLog`
- 参数：
  - `_action=handleAgainLog`
  - `log_id`

5. 新建消息
- `POST /common/message.message/addMessage?_action=addMessage`
- 参数：
  - `title`、`content`、`target`、`target_type`
  - `sender`、`sender_type`
  - `receiver`、`receiver_type`
  - `type`
  - `newClass`（消息类全限定名，写入 `class` 字段）

## 返回格式

组件接口统一使用 `createReturn()`，结构：

```php
[
    'status' => bool,
    'code'   => int,
    'data'   => mixed,
    'msg'    => string
]
```

## 常见问题

1. 消息创建成功但未发送
- 原因：只入库未处理
- 处理：检查计划任务是否配置 `app\common\cronscript\HandleMessageScript`，或在消息列表手动执行

2. 同一条消息没有重复发送
- 原因：默认有“成功日志去重”
- 处理：使用强制执行（后台“执行”按钮）可重新发送

3. 新建消息时报“请输入正确的实例化的类名”
- 原因：`newClass` 不存在或类自动加载失败
- 处理：确认类名、命名空间、自动加载配置正确

