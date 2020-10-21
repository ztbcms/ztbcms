### 新增菜单

```shell
# 安装模块
/home/admin/Module/doInstallModule?module={模块名}

# 卸载模块
/home/admin/Module/doUninstallModule?module={模块名}

```

后台菜单请添加到`admin/install/Menu.php`
```php
│ 消息 
│
├─ 所有消息 {{domain}}/Admin/AdminMessage/index => {{domain}}/home/Admin/AdminMessage/index                                                
├─ 未读消息 {{domain}}/Admin/AdminMessage/noRead => {{domain}}/home/Admin/AdminMessage/noRead
├─ 系统消息 {{domain}}/Admin/AdminMessage/system => {{domain}}/home/Admin/AdminMessage/system
│
│ 我的面板
│
├─ 个人信息
│ 
├──── 修改个人信息   {{domain}}/Admin/Adminmanage/myinfo => {{domain}}/home/Admin/Management/myBasicsInfo
├──── 修改密码   {{domain}}/Admin/Adminmanage/chanpass => {{domain}}/home/admin/Management/chanpass
│
│ 设置
│
├─ 后台菜单管理
│
├──── 后台菜单 {{domain}}/Admin/Menu/index => {{domain}}/home/Admin/Menu/index
├──── 后台详情 {{domain}}/Admin/Menu/add || edit => {{domain}}/home/Admin/Menu/details
├
├─ 管理员管理
│
├──── 管理员列表 {{domain}}/Admin/Management/index => {{domain}}/home/Admin/Management/index
├──── 管理员详情 {{domain}}/Admin/Management/add || edit => {{domain}}/home/Admin/Management/details
├
├─ 角色管理
│
├──── 角色列表 {{domain}}/Admin/Rbac/index => {{domain}}/home/Admin/Rbac/index
├──── 添加角色 {{domain}}/home/Admin/Rbac/roleAdd
├──── 编辑角色 {{domain}}/home/Admin/Rbac/roleEdit
├──── 权限设置 {{domain}}/home/Admin/Rbac/authorize
├──── 成员管理 {{domain}}/home/Admin/Management/index
├──── 权限组设置 {{domain}}/home/Admin/AccessGroup/accessGroupRoleSetting
│
├─ 权限组管理
│
├──── 权限组列表 {{domain}}/Admin/AccessGroup/accessGroupList => {{domain}}/home/Admin/AccessGroup/accessGroupList
├──── 权限组详情 {{domain}}/home/Admin/AccessGroup/accessGroupDetails

├─ 系统设置
│
├──── 站点配置 {{domain}}/home/Admin/Config/index
├──── 邮箱配置 {{domain}}/home/Admin/Config/email
├──── 邮箱配置 {{domain}}/home/Admin/Config/attach
├──── 拓展配置 {{domain}}/home/Admin/Config/extend
├──── 添加配置 {{domain}}/home/Admin/Config/editExtend

├─ 模块
│
├──── 模块管理 
        ├──── 本地模块 {{domain}}/admin/module/index=> {{domain}}/home/admin/module/index

```


