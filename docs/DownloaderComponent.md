# 下载中心（Downloader）组件文档

本文档面向 ZTBCMS 框架使用者，介绍下载中心组件的能力、配置方式与使用方法。

## 目录

- [概述](#概述)
- [组件能力](#组件能力)
- [快速开始](#快速开始)
- [配置项说明](#配置项说明)
- [任务状态与流程](#任务状态与流程)
- [代码调用方式](#代码调用方式)
- [后台接口说明](#后台接口说明)
- [数据表说明](#数据表说明)
- [常见问题排查](#常见问题排查)

## 概述

下载中心用于把远程 URL 文件下载到本地并记录任务状态，支持失败重试与超时处理。

核心实现位置：

- 服务层：`tp6/app/common/service/downloader/DownloaderService.php`
- 下载工具：`tp6/app/common/libs/downloader/DownloaderTool.php`
- 队列任务：`tp6/app/common/job/downloader/ImplementDownloaderTaskJop.php`
- 计划任务脚本：`tp6/app/common/cronscript/Downloader*.php`

## 组件能力

- 异步下载：创建任务后推入 `downloader` 队列执行。
- 去重能力：同 URL（`md5(url)`）只保留一个任务记录。
- 状态追踪：记录待下载、下载中、成功、失败状态与耗时。
- 自动入库：下载成功后自动同步到附件表 `cms_attachment`。
- 失败治理：支持失败重试与超时失败回收。

## 快速开始

### 1. 配置下载中心参数

后台进入下载中心配置页（路由：`/common/downloader.Config/index`），至少配置：

- `downloader_domain`：下载文件访问域名（示例：`http://ztbcms.com`，不要以 `/` 结尾）
- `downloader_timeout`：任务超时时间（秒）
- `downloader_retry_switch`：失败是否自动重试（`1`=重试，`0`=不重试）
- `downloader_retry_num`：最大重试次数

### 2. 启动下载队列 Worker

在 `tp6` 目录执行：

```bash
php think queue:work --queue downloader
```

### 3. 配置计划任务

下载中心依赖以下 Cron 脚本：

- 可选：`app\common\cronscript\DownloaderImplementScript`（轮询执行待下载任务，不推荐主流程依赖）
- 必须：`app\common\cronscript\DownloaderRetryScript`（处理失败重试）
- 必须：`app\common\cronscript\DownloaderProcessTimeoutScript`（处理下载超时任务）

### 4. 检查目录权限

确保目录 `tp6/public/downloader` 可写。

## 配置项说明

默认配置定义见 `tp6/app/install/data/cms.sql`：

| 配置键 | 说明 | 默认值 |
|---|---|---|
| `downloader_retry_switch` | 任务失败是否重试 | `0` |
| `downloader_retry_num` | 最大重试次数 | `3` |
| `downloader_timeout` | 下载超时秒数 | `300` |
| `downloader_domain` | 下载文件访问域名 | 空 |

## 任务状态与流程

状态定义见 `tp6/app/common/model/downloader/DownloaderModel.php`：

| 状态值 | 含义 |
|---|---|
| `10` | 待下载 |
| `20` | 下载中 |
| `30` | 下载成功 |
| `40` | 下载失败 |

执行流程：

1. 调用 `createDownloaderTask($url)` 创建任务（或复用历史任务）。
2. 系统将任务推送到 `downloader` 队列。
3. Worker 执行 `implementDownloaderTask($downloaderId)`，下载并更新状态。
4. 成功时写入文件信息并同步附件表；失败时记录失败原因。
5. Cron 脚本负责失败重试与超时回收。

下载后的保存路径规则：

```text
/public/downloader/{md5(url)前2位}/{文件名}
```

支持类型（按扩展名识别）：

- 图片：`jpg`、`gif`、`png`、`jpeg`、`bmp`
- 视频：`mp4`
- 文件：`pdf`、`doc`、`docx`、`xls`、`xlsx`、`ppt`、`pptx`、`txt`

## 代码调用方式

### 创建下载任务

```php
use app\common\service\downloader\DownloaderService;

$res = DownloaderService::createDownloaderTask('https://example.com/a.pdf');
// $res['status'] 为 true 时，$res['data']['downloader_id'] 可用于后续查询
```

### 立即执行任务

```php
$res = DownloaderService::implementDownloaderTask($downloaderId);
```

### 重试失败任务

```php
$res = DownloaderService::retryDownloaderTask($downloaderId);
```

返回结构统一为：

```php
[
    'status' => bool,
    'code'   => int,
    'data'   => mixed,
    'msg'    => string
]
```

## 后台接口说明

### 下载中心面板

控制器：`tp6/app/common/controller/downloader/Panel.php`

| 方法 | `_action` | 说明 |
|---|---|---|
| `GET` | `list` | 查询当前面板任务 |
| `POST` | `submit` | 创建下载任务（参数：`url`） |
| `POST` | `implement` | 执行任务（参数：`downloader_id`） |
| `POST` | `retry` | 重试任务（参数：`downloader_id`） |
| `POST` | `delete` | 删除任务（参数：`downloader_id`） |

### 下载记录页

控制器：`tp6/app/common/controller/downloader/Log.php`

| 方法 | `_action` | 说明 |
|---|---|---|
| `GET` | `list` | 按关键词/状态分页查询 |
| `POST` | `implement` | 执行任务 |
| `POST` | `retry` | 重试任务 |
| `POST` | `delete` | 删除任务 |

## 数据表说明

表结构定义见 `tp6/app/common/install/Common.sql`。

主要表：`cms_downloader`

关键字段：

- 任务字段：`downloader_id`、`downloader_url`、`downloader_url_hash`
- 状态字段：`downloader_state`、`downloader_result`、`downloader_duration`
- 文件字段：`file_name`、`file_path`、`file_url`、`file_thumb`、`file_hash`、`file_size`、`file_ext`
- 重试字段：`downloader_implement_num`、`downloader_next_implement_time`
- 时间字段：`create_time`、`update_time`、`delete_time`

## 常见问题排查

### 1. 任务一直不执行

- 检查是否已启动 Worker：`php think queue:work --queue downloader`
- 检查队列表是否有积压任务（见队列组件文档）

### 2. 提示“文件类型暂不支持”

- 下载中心按扩展名识别类型，URL 无扩展名时可能被判定为不支持
- 可在业务层补充文件名再提交任务

### 3. 文件下载成功但访问地址不可用

- 检查 `downloader_domain` 是否正确配置
- 检查是否误加了结尾 `/`

### 4. 任务长时间停留“下载中”

- 检查是否已配置 `DownloaderProcessTimeoutScript`
- 检查 `downloader_timeout` 是否过大

### 5. 重试不生效

- 检查 `downloader_retry_switch` 是否开启
- 检查 `downloader_retry_num` 是否大于当前重试次数
- 检查 `DownloaderRetryScript` 是否在调度中运行
