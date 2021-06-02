### 模块安装卸载（调试admin、common也可以用）

```shell
# 安装模块
/admin/Module/doInstallModule?module={模块名}

# 卸载模块
/admin/Module/doUninstallModule?module={模块名}
```


## 权限说明

管理后台的权限默认对继承AdminController的控制器进行权限判定，设置有两种方式：

1、代码设置
> 使用场景：由开发人员决定不需要验证权限的Action，例如『获取登录用户的后台菜单』，这些每个用户都是默认有的

设置忽略权限验证的 Action：
```php
class Foo extends AdminController
{
    public $noNeedPermission = ['addItem'];
    // public $noNeedPermission = ['*']; // 忽略全部Action
}
```

2、管理后台设置

进入管理后台 => 角色管理-角色列表-权限设置 

左侧栏菜单获取流程
```
获取状态为『展示』菜单	=>	是否分配了权限=> 是 => 是否需要权限验证	=> 是 => 是否有权限	=> 是 => 返回菜单
									=> 否 => 不返回菜单
															=> 否 => 返回菜单
                                                                                => 否 => 不返回菜单
```

## 路由

路由格式:`/app/controller/acton`,注意，controller 格式可能为 `admin.user.role` 这种多层目录格式

常见格式路由有
- `member/admin.Role/settingList`
- `member/Role/settingList`

路由权限验证流程：分别按顺序判定该角色app、controller、action是否有权限，具体细节请参考中间件`app\common\middleware\AdminAuth.php`的实现
其中，% 代表全部都有权限，示例：
- `member/admin.Role/%`表示控制器 `member/admin.Role`所有Action
- `member/admin.%/%` 表示目录 `member/admin`所有Controller
- `member/%/%` 表示目录 `member/`所有Controller
- `%/%/%` 表示目录 `member/`所有应用


## 菜单权限

按照管理员权限展示对应前端代码，常用于控制页面某个按钮,是否有操作权限
```php
<?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('app', 'controller', 'action')){ ?>
// 你的前端代码
<?php } ?>
```


