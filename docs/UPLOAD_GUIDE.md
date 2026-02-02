# ZTBCMS 上传组件使用教程

本文档详细介绍了 ZTBCMS 系统中上传组件的使用方法，涵盖后台 Vue 组件调用、Iframe 弹窗调用以及前台 API 直接调用三种方式。

## 1. 组件概述

ZTBCMS 提供了统一的上传服务，支持图片、视频和普通文件的上传。核心功能包括：

*   **多类型支持**：图片、视频、文件（doc, pdf, xls 等）。
*   **多存储驱动**：支持本地存储和阿里云 OSS。
*   **权限控制**：支持公开读和私有读（需配合权限验证）。
*   **资源管理**：内置资源库，支持分组管理和历史资源选择。

## 2. 后台使用篇

### 2.1 方法一：Vue 组件调用（推荐）

在后台管理页面中，推荐使用系统封装好的 Vue 组件，交互体验最佳。

#### 引入组件模板
在你的视图文件（.php）底部引入需要的组件模板：

```html
<!-- 引入图片选择组件 -->
{ztbcms:include file="common/@/components/select-image"}

<!-- 引入视频选择组件 -->
{ztbcms:include file="common/@/components/select-video"}

<!-- 引入文件选择组件 -->
{ztbcms:include file="common/@/components/select-file"}
```

#### 使用组件
在 Vue 实例中注册并使用组件。

**HTML 代码：**
```html
<div id="app" v-cloak>
    <!-- 触发按钮 -->
    <el-button type="primary" @click="show_image = true">上传图片</el-button>
    <el-button type="primary" @click="show_video = true">上传视频</el-button>

    <!-- 图片选择组件 -->
    <!-- :show 控制显示隐藏 -->
    <!-- :is_private 是否私有读（1:是, 0:否） -->
    <!-- :max_upload 最大上传数量 -->
    <!-- @confirm 确认回调 -->
    <!-- @close 关闭回调 -->
    <select-image 
        :show="show_image" 
        :is_private="0" 
        :max_upload="9"
        @confirm="confirmImage"
        @close="show_image=false">
    </select-image>

    <!-- 视频选择组件 -->
    <select-video 
        :show="show_video" 
        @confirm="confirmVideo"
        @close="show_video=false">
    </select-video>
</div>
```

**JavaScript 代码：**
```javascript
new Vue({
    el: '#app',
    data: {
        show_image: false,
        show_video: false,
        imageList: [],
        videoList: []
    },
    methods: {
        // 图片上传回调
        confirmImage: function (files) {
            console.log('选择的文件列表:', files);
            // files 结构示例:
            // [{
            //     aid: 1,
            //     fileurl: "http://...",
            //     filename: "example.jpg",
            //     ...
            // }]
            if (files && files.length > 0) {
                // 处理你的业务逻辑，例如将图片添加到列表中
                this.imageList = this.imageList.concat(files);
            }
        },
        // 视频上传回调
        confirmVideo: function (files) {
            console.log('选择的视频列表:', files);
            // 处理视频逻辑
        }
    }
});
```

### 2.2 方法二：Iframe 弹窗调用

如果你无法直接使用 Vue 组件，或者是在某些原生 JS 场景下，可以使用 `layer.open` 配合 Iframe 打开上传面板。

#### 调用代码

```javascript
// 打开图片上传窗口
layer.open({
    type: 2,
    title: '选择图片',
    content: "{:api_url('common/upload.panel/imageUpload')}", // 或 videoUpload, fileUpload
    area: ['720px', '550px'],
});
```

#### 接收回调
在父页面（调用页面）监听 window 事件来获取上传结果。

```javascript
/* 监听图片上传事件 */
window.addEventListener('ZTBCMS_UPLOAD_IMAGE', function(event) {
    var files = event.detail.files;
    console.log('收到图片:', files);
    // 处理文件逻辑
});

/* 监听视频上传事件 */
window.addEventListener('ZTBCMS_UPLOAD_VIDEO', function(event) {
    var files = event.detail.files;
    console.log('收到视频:', files);
});

/* 监听文件上传事件 */
window.addEventListener('ZTBCMS_UPLOAD_FILE', function(event) {
    var files = event.detail.files;
    console.log('收到文件:', files);
});
```

---

## 3. 前台/API 使用篇

