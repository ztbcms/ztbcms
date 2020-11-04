
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