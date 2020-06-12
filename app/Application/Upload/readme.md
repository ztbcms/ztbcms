# 上传管理

- 前台上传接口：UploadPublicApiController
- 后台上传调用：UploadCenterController

请参考 Elementui 模块的示例

### 水印设置

1. 后台：上传时可以选择是否添加水印
2. 前台：是否添加水印取决于后台配置


### 调起图片裁剪

```javascript
var url = "/Upload/UploadCropImage/cropImage";
//传入图片地址
if(img_url != 0){
    // url 指定图片，可以为空 width/height分别指定需要裁剪的宽高比
    url = url + '?url=' + encodeURIComponent(img_url) + '&width=100&height=100';
}
//直接打开新页面
layer.open({
    type: 2,
    title: '图片裁剪',
    content: url,
    area: ['80%', '75%']
})
```

图片裁剪插件，来源地址：https://github.com/fengyuanchen/cropperjs/tree/master/dist