# 上传管理

- 前台上传接口：UploadPublicApiController
- 后台上传调用：UploadCenterController

请参考 Elementui 模块的示例

### 水印设置

1. 后台：上传时可以选择是否添加水印
2. 前台：是否添加水印取决于后台配置

### 图片裁剪

1. Elementui 模块的示例调用的是后台上传接口
2. 参数：url  String  
   值：要进行截图的图片地址
3. 设置宽高比  
    参数：width   Int  
    参数：height  Int

```
http://localhost/Upload/UploadCropImage/cropImage?url=/d/file/module_upload_images/2020/05/5ec8b849f1413.jpg&width=1920&height=1080
```