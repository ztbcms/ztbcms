### 安装

php 版本要求 7.2或以上

### 入口

- 后台入口：http://ztbcms.ztbweb.cn/admin/Login/index
- 安装向导：http://ztbcms.ztbweb.cn/install/index/index (重新安装请删除`tp6/app/install/install.lock`)


### 配置

拷贝`tp6/.example.env`配置到`tp6/.env`开启应用配置

`config/app.php`开启错误提示
```shell script
// 显示错误信息
'show_error_msg'   => true,
```

后台主要服务

类名 | 说明 | 
:----------- | :-----------: |
admin/RbacService        |     角色权限服务    |   
admin/ModuleService      |     模块服务，模块安装、卸载    |   


### Controller

- 管理后台请继承AdminController(需要启动`Session`中间件，请参考`admin/middleware.php`)
- 非管理后台请集成BaseController


