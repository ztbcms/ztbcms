# TP6 → TP8 升级指南

## 升级策略

采用**双目录并行**策略：保留 `tp6/` 不动，新建 `tp8/` 目录独立演进。

## 升级步骤

### 1. 基础搭建

```bash
# 创建分支
git checkout -b tp8

# 生成 TP8 骨架
composer create-project topthink/think tp8

# 复制应用代码
cp -r tp6/app tp8/app
cp -r tp6/public/static tp8/public/
cp -r tp6/public/statics tp8/public/
cp -r tp6/extend tp8/
cp -r tp6/database tp8/
cp tp6/.env tp8/.env
cp tp6/.env.example tp8/.env.example
```

### 2. 移除死代码

```bash
# 删除从未使用的自定义模板标签库
rm -rf tp8/app/common/taglib/
```

### 3. 配置迁移

```bash
# 复制配置文件
cp tp6/config/*.php tp8/config/

# 必须修改的配置：
# 1. config/database.php — config_path() 改为 __DIR__
# 2. config/view.php — 删除 taglib_pre_load 行
```

### 4. 安装依赖

```bash
cd tp8

# 配置 composer.json（见下方完整版本）
# 然后安装
composer install
```

### 5. 验证

```bash
php think version    # 应显示 v8.1.4
php think            # 应显示命令列表
```

## composer.json 最终版本

关键约束：
- `topthink/framework: ^8.0`
- `topthink/think-orm: ^3.0`（不能用 ^4.0，与 easywechat PSR 冲突）
- `topthink/think-multi-app: ^1.1`
- `w7corp/easywechat: 4.9.0.1`（Jayin fork，需 VCS 仓库配置）
- `nesbot/carbon: ^3.0`（TP8 生态要求）

## 文件变更清单

### 无需修改的文件（直接从 tp6 复制）

| 文件 | 原因 |
|------|------|
| `app/ExceptionHandle.php` | 异常处理基类 API 未变 |
| `app/Request.php` | 请求对象扩展方式未变 |
| `app/provider.php` | 容器绑定格式未变 |
| `app/AppService.php` | 服务注册格式未变 |
| `app/common.php` | 全局函数不依赖框架内部 API |
| `app/event.php` | 事件定义格式未变 |
| `app/middleware.php` | 中间件定义格式未变 |
| `app/service.php` | 服务注册文件格式未变 |

### 需要修改的文件

| 文件 | 修改内容 |
|------|---------|
| `app/BaseController.php` | 添加 `declare(strict_types=1)`；validate 方法参数类型改为 `string\|array` |
| `config/database.php` | `config_path()` 改为 `__DIR__` |
| `config/view.php` | 删除 `taglib_pre_load` 行 |
| `composer.json` | 完全重写，见 docs_tp8/README.md |

### 移除的文件

| 文件 | 原因 |
|------|------|
| `app/common/taglib/Template.php` | 死代码，无模板使用 |
| `app/common/taglib/Ztbcms.php` | 死代码，无模板使用 |