在开发前台应用或小程序时，可以直接调用 API 接口进行文件上传。

### 3.1 接口地址

| 资源类型 | 接口地址 (TP6 路由) |
| :--- | :--- |
| **图片** | `/common/upload.api/imageUpload` |
| **视频** | `/common/upload.api/videoUpload` |
| **文件** | `/common/upload.api/fileUpload` |

### 3.2 请求参数 (POST)

**请求头 (Headers):**

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| **Authorization** | String | 是 | JWT 令牌,格式: `Bearer {token}` |

**表单参数 (Form Data):**

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| **file** | File | 是 | 上传的文件对象 |
| **is_private** | Int | 否 | 是否私有读。`1`:私有, `0`:公开(默认) |

> [!IMPORTANT]
> 所有上传接口均已启用 **JWT 强制鉴权**,请求时必须在 Header 中携带有效的 `Authorization` 令牌,否则将返回 401 未授权错误。

### 3.3 用户信息追踪

上传接口会自动从 JWT Token 中提取用户信息并记录到附件表中:

| 字段 | 说明 | 来源 |
| :--- | :--- | :--- |
| **user_type** | 用户类型 | JWT Token 的 `user_type` 字段,默认为 `user` |
| **user_id** | 用户 ID | JWT Token 的 `uid` 字段 |

**示例:**

- **前台用户上传**: JWT 中包含 `{"uid": 123, "user_type": "user"}`,则记录为 `user_type=user, user_id=123`
- **后台管理员上传**: JWT 中包含 `{"uid": 1, "user_type": "admin"}`,则记录为 `user_type=admin, user_id=1`

> [!NOTE]
> 后台管理页面(如上传演示页面)会自动调用 `/common/upload.upload/getUploadToken` 接口获取临时 JWT Token,无需手动处理。

### 3.3 返回结果

成功响应示例：
```json
{
    "status": true,
    "msg": "上传成功",
    "data": {
        "aid": 123,
        "filename": "demo.jpg",
        "module": "image",
        "fileurl": "http://domain.com/upload/demo.jpg",
        "filethumb": "http://domain.com/upload/demo.jpg"
    }
}
```

失败响应示例：
```json
{
    "status": false,
    "msg": "文件大小超出限制",
    "data": null
}
```

### 3.4 前端调用示例 (Element UI)

```html
<el-upload
    action="{:api_url('common/upload.api/imageUpload')}"
    accept="image/*"
    :headers="uploadHeaders"
    :on-success="handleSuccess"
    :on-error="handleError"
    :show-file-list="false"
    name="file">
    <el-button size="small" type="primary">点击上传</el-button>
</el-upload>

<script>
    // ... Vue data
    data() {
        return {
            uploadHeaders: {
                'Authorization': 'Bearer ' + localStorage.getItem('token') // 从本地存储获取 JWT token
            }
        }
    },
    // ... Vue methods
    handleSuccess(res, file) {
        if (res.status) {
            console.log('上传成功', res.data);
            this.imageUrl = res.data.fileurl;
        } else {
            this.$message.error(res.msg);
        }
    },
    handleError(err, file) {
        if (err.status === 401) {
            this.$message.error('未授权,请先登录');
        } else {
            this.$message.error('上传失败');
        }
    }
    // ...
</script>
```

## 4. 配置说明

上传相关的配置（如允许的后缀、文件大小限制、OSS配置等）可以在后台管理系统中进行修改。

*   **路径**：系统设置 -> 上传设置
*   **私有读说明**：开启私有读 (`is_private=1`) 后，上传的文件 `fileurl` 会带有签名参数，且有时效性。每次访问需通过接口重新获取访问地址。

## 5. 权限与安全

*   **后台上传**:默认需要管理员登录权限。
*   **API 上传**:已启用 **JWT 强制鉴权**,所有上传接口 (`imageUpload`、`videoUpload`、`fileUpload`) 必须携带有效的 JWT 令牌才能访问。未授权请求将返回 401 错误。

### 5.1 获取 JWT 令牌

用户需要先通过登录接口获取 JWT 令牌,然后在上传请求的 Header 中携带:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 5.2 鉴权失败响应

当 JWT 令牌无效、过期或缺失时,接口将返回:

```json
{
    "status": false,
    "code": 401,
    "msg": "凭证不能为空", // 或 "Token已过期" 等
    "data": null
}
```
