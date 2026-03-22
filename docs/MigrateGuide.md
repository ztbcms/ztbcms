# ThinkPHP Migrate 指令使用文档

本文整理 ZTBCMS 项目中 `php think migrate:*` 指令的常用用法、参数说明与推荐操作流程。

## 目录

- [一、基础说明](#一基础说明)
- [二、命令总览](#二命令总览)
- [三、migrate:create](#三migratecreate)
- [四、migrate:run](#四migraterun)
- [五、migrate:rollback](#五migraterollback)
- [六、migrate:breakpoint](#六migratebreakpoint)
- [七、migrate:status](#七migratestatus)
- [八、迁移文件示例](#八迁移文件示例)
- [九、推荐使用流程](#九推荐使用流程)
- [十、常见问题](#十常见问题)

## 一、基础说明

### 1. 进入项目目录

所有迁移命令都需要在 `tp6/` 目录下执行：

```bash
cd tp6
```

### 2. 本项目迁移文件目录

当前项目的迁移文件默认放在：

```text
tp6/database/migrations/
```

### 3. 迁移记录表

`think-migration` 会把已执行的迁移版本记录到迁移表中。

- 默认表名来源于 `database.migration_table`
- 当前项目未单独配置该项，因此默认值为 `migrations`
- 实际表名会自动拼接数据库前缀，通常为：`{DB_PREFIX}migrations`

### 4. 命令总入口

可通过以下命令查看完整命令列表：

```bash
php think list
```

当前项目已启用的迁移命令主要有：

- `migrate:breakpoint`
- `migrate:create`
- `migrate:rollback`
- `migrate:run`
- `migrate:status`

## 二、命令总览

| 命令 | 作用 | 常用场景 |
|------|------|----------|
| `php think migrate:create <Name>` | 创建迁移文件 | 新增表、字段、索引 |
| `php think migrate:run` | 执行未运行的迁移 | 发布数据库结构变更 |
| `php think migrate:rollback` | 回滚迁移 | 撤销最近一次或指定版本变更 |
| `php think migrate:breakpoint` | 设置/取消回滚断点 | 防止误回滚关键版本 |
| `php think migrate:status` | 查看迁移状态 | 检查哪些迁移已执行 |

## 三、migrate:create

### 1. 作用

创建一个新的迁移文件。

### 2. 命令格式

```bash
php think migrate:create MigrationName
```

### 3. 参数说明

| 参数 | 必填 | 说明 |
|------|------|------|
| `name` | 是 | 迁移类名，建议使用 `CamelCase` 命名 |

### 4. 使用示例

```bash
php think migrate:create CreateUserTable
php think migrate:create AddStatusToNewsTable
```

### 5. 生成结果

命令执行后，会在 `tp6/database/migrations/` 下生成类似文件：

```text
20260322123045_create_user_table.php
```

### 6. 命名建议

- 类名使用大驼峰，如 `CreateUserTable`
- 文件名会自动转换为带时间戳的下划线格式
- 类名必须唯一，否则会创建失败

## 四、migrate:run

### 1. 作用

执行数据库迁移，把未执行的迁移应用到数据库。

### 2. 命令格式

```bash
php think migrate:run
php think migrate:run -t 版本号
php think migrate:run -d 日期
```

### 3. 可用参数

| 参数 | 简写 | 说明 |
|------|------|------|
| `--target` | `-t` | 迁移到指定版本号 |
| `--date` | `-d` | 迁移到指定日期对应的版本 |

### 4. 使用示例

执行所有未执行迁移：

```bash
php think migrate:run
```

迁移到指定版本：

```bash
php think migrate:run -t 20260322123045
```

迁移到指定日期：

```bash
php think migrate:run -d 2026-03-22
```

### 5. 行为说明

- 不带参数时，会执行所有未执行迁移
- `-t` 会迁移到指定版本号
- `-d` 会迁移到指定日期时间之前的最近版本
- 如果指定版本不存在，命令会输出警告并停止

## 五、migrate:rollback

### 1. 作用

回滚数据库迁移，用于撤销已执行的结构变更。

### 2. 命令格式

```bash
php think migrate:rollback
php think migrate:rollback -t 版本号
php think migrate:rollback -d 日期
php think migrate:rollback -f
```

### 3. 可用参数

| 参数 | 简写 | 说明 |
|------|------|------|
| `--target` | `-t` | 回滚到指定版本 |
| `--date` | `-d` | 回滚到指定日期对应的版本 |
| `--force` | `-f` | 忽略断点强制回滚 |

### 4. 使用示例

回滚最近一次迁移：

```bash
php think migrate:rollback
```

回滚到指定版本：

```bash
php think migrate:rollback -t 20260322123045
```

按日期回滚：

```bash
php think migrate:rollback -d 2026-03-22
```

忽略断点强制回滚：

```bash
php think migrate:rollback -f
```

### 5. 行为说明

- 不带参数时，默认只回滚最近一次迁移
- `-t` 表示回滚到指定版本，保留该版本及其之前的迁移状态
- 如果目标版本早于第一条已执行迁移，则会回滚全部迁移
- 遇到断点时会停止回滚，除非显式加 `-f`

## 六、migrate:breakpoint

### 1. 作用

为某个已执行迁移设置或取消回滚断点，防止继续向前回滚。

### 2. 命令格式

```bash
php think migrate:breakpoint
php think migrate:breakpoint -t 版本号
php think migrate:breakpoint -r
```

### 3. 可用参数

| 参数 | 简写 | 说明 |
|------|------|------|
| `--target` | `-t` | 指定要设置/取消断点的版本号 |
| `--remove-all` | `-r` | 清除全部断点 |

### 4. 使用示例

对最近一次已执行迁移设置或取消断点：

```bash
php think migrate:breakpoint
```

对指定版本设置或取消断点：

```bash
php think migrate:breakpoint -t 20260322123045
```

清空所有断点：

```bash
php think migrate:breakpoint -r
```

### 5. 行为说明

- 不传 `-t` 时，默认操作最近一次已执行迁移
- 同一个版本再次执行该命令，会在“设置断点”和“取消断点”之间切换
- 不能对未执行的迁移设置断点
- `-t` 与 `-r` 不能同时使用

## 七、migrate:status

### 1. 作用

查看当前迁移执行状态。

### 2. 命令格式

```bash
php think migrate:status
php think migrate:status -f json
```

### 3. 可用参数

| 参数 | 简写 | 说明 |
|------|------|------|
| `--format` | `-f` | 输出格式，支持 `text` 或 `json` |

### 4. 使用示例

查看文本状态：

```bash
php think migrate:status
```

查看 JSON 状态：

```bash
php think migrate:status -f json
```

### 5. 输出说明

- `up`：该迁移已执行
- `down`：该迁移尚未执行
- `BREAKPOINT SET`：该迁移已设置断点
- `** MISSING **`：数据库记录里有该版本，但迁移文件已不存在

## 八、迁移文件示例

`migrate:create` 生成的基础模板大致如下：

```php
<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserTable extends Migrator
{
    public function change()
    {

    }
}
```

一个更实用的示例：

```php
<?php

use think\migration\Migrator;

class CreateDemoUserTable extends Migrator
{
    public function change()
    {
        $table = $this->table('demo_user');
        $table->addColumn('username', 'string', ['limit' => 50, 'default' => ''])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('create_time', 'integer', ['default' => 0])
            ->create();
    }
}
```

### 编写建议

- 优先使用 `change()` 编写可逆变更
- 新建表后使用 `create()`
- 修改表结构后使用 `update()`
- 表名不需要手工拼接前缀，迁移组件会结合当前数据库前缀处理

## 九、推荐使用流程

### 场景一：新增表

```bash
cd tp6
php think migrate:create CreateArticleTable
```

随后编辑生成文件，补充表结构，再执行：

```bash
php think migrate:run
php think migrate:status
```

### 场景二：新增字段

```bash
cd tp6
php think migrate:create AddStatusToArticleTable
php think migrate:run
```

### 场景三：回滚错误变更

```bash
cd tp6
php think migrate:rollback
php think migrate:status
```

### 场景四：关键版本保护

```bash
cd tp6
php think migrate:breakpoint -t 20260322123045
```

后续如需继续强制回滚：

```bash
php think migrate:rollback -f
```

## 十、常见问题

### 1. 为什么命令要在 `tp6/` 目录执行？

因为 `think` 命令入口在 `tp6/think`，并且迁移目录、配置文件、数据库连接都以 `tp6` 为应用根目录。

### 2. `migrate:create` 之后文件放在哪里？

默认放在 `tp6/database/migrations/`。

### 3. 为什么回滚没有继续执行？

通常是以下几种原因：

- 已经没有可回滚的迁移
- 回滚碰到了断点
- 指定的目标版本不存在

### 4. `status` 里出现 `** MISSING **` 是什么意思？

表示数据库里记录了某个迁移版本已执行，但当前 `tp6/database/migrations/` 目录里已经没有对应文件。此时不建议继续盲目回滚，应该先补齐迁移文件或核对数据库状态。
