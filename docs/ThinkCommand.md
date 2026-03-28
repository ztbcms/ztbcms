# php think 命令速查

所有命令在 `tp6/` 目录下执行，格式为 `php think <command>`。使用 `php think list` 查看完整列表，`php think <command> --help` 查看具体用法。

## 常用命令
| 命令 | 说明 |
|------|------|
| `php think run` | 使用 PHP 内置服务器启动项目 |
| `php think version` | 显示 ThinkPHP 框架版本 |
| `php think clear` | 清除 runtime 缓存文件 |
| `php think build` | 构建应用目录 |

## 代码生成（make）
| 命令 | 说明 |
|------|------|
| `php think make:controller <app>@<Name>` | 创建控制器类 |
| `php think make:model <app>@<Name>` | 创建模型类 |
| `php think make:service <app>@<Name>` | 创建 Service 类 |
| `php think make:validate <app>@<Name>` | 创建验证器类 |
| `php think make:middleware <Name>` | 创建中间件类 |
| `php think make:command <Name>` | 创建自定义命令类 |
| `php think make:event <Name>` | 创建事件类 |
| `php think make:listener <Name>` | 创建事件监听器类 |
| `php think make:subscribe <Name>` | 创建事件订阅者类 |

## 数据库迁移（migrate）
| 命令 | 说明 |
|------|------|
| `php think migrate:create <MigrationName>` | 创建新的迁移文件 |
| `php think migrate:run` | 执行数据库迁移 |
| `php think migrate:rollback` | 回滚上一次或指定的迁移 |
| `php think migrate:status` | 查看迁移状态 |
| `php think migrate:breakpoint` | 管理迁移断点 |

## 数据填充（seed）
| 命令 | 说明 |
|------|------|
| `php think seed:create <SeederName>` | 创建数据填充文件 |
| `php think seed:run` | 运行数据填充 |

## 队列（queue）
| 命令 | 说明 |
|------|------|
| `php think queue:work` | 处理队列中的下一个任务 |
| `php think queue:listen` | 监听并持续处理队列任务 |
| `php think queue:restart` | 重启队列 worker 进程 |
| `php think queue:failed` | 列出所有失败的队列任务 |
| `php think queue:retry <id>` | 重试指定的失败任务 |
| `php think queue:flush` | 清空所有失败的队列任务 |
| `php think queue:forget <id>` | 删除指定的失败任务 |
| `php think queue:table` | 创建队列任务表的迁移文件 |
| `php think queue:failed-table` | 创建失败任务表的迁移文件 |

## 计划任务（cron）
| 命令 | 说明 |
|------|------|
| `php think cron:run` | 启动计划任务 |
| `php think cron:exec` | 执行指定的计划任务 |
| `php think cron:clean` | 清理计划任务缓存 |

## 其他
| 命令 | 说明 |
|------|------|
| `php think route:list` | 显示路由列表 |
| `php think optimize:route` | 生成路由缓存 |
| `php think optimize:schema` | 生成数据库表结构缓存 |
| `php think service:discover` | 发现并注册服务 |
| `php think vendor:publish` | 发布扩展包资源文件 |
| `php think factory:create` | 创建模型工厂 |
