### 安装

php 版本要求 7.2或以上

```shell script
cd tp6 && composer install
```


### 入口

旧版本
- 后台入口：http://ztbcms.ztbweb.cn/admin/public/login

tp6版本
- 后台入口：http://ztbcms.ztbweb.cn/home/admin/Login/index
- 安装向导：http://ztbcms.ztbweb.cn/home/install/index/index (重新安装请删除`tp6/app/install/install.lock`)


测试账号密码 admin/zhutibang
PS: 请勿同时在同一浏览器登录两个版本

### 配置

拷贝`tp6/.example.env`配置到`tp6/.env`开启应用配置


`config/app.php`开启错误提示
```shell script
// 显示错误信息
'show_error_msg'   => true,
```

后台主要服务
RbacService 角色权限服务
ModuleService
TODO: AccessService


## 迁移说明

定义了常量(tp6/index.php)IS_THINKPHP_V6来判定环境是否为tp6,稍后相关的判定可以删除的

