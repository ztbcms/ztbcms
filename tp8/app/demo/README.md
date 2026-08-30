### 示例模块
##
本模块提供：新建后台标签页、列表页、表单页、图标选用、图片合成和图片预览示例

1、图片处理-图片合成 需在TP6文件夹下安装
```
composer require intervention/image
```

##### 备注
目前Upload模块未迁移到TP6，要先按照TP3的upload模块才能正常演示
```
│ 示例
│
├─ 图片处理
├──── 图片合成 {{domain}}/Elementui/ImageProcessDemo/index => {{domain}}/home/demo/admin.ImageProcess/index
├──── 图片合成接口 {{domain}}/Elementui/ImageProcessDemo/createSharePoster => {{domain}}/home/demo/admin.ImageProcess/createSharePoster
│
```
