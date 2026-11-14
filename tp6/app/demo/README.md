### 示例模块

后台常用页面示例，供开发时参考。**仅用于开发环境，生产环境请勿安装。**

```
│ 示例
│
├─ 打开新页面      demo/admin.router/openNewIframe
├─ 页面
│  ├─ 列表页       demo/admin.page/list
│  ├─ 表单页       demo/admin.page/form
│  └─ 图片预览     demo/admin.page/image
├─ Iconfont
│  ├─ 内置Iconfont      demo/admin.iconfont/aliIconFont
│  └─ Element Iconfont  demo/admin.iconfont/elementIconfont
├─ 图片处理
│  ├─ 图片裁剪     demo/admin.image/index
│  └─ 图片裁剪面板 demo/admin.image/cropImage（弹窗页，不在菜单显示）
└─ 上传示例
   ├─ 上传面板示例 demo/admin.upload/demo
   └─ 直传示例     demo/admin.upload/directUploadDemo（演示详情页，不在菜单显示）
```

#### 图片裁剪

裁剪面板通过 `common/upload.upload/getUploadToken` 获取临时上传凭证，再调用 `common/upload.api/upload` 上传，完成后向父页面派发 `ZTBCMS_IMAGE_CROP_FILE` 事件（`event.detail` 包含 `name`、`url`）。

裁剪跨域图片（如 OSS）时，图片域名需配置允许本站的 CORS，否则无法导出裁剪结果。
