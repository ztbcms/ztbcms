# ZTBCMS ThinkPHP 8 版本说明

## 目录约定

- `tp6/` 保留原 ThinkPHP 6 版本
- `tp8/` 为 ThinkPHP 8 版本
- `docs/` 保持不变
- `docs_tp8/` 存放 ThinkPHP 8 专用文档

## 环境要求

- PHP 8.2 及以上版本
- 推荐使用 PHP 8.3
- MySQL 5.6 及以上版本
- Composer 2
- PHP 扩展 `ctype`、`json`、`mbstring`、`openssl`、`pdo_mysql`

依赖锁文件要求 PHP 8.2 及以上版本，当前版本已在 PHP 8.3.32 下完成验证，核心依赖如下：

| 依赖 | 版本 |
| --- | --- |
| ThinkPHP | 8.1.4 |
| ThinkORM | 3.0.34 |
| think-view | 2.0.5 |
| think-template | 3.0.2 |
| think-trace | 2.0.0 |
| firebase/php-jwt | 7.1.0 |

## 安装

```bash
cd tp8
cp .env.example .env
composer install
```

根据实际环境修改 `.env` 中的数据库、Redis、邮件和对象存储等配置，敏感信息不得提交到版本控制

首次安装可以执行：

```bash
php think ztbcms:install
```

也可以启动本地服务器后访问安装向导：

```bash
php -S 127.0.0.1:8088 -t public public/router.php
```

浏览器访问 `http://127.0.0.1:8088/install/index/index`

## 常用命令

所有命令均在 `tp8/` 目录下执行：

```bash
php think list
php think clear
php think route:list
php think module:list
php think migrate:status
php think queue:work
```

## 验证

```bash
composer validate
composer audit --locked
php tests/php83_pagination_compatibility_test.php
php tests/admin_login_form_php83_test.php
php think list
php think route:list
```

`composer validate` 会提示项目沿用的 PSR-0 空命名空间以及两个定制依赖使用精确版本号，这些是现有项目兼容性约束，不影响安装

## 部署

Web 根目录必须指向 `tp8/public/`，不要直接暴露 `tp8/` 根目录。生产环境建议执行：

```bash
composer install --no-dev --classmap-authoritative
php think clear
```

从 `tp6` 切换时继续使用原数据库和持久化存储配置，先在预发布环境验证后台登录、模块管理、上传、队列和计划任务，再切换 Web 根目录。需要回滚时将 Web 根目录恢复为 `tp6/public/`
