# ThinkPHP 8 迁移记录

## 迁移范围

`tp8/` 以 Git 已跟踪的 `tp6/` 代码为基线迁移，包含后台、API、前台、安装向导、公共服务、静态资源和已纳入版本控制的 Composer 依赖

迁移未复制 `tp6/` 中未跟踪的本地模块、测试脚本、安装锁和临时文件，避免将本地实验内容或环境状态带入新版本

## 核心改动

1. PHP 最低版本由 PHP 7 系列提升为 PHP 8.2
2. `topthink/framework` 从 6.1.5 升级到 8.1.4
3. `topthink/think-orm` 从 2.0.62 升级到 3.0.34
4. `topthink/think-view` 和 `topthink/think-template` 分别升级到 2.0.5 和 3.0.2
5. `topthink/think-trace` 升级到 2.0.0
6. 应用入口和命令行入口改用 `use think\App`，与 ThinkPHP 8 官方项目骨架保持一致
7. 基础控制器的验证器参数使用 PHP 8 联合类型 `string|array`
8. `firebase/php-jwt` 升级到 7.1.0，消除旧版本安全审计告警
9. 未复制已过时且包含旧发布凭据的 `.travis.yml`，避免在新目录重复敏感配置

## develop 同步记录

`thinkphp8` 已同步 `develop` 截至 `7bea2d7b` 的变更，并将对应功能等价迁移到 `tp8/`：

- 新增 `module:menu-sync` 模块菜单同步命令
- 调整默认配置与安装环境模板
- 支持后台登录密码显示切换及 Tab 键焦点优化
- 调整缓存清理范围并优化维护页面
- 修复首页网站简介被默认转换为英文大写的问题

## 兼容性验证结果

- Composer 依赖求解和自动发现成功
- `php think list` 正常加载 ThinkPHP 8.1.4 及项目自定义命令
- 236 个项目 PHP 文件语法检查通过
- 212 个应用文件逐一加载通过，未发现继承方法签名冲突
- 分页兼容测试通过
- 后台登录表单兼容测试通过
- 内置服务器首页返回 HTTP 200
- Web 安装向导返回 HTTP 200
- Composer 安全审计无已知漏洞

## 上线检查

- 备份数据库和上传目录
- 使用生产环境配置生成 `tp8/.env`
- 执行 `composer install --no-dev --classmap-authoritative`
- 执行 `php think clear`
- 检查首页和后台登录
- 检查模块列表及至少一个业务模块页面
- 检查文件上传、邮件、Redis、队列和计划任务
- 确认无误后将 Web 根目录切换到 `tp8/public/`

本次迁移未执行真实数据库写入、模块安装卸载、队列消费和外部服务调用，这些项目需要使用部署环境配置完成验收
