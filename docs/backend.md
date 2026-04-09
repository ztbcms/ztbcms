# 3. 后端开发指南

### Controller

- 管理后台请继承AdminController(只有继承这个AdminController才会自动启用session)
- 非管理后台请集成BaseController

### 状态码说明

- 200 正常
- 400 错误返回
- 401 未登录授权
- 403 禁止访问，没有权限
- 404 找不到资源

### 用户操作日志

新增操作日志示例：

```php
$log_data = [
    'source_type' => 'install_module', //分类名
    'source' => 'demo', //来源
    'content' => '安装模块：demo', //操作内容
];
(new UserOperateLogService)::addUserOperateLog($log_data);
```
