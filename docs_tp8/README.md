# ZTBCMS ThinkPHP 8 版本

基于 ThinkPHP 8.1.4 的 ZTBCMS 内容管理系统。

## 环境要求

| 依赖 | 版本 |
|------|------|
| PHP | >= 8.0（推荐 8.1+） |
| MySQL | >= 5.7 |
| Redis | >= 6.0（可选） |
| Composer | >= 2.0 |

## 快速开始

```bash
cd tp8

# 安装依赖
composer install

# 配置环境
cp .env.example .env
# 编辑 .env 填写数据库等配置

# 启动开发服务器
php -S 127.0.0.1:8082 -t public/
# 或使用 Makefile
make serve-tp8
```

访问 http://127.0.0.1:8082/ 即可看到首页。

## 核心依赖版本

| 包 | 版本 | 说明 |
|---|---|---|
| topthink/framework | v8.1.4 | ThinkPHP 核心 |
| topthink/think-orm | v3.0.34 | ORM（不使用 v4.0，与 easywechat PSR 冲突） |
| topthink/think-multi-app | v1.1.1 | 多应用支持 |
| topthink/think-view | v2.0.5 | 视图引擎 |
| topthink/think-queue | v3.0.12 | 队列 |
| topthink/think-template | v3.0.2 | 模板引擎 |
| w7corp/easywechat | 4.9.0.1 | 微信 SDK（Jayin fork，PHP 8.3 兼容） |

## 与 TP6 的主要差异

| 维度 | TP6 | TP8 |
|------|-----|-----|
| PHP 版本 | >= 7.4 | >= 8.0 |
| ORM 版本 | think-orm v2.0.x | think-orm v3.0.x |
| 验证器 | 内置于 framework | 独立 think-validate ^3.0 |
| 容器 | 内置于 framework | 独立 think-container ^3.0 |
| 调试工具 | symfony/var-dumper | think-dumper ^1.0 |
| 模板标签库 | taglib_pre_load => Ztbcms | 已移除（死代码） |
| carbon | v2.x | v3.x |
| PSR simple-cache | v3.0 | v1.0（easywechat 兼容） |

## 目录结构

```
tp8/
├── app/              # 应用代码（从 tp6 迁移）
│   ├── admin/        # 管理后台模块
│   ├── api/          # API 模块
│   ├── common/       # 公共模块
│   ├── demo/         # 示例模块
│   ├── home/         # 前台首页模块
│   └── install/      # 安装向导模块
├── config/           # 配置文件
├── public/           # 入口文件 + 静态资源
├── route/            # 路由定义
├── vendor/           # 依赖包
├── think             # CLI 入口
└── composer.json
```

## 常用命令

```bash
# 查看版本
php think version

# 清理运行时缓存
php think clear

# 生成配置缓存
php think optimize:config

# 启动队列消费者
php think queue:work

# 查看路由列表
php think route:list
```

## 已知限制

- `think-orm` 锁定在 v3.0.x（不使用 v4.0），因为 v4.0 的 `psr/simple-cache ^3.0` 与 easywechat 4.x 的 `^1.0` 冲突
- `psr/log` 和 `psr/simple-cache` 锁定在 v1.x 以兼容 easywechat
- 自定义模板标签库（`taglib/Template.php` + `taglib/Ztbcms.php`）已移除，经验证为死代码

## 相关文档

- [升级指南](UpgradeGuide.md) — TP6 → TP8 详细升级步骤
- [Breaking Changes](BreakingChanges.md) — 不兼容变更清单
