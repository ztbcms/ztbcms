
### `url()`使用

```php
# 错误，返回UrlBuild对象
$u = url('/a/b/c'); 
# 正确，返回string
$u = url('/a/b/c')->build();
# ztbcms 改进用法：
$u = build_url('/a/b/c');
```

### 权限

父角色包含了角色的所有权限


### 为什么我的session设置无效？

TP6为了提高性能默认关闭session，需要显式启用 `\think\middleware\SessionInit::class`：
- 全局：在 `tp6/app/middleware.php` 取消注释 `\think\middleware\SessionInit::class`。
- 模块：在对应模块的 `middleware.php`（如 `tp6/app/api/middleware.php`）加入 `\think\middleware\SessionInit::class`。
- 控制器：在控制器 `$middleware` 属性中添加 `\think\middleware\SessionInit::class`，精准到单个控制器。

注意：为了性能，优先按需启用（模块或控制器级），不建议全局常开。
