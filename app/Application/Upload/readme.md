## 上传管理

- 前台上传接口：UploadPublicApiController
- 后台上传调用：UploadCenterController

请参考 Elementui 模块的示例


### Aliyun OSS 存储配置

打开 **设置-系统设置-附件配置** 填入相应的OSS配置。建议使用 使用RAM账号 的配置


![wCx54S.png](https://s1.ax1x.com/2020/09/03/wCx54S.png)



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


### 上传图片

#### 1. 使用layer 调用

```
layer.open({
            type: 2,
            title: '',
            closeBtn: false,
            content: '{:U("Upload/UploadCenter/imageUploadPanel")}',
            area: ['670px', '550px']  //固定宽高，要不然样式会影响。
        })

```

#### 2. 绑定回调方法，下面例子在 vue 的 mounted 定义
`window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.ZTBCMS_UPLOAD_IMAGE.bind(this))`;

#### 3. 回调处理

```
  ZTBCMS_UPLOAD_IMAGE: function(event){
                        //根据自己的需要，获取选中数组中的数据
                        var files = event.detail.files;
                        that.form.icon = files[0].url;
                    },
```

#### 4. event.detail.files【array】

字段 | 类型 | 备注
:-----------: | :-----------: | :-----------:
 aid        |     int    |       文件id
 url        |     string    |      文件url
 name        |     string    |       文件名称


### 上传视频

#### 1. 使用layer 调用

```
layer.open({
            type: 2,
            title: '',
            closeBtn: false,
            content: '{:U("Upload/UploadCenter/videoUploadPanel")}',
            area: ['670px', '550px']  //固定宽高，要不然样式会影响。
        })

```

#### 2. 绑定回调方法，下面例子在 vue 的 mounted 定义
`window.addEventListener('ZTBCMS_UPLOAD_VIDEO', this.ZTBCMS_UPLOAD_VIDEO.bind(this))`;

#### 3. 回调处理

```
  ZTBCMS_UPLOAD_VIDEO: function(event){
                        //根据自己的需要，获取选中数组中的数据
                        var files = event.detail.files;
                        that.form.icon = files[0].url;
                    },
```

#### 4. event.detail.files【array】

字段 | 类型 | 备注
:-----------: | :-----------: | :-----------:
 aid        |     int    |       文件id
 url        |     string    |      文件url
 name        |     string    |       文件名称
 filethumb        |     string    |       文件缩略图

#### 5. TODO  
- 缩略图生成
- OSS 自动获取缩略图



### 上传文件

#### 1. 使用layer 调用

```
layer.open({
            type: 2,
            title: '',
            closeBtn: false,
            content: '{:U("Upload/UploadCenter/fileUploadPanel")}',
            area: ['670px', '550px']  //固定宽高，要不然样式会影响。
        })

```

#### 2. 绑定回调方法，下面例子在 vue 的 mounted 定义
`window.addEventListener('ZTBCMS_UPLOAD_FILE', this.ZTBCMS_UPLOAD_FILE.bind(this))`;

#### 3. 回调处理

```
  ZTBCMS_UPLOAD_FILE: function(event){
                        //根据自己的需要，获取选中数组中的数据
                        var files = event.detail.files;
                        that.form.icon = files[0].url;
                    },
```

#### 4. event.detail.files【array】

字段 | 类型 | 备注
:-----------: | :-----------: | :-----------:
 aid        |     int    |       文件id
 url        |     string    |      文件url
 name        |     string    |       文件名称
 filethumb        |     string    |       文件缩略图

