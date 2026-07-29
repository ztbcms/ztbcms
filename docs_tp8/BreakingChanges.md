# Breaking Changes — TP6 → TP8

## 框架层

### PHP 版本
- **TP6**: PHP >= 7.2.5
- **TP8**: PHP >= 8.0（推荐 8.1+）

### 验证器独立
- **TP6**: `think\Validate` 内置于 `topthink/framework`
- **TP8**: 独立为 `topthink/think-validate ^3.0`，命名空间不变 `think\Validate`

### 容器独立
- **TP6**: `think\Container` 内置于 `topthink/framework`
- **TP8**: 独立为 `topthink/think-container ^3.0`

### App::VERSION 废弃
- TP8 废弃了 `App::VERSION` 常量，使用 `php think version` 代替

### 缓存计数废弃
- TP8 移除了缓存读取/写入次数计数，相关方法已废弃

## ORM 层

### think-orm 版本
- **TP6**: `topthink/think-orm ^2.0`（v2.0.62）
- **TP8**: `topthink/think-orm ^3.0`（v3.0.34）
  - 不能使用 v4.0.x，因为 v4.0 要求 `psr/simple-cache ^3.0`，与 easywechat 4.x 不兼容

### Model 基类
- `think\Model` 命名空间不变
- 查询构建器 API 基本兼容
- 需测试 `where()`、`join()`、`order()` 等常用方法

## 依赖层

### PSR 包降级
| 包 | TP6 版本 | TP8 版本 | 原因 |
|---|---|---|---|
| psr/log | v3.0.2 | v1.1.4 | easywechat 4.x 要求 `^1.1` |
| psr/simple-cache | v3.0.0 | v1.0.1 | easywechat 4.x 要求 `^1.0` |

> TP8 框架声明兼容 `psr/log ^1.0|^2.0|^3.0` 和 `psr/simple-cache ^1.0|^2.0|^3.0`，降级不影响框架功能。

### Carbon 升级
- **TP6**: `nesbot/carbon ^2.66`
- **TP8**: `nesbot/carbon ^3.0`（v3.13.1）
  - Carbon 3.x API 基本兼容 2.x，主要变更：
    - `Carbon::now()` 仍可用
    - 部分 deprecated 方法可能已移除
    - 时区处理更严格

### 调试工具变更
- **TP6**: `symfony/var-dumper ^4.2`
- **TP8**: `topthink/think-dumper ^1.0`（TP 自有实现）

## 配置层

### database.php
- `config_path()` 助手在 TP8 中不可用（配置文件加载时机不同）
- 改为 `__DIR__` 相对路径引入 dataconfig.php

### view.php
- `taglib_pre_load` 配置项仍存在，但本项目不再使用自定义标签库
- 移除了 `app\common\taglib\Ztbcms` 的加载

## 已移除代码

### 自定义模板标签库（死代码）
| 文件 | 行数 | 说明 |
|------|------|------|
| `app/common/taglib/Template.php` | 1388 行 | 从 think-template 复制的完整副本 |
| `app/common/taglib/Ztbcms.php` | 49 行 | 自定义 include 标签，从未被任何模板使用 |

经全项目搜索确认，没有任何模板文件使用 `{ztbcms:include}` 标签，因此直接移除。
