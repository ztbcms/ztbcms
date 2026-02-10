# ZTBCMS 上传组件使用教程

本文档基于当前代码实现（`tp6/app/common/controller/upload`、`tp6/app/common/service/upload`、`tp6/app/common/view/upload`）整理，覆盖后台面板上传、前台 API 上传与直传流程。

## 1. 能力概览

当前上传系统支持：

- 多类型文件：图片、视频、普通文件。
- 多存储驱动：`Local`、`Aliyun`、`Qiniu`。
- 统一上传面板：通过 `common/upload.panel/index` 按模块切换。
- 统一 API 上传：通过单一接口自动识别文件类型。
- 直传能力：支持本地「经服务器中转」和云端直传后回写附件记录。

## 2. 后台使用（推荐）

### 2.1 统一上传面板入口

历史组件 `select-image/select-video/select-file/select-ue-image` 已下线，统一改为 Iframe 面板：

`/common/upload.panel/index?module=image|video|file`

可选参数：

- `max_select`：最大选择数量，默认 `1`。
- `max_upload`：单次最多上传数量。
- `callback`：回调事件名，默认随模块自动设置。
- `is_private`：是否私有读（`1` 私有，`0` 公开）。

示例：

```javascript
// 图片单选
layer.open({
    type: 2,
    title: '选择图片',
    content: "{:api_url('common/upload.panel/index')}?module=image&max_select=1",
    area: ['720px', '550px']
});

// 视频多选
layer.open({
    type: 2,
    title: '选择视频',
    content: "{:api_url('common/upload.panel/index')}?module=video&max_select=5",
    area: ['720px', '550px']
});

// 文件多选
layer.open({
    type: 2,
    title: '选择文件',
    content: "{:api_url('common/upload.panel/index')}?module=file&max_select=5",
    area: ['720px', '550px']
});
```

### 2.2 父页面回调事件

上传面板通过 `window` 事件回传选中文件：

- 图片：`ZTBCMS_UPLOAD_IMAGE`
- 视频：`ZTBCMS_UPLOAD_VIDEO`
- 文件：`ZTBCMS_UPLOAD_FILE`

示例：

```javascript
window.addEventListener('ZTBCMS_UPLOAD_IMAGE', function(event) {
    var files = event.detail.files || [];
    console.log('收到图片', files);
});

window.addEventListener('ZTBCMS_UPLOAD_VIDEO', function(event) {
    var files = event.detail.files || [];
    console.log('收到视频', files);
});

window.addEventListener('ZTBCMS_UPLOAD_FILE', function(event) {
    var files = event.detail.files || [];
    console.log('收到文件', files);
});
```

## 3. API 接口说明

`common/upload.api/*` 接口启用 JWT 鉴权，请在 Header 中传入：

`Authorization: Bearer <token>`

### 3.1 获取前台上传配置

- 路径：`GET /common/upload.api/getUploadConfig`
- 用途：获取前台上传大小、后缀白名单。

返回示例：

```json
{
    "status": true,
    "code": 200,
    "msg": "",
    "data": {
        "max_size": 10240,
        "max_size_bytes": 10485760,
        "allow_ext": ["jpg", "png", "gif"],
        "allow_ext_str": "jpg|png|gif"
    }
}
```

### 3.2 统一上传接口（保存附件记录）

- 路径：`POST /common/upload.api/upload`
- 表单：
  - `file`（必填）
  - `is_private`（可选，`0|1`）
  - `driver`（可选，`Local|Aliyun|Qiniu`，不传则取系统配置）

说明：

- 接口自动识别文件后缀并推断 `module`（image/video/file）。
- 成功后会写入附件表并返回附件核心字段。

### 3.3 本地直传（仅上传文件，不保存记录）

- 路径：`POST /common/upload.api/uploadLocalDirect`
- 表单：
  - `file`（必填）
  - `module`（可选）
  - `is_private`（可选）

说明：

- 该接口固定使用本地驱动 `Local`。
- 返回 `filepath/fileurl` 等信息，但不会创建附件记录。

### 3.4 获取直传凭证

- 路径：`GET /common/upload.api/getDirectUploadCredential`
- 参数：
  - `filename`（必填，用于识别后缀与模块）
  - `driver`（可选，`Local|Aliyun|Qiniu`）

说明：

- `Local`：返回 `upload_url=/common/upload.api/uploadLocalDirect`。
- `Aliyun`：返回 STS 临时凭证（需配置 `attachment_aliyun_sts_role_arn` 才支持）。
- `Qiniu`：返回上传 `token`、`object_key`、`upload_url` 等参数。

### 3.5 保存直传记录

- 路径：`POST /common/upload.api/saveDirectUploadRecord`
- 参数：
  - `filepath`（必填）
  - `filename`（必填）
  - `filesize`（可选）
  - `fileext`（可选）
  - `module`（可选）
  - `group_id`（可选）
  - `is_private`（可选）
  - `driver`（可选）

说明：

- 直传成功后应调用该接口入库。
- 若检测到同用户/同驱动/同路径的附件记录，接口会直接返回已有记录，避免重复写入。

## 4. 后台获取上传 Token

后台页面可通过以下接口获取临时 JWT：

- 路径：`GET /common/upload.upload/getUploadToken`
- 默认 `user_type=admin`，默认 `user_id=当前登录管理员`。
- 返回 token 默认 1 小时有效。

## 5. 存储驱动与配置

上传驱动配置入口：后台「系统设置 -> 上传设置」。

### 5.1 驱动列表

- `Local`：本地存储。
- `Aliyun`：阿里云 OSS。
- `Qiniu`：七牛云。

### 5.2 关键配置项

通用：

- `attachment_driver`
- `attachment_direct_file_dir_template`
- `uploadmaxsize/uploadallowext`
- `qtuploadmaxsize/qtuploadallowext`

阿里云：

- `attachment_aliyun_key_id`
- `attachment_aliyun_key_secret`
- `attachment_aliyun_endpoint`
- `attachment_aliyun_bucket`
- `attachment_aliyun_sts_role_arn`
- `attachment_aliyun_privilege`
- `attachment_aliyun_expire_time`

七牛云：

- `attachment_qiniu_access_key`
- `attachment_qiniu_secret_key`
- `attachment_qiniu_bucket`
- `attachment_qiniu_domain`
- `attachment_qiniu_privilege`
- `attachment_qiniu_expire_time`

## 6. 直传流程（建议）

以前端/小程序为例，推荐流程：

1. 调用 `getDirectUploadCredential` 获取凭证。
2. 根据 `driver` 分支上传：
   - `Local`：调用 `uploadLocalDirect` 上传文件。
   - `Aliyun`/`Qiniu`：直接上传到云端。
3. 上传成功后调用 `saveDirectUploadRecord` 写入附件表。
4. 使用返回的 `aid/fileurl/filethumb` 进行业务绑定。

## 7. 返回结构与错误处理

上传接口统一返回结构：

```json
{
    "status": true,
    "code": 200,
    "data": {},
    "msg": ""
}
```

常见失败场景：

- JWT 缺失或过期（401）。
- 文件过大或后缀不合法。
- 驱动不支持直传（例如阿里云未配置 `STS RoleArn`）。
- 直传完成但未调用 `saveDirectUploadRecord`，导致附件库无记录。

