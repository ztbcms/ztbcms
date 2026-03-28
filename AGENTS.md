# Repository Guidelines

## 项目结构与模块

- 文档目录: `docs/`
- `tp6/` 为源码目录，遵循 ThinkPHP 6 默认多应用目录格式
  - 核心应用：`app/admin/` 后台管理、`app/api/` 通用接口、`app/home/` 默认前台、`app/install/` 安装向导，公共逻辑、扩展、工具类等在 `app/common/`。
  - Web 入口 `tp6/public/index.php`，静态资源放在 `tp6/public/static` 与 `tp6/public/statics`；配置集中于 `tp6/config`，全局函数位于 `tp6/app/common.php`。
  - 数据库/队列等扩展按模块下的 `config`、`event.php` 管理；本地脚本见根目录 `Makefile`


## 环境与运行
- **PHP >=7.2.5** 与 **MySQL 5.6+**，首次运行执行 `cd tp6 && composer install`（已配置阿里云镜像）。
- **本地启动**：在仓库根目录运行 `make serve`，或直接 `php -S 127.0.0.1:8081 -t ./tp6/public/`，访问 `http://localhost:8081/`。
- **清理**：`make clean` 清除 runtime 缓存，`make clean-install` 删除安装目录；变更配置后重启内置服务器。
- **依赖安装**：`cd tp6 && composer install`,为了方便起见，我们已经默认安装好了vendor

## php think 命令

- 所有命令在 `tp6/` 目录下执行，格式为 `php think <command>`。
- 涵盖代码生成（make）、数据库迁移（migrate）、队列（queue）、计划任务（cron）等常用操作。
- 详细命令速查表见 `docs/ThinkCommand.md`。

## 代码风格与命名
- **PSR 标准**：遵循 PSR-4/PSR-12，**4 空格缩进**，UTF-8，**无 BOM**。
- **命名规范**：
  - 类/控制器：`PascalCase`（如 `AdminUserService`、`LoginController`）
  - 方法/变量：`camelCase`（如 `getUserInfo`、`$userId`）
  - 配置键/常量：`SCREAMING_SNAKE_CASE` 或 `snake_case`（如 `admin_panel_security_code`）
  - 接口路径：**全小写、中划线分隔**（如 `/api/user/login`，禁止大写和空格）
- **文件编码**：所有 PHP 文件使用 **UTF-8 无 BOM** 编码。
- **注释风格**：类注释使用 `/** ... */`，方法注释标注 `@param`、`@return`、`@throws`。

## 错误处理与返回格式
- **统一返回**：使用 `app\BaseController::createReturn($status, $data, $msg, $code)` 或静态方法 `makeJsonReturn`、`returnSuccessJson`、`returnErrorJson`。
- **返回结构**：`['status' => bool, 'code' => int, 'data' => mixed, 'msg' => string]`，成功 code=200，失败 code=400。
- **异常处理**：ThinkPHP 异常自动捕获；业务校验失败使用 `ValidateException` 或返回错误 JSON。
- **控制器响应**：API 接口统一返回 JSON，使用 `return self::returnSuccessJson($data)` 或 `return self::returnErrorJson($msg)`。
- **数据库异常**：捕获 `\think\db\exception\DataNotFoundException`、`DbException`、`ModelNotFoundException`。

## 导入与命名空间
- **命名空间**：`app\` 为主命名空间，模块内使用相对路径（如 `app\admin\service\`）。
- **类导入**：使用 `use` 语句导入完整类名，避免使用完整限定名。
- **排序规范**：`use` 语句按字母顺序排列，ThinkPHP 框架类在前，业务类在后。
- **自动加载**：`composer.json` 已配置 PSR-4 自动加载，新增命名空间需运行 `composer dump-autoload`。

## 安全与日志
- **敏感信息**：严禁提交密钥、密码、API Key 等到版本控制；使用 `.env` 或环境变量管理。
- **密码处理**：使用 `app\common\libs\helper\PasswordHelper::hashPassword()` 加密。
- **用户输入**：必须使用 `think\facade\Request::param()` 或 `->get()`/`->post()` 获取，并进行校验。
- **日志记录**：操作日志使用 `app\admin\service\UserOperateLogService`。

## 跨模块开发
- **公共逻辑**：优先放入 `app/common`，避免重复实现。
- **复用组件**：将通用功能抽取为 Service、Helper 或 Library，置于 `app\common\`。
- **统一入口**：全局函数定义于 `tp6/app/common.php`，如 `build_url()`、`createReturn()`、`generateToken()`。

## 提交与合并
- **提交信息**：推荐 Conventional Commits，如 `feat(admin): 新增用户管理模块`、`fix(common): 修复登录验证漏洞`。
- **MR/PR 内容**：包含变更摘要、关联需求/Issue、测试步骤与结果、UI 变更截图（如有）、上线影响与回滚方案。
- **环境说明**：涉及配置变更时说明所需环境变量及默认值，严禁提交敏感信息。

## 开发工作流
1. 创建分支：`git checkout -b feat/module-name` 或 `fix/bug-description`。
2. 开发与测试：本地验证功能，确保通过代码检查。
3. 提交：`git add . && git commit -m "type(scope): 描述"`。
4. 推送与合并：推送到远程并创建 Merge Request。
