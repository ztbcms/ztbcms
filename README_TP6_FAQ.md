
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

TP6为了提高性能，默认是关闭session的初始化。请在中间件配置上增加`\think\middleware\SessionInit::class`从而开始。

注意：为了提高性能，不必要时请勿开启，或者按需启用session(在应用，控制器层面指定开启。不建议全局开启)


### composer json

依赖composer/semver可能会导致无法安装