### 新增菜单

```shell
# 安装模块
/admin/Module/doInstallModule?module={模块名}

# 卸载模块
/admin/Module/doUninstallModule?module={模块名}
```

## 菜单权限

按照管理员权限展示对应前端
```php
<?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'role', 'authorize')){ ?>
// 你的前端代码
<?php } ?>
```


