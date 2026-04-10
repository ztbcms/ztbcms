# 安装框架

## 1. 环境要求

- **PHP 版本**：`>= 7.2`
- **数据库依赖**：MySQL / MariaDB（安装建库时需要）

## 2. 伪静态配置 (URL 重写)

为确保框架的路由解析正常，请根据您的 Web 服务器环境配置相应的伪静态规则：

### Nginx
低版本的 Nginx 默认可能不支持 `PATHINFO`，您可以通过在 `nginx.conf`（或对应的站点 vhost 配置文件）中添加以下转发规则来实现：

```nginx
location / { 
    # ... 省略部分原有配置 ...
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php/$1 last;
        break;
    }
}
```

### Apache
1. 确保在 `httpd.conf` 配置文件中已开放并加载了 `mod_rewrite.so` 模块。
2. 将站点的目录权限控制项 `AllowOverride None` 更改为 `AllowOverride All`。
3. 拷贝下方代码，将其保存为 `.htaccess` 文件，放置于框架的应用入口文件同级目录下（即 `tp6/public/` 目录下）：

```apache
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

## 3. 安装方式

系统提供两种方式进行自动化安装，请根据您的使用场景进行选择：

- **1.网页控制台安装**：
  配置好运行目录指向 `tp6/public/` 并在浏览器直接访问：`http://{您的域名}/install/index/index`，依照页面提示下一步即可。

- **2.命令行 CLI 安装**（推荐）：
  在项目根目录下进入终端，执行指令：
```bash
php think ztbcms:install -f \
  --db_host 127.0.0.1 \
  --db_port 3306 \
  --db_name your_db \
  --db_user root \
  --db_pwd root_password \
  --manager admin \
  --manager_pwd admin_password
```

> **进阶参考**：关于一键静默安装参数和防重复安装机制，请详细查阅 [系统安装向导](../tp6/app/install/README.md)