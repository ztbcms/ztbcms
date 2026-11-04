# ZTBCMS 路由开发指南

本文面向 ZTBCMS 开发人员，说明当前项目的路由结构、默认路由规则、应用级自定义路由、全局路由的作用范围，以及日常开发中的推荐做法。

## 目录

- [一、项目当前路由结构](#一项目当前路由结构)
- [二、请求是如何定位到应用的](#二请求是如何定位到应用的)
- [三、默认路由规则](#三默认路由规则)
- [四、应用级自定义路由](#四应用级自定义路由)
- [五、全局路由](#五全局路由)
- [六、为什么 `/hello` 要写到 `home` 应用路由里](#六为什么-hello-要写到-home-应用路由里)
- [七、路由编写示例](#七路由编写示例)
- [八、开发建议](#八开发建议)
- [九、常见排查方法](#九常见排查方法)

## 一、项目当前路由结构

当前项目采用 ThinkPHP6 多应用结构，应用目录位于：

```text
tp6/app/
```

例如：

- `tp6/app/home`
- `tp6/app/admin`
- `tp6/app/api`
- `tp6/app/news`
- `tp6/app/wechat`

当前和路由直接相关的文件主要有：

- `tp6/config/app.php`
- `tp6/config/route.php`
- `tp6/route/app.php`
- `tp6/app/home/route/app.php`

其中：

- `tp6/config/app.php` 负责应用层面的路由入口行为，例如默认应用、多应用、应用快速访问
- `tp6/config/route.php` 负责框架通用路由设置
- `tp6/app/{应用名}/route/*.php` 是当前项目最主要的自定义路由编写位置
- `tp6/route/*.php` 是根目录全局路由目录

## 二、请求是如何定位到应用的

### 1. 关键配置

当前项目在 `tp6/config/app.php` 中有如下关键配置：

```php
'with_route'     => true,
'auto_multi_app' => true,
'app_express'    => true,
'default_app'    => 'home',
```

含义可以简单理解为：

- 开启路由功能
- 项目按多应用结构运行
- 当 URL 第一段不是有效应用名时，自动回退到默认应用
- 默认应用是 `home`

### 2. 请求解析示例

访问：

```text
/admin/login/index
```

处理过程：

1. 第一段是 `admin`
2. 系统发现 `tp6/app/admin/` 存在
3. 当前应用切换为 `admin`
4. 后续控制器、配置、路由都按 `admin` 应用处理

访问：

```text
/news/content/index
```

处理过程：

1. 第一段是 `news`
2. 系统发现 `tp6/app/news/` 存在
3. 当前应用切换为 `news`

访问：

```text
/hello
```

处理过程：

1. 第一段是 `hello`
2. 系统发现 `tp6/app/hello/` 不存在
3. 因为 `app_express=true`
4. 自动回退到默认应用 `home`
5. 后续应由 `home` 应用的控制器或 `home` 应用自己的路由处理

### 3. 这会直接影响路由文件加载位置

在当前项目配置下，框架通常会优先加载：

```text
tp6/app/{当前应用}/route/*.php
```

因此，日常业务开发时，应该优先把路由写到对应应用的 `route/` 目录，而不是直接写到根目录 `tp6/route/`。

## 三、默认路由规则

当前项目在 `tp6/config/route.php` 中配置了：

```php
'url_route_must' => false,
```

这表示项目没有强制要求“必须命中自定义路由”。

也就是说，当请求没有匹配到显式定义的路由规则时，框架仍然会继续尝试按默认控制器路由解析。

### 1. 默认路由格式

默认情况下，可按以下格式访问：

```text
/应用/控制器/操作
```

示例：

- `/home/index/index`
- `/admin/login/index`
- `/wechat/index/index`
- `/crawl/article/index`

### 2. 默认路由适用场景

默认路由最适合以下场景：

- 后台管理页面
- 模块内部功能页
- 调试接口
- 临时开发阶段
- 不要求对外 URL 固定的功能

### 3. 默认路由的优点

- 新增控制器后几乎可以直接访问
- 与应用目录、控制器结构天然对应
- 对模块化开发最省事
- 维护成本低

## 四、应用级自定义路由

### 1. 推荐位置

在当前项目中，自定义路由推荐写在：

```text
tp6/app/{应用名}/route/
```

例如：

- `tp6/app/home/route/app.php`
- `tp6/app/news/route/app.php`
- `tp6/app/admin/route/app.php`

### 2. 路由目录加载规则

框架会加载当前应用 `route/` 目录下的所有 `*.php` 文件。

因此：

- 可以只有一个 `app.php`
- 也可以拆成多个平级文件，例如 `api.php`、`admin.php`、`wechat.php`
- 不建议放在更深层子目录，因为当前加载规则是按 `route/*.php` 读取，不是递归读取

### 3. 新增应用级路由的步骤

以 `news` 应用为例：

1. 创建目录

```text
tp6/app/news/route/
```

2. 新建文件

```text
tp6/app/news/route/app.php
```

3. 编写路由

```php
<?php

use think\facade\Route;

Route::get('article/:id', 'Content/detail');
```

4. 在对应控制器中补充方法

```php
<?php

namespace app\news\controller;

use app\BaseController;

class Content extends BaseController
{
    public function detail($id)
    {
        return 'news article:' . $id;
    }
}
```

当请求命中 `news` 应用时，即可访问：

```text
/news/article/100
```

### 4. 应用级路由的目标写法

推荐写法：

```php
Route::get('hello/[:name]', 'Index/hello');
```

这里的 `Index/hello` 是“当前应用下的控制器/方法”，不需要再写成完整的 `home/Index/hello`。

因为当请求已经进入某个应用后，路由解析默认就是在当前应用上下文中进行。

## 五、全局路由

### 1. 全局路由位置

根目录全局路由通常写在：

```text
tp6/route/*.php
```

当前项目已有文件：

```text
tp6/route/app.php
```

### 2. 全局路由的作用

从框架层面看，全局路由是整个项目的路由定义入口。

但在当前 ZTBCMS 的多应用配置下，日常业务请求往往会先进入某个应用，然后切换到对应应用的路由目录，因此：

- 全局路由不是当前项目最主要的业务路由配置位置
- 日常业务开发应优先使用应用级路由
- 根目录路由更适合作为历史示例、特殊入口或统一说明

### 3. 当前项目中的建议

在当前配置下，不建议把前台和模块业务路由长期堆在 `tp6/route/app.php` 中，否则容易出现“看起来配了，但实际请求并没有按预期命中”的理解偏差。

如果某个功能明确属于 `home` 应用，应优先写到：

```text
tp6/app/home/route/app.php
```

如果属于 `news` 应用，则优先写到：

```text
tp6/app/news/route/app.php
```

## 六、为什么 `/hello` 要写到 `home` 应用路由里

这是当前项目最容易踩坑的一个例子。

访问：

```text
/hello
```

在本项目中的实际行为是：

1. 系统先尝试把 `hello` 识别为应用名
2. 发现 `tp6/app/hello/` 不存在
3. 因为 `app_express=true`
4. 自动回退到默认应用 `home`
5. 请求进入 `home` 应用上下文
6. 此时应加载 `tp6/app/home/route/*.php`

所以这条路由正确的落点是：

```text
tp6/app/home/route/app.php
```

示例：

```php
<?php

use think\facade\Route;

Route::get('hello/[:name]', 'Index/hello');
```

配套控制器示例：

```php
<?php

namespace app\home\controller;

use app\BaseController;

class Index extends BaseController
{
    public function hello(string $name = 'ThinkPHP6')
    {
        return 'hello,' . $name . '!';
    }
}
```

这样即可访问：

- `/hello`
- `/hello/唐老板`

## 七、路由编写示例

### 1. GET 路由

```php
Route::get('hello/[:name]', 'Index/hello');
```

### 2. POST 路由

```php
Route::post('user/login', 'User/login');
```

### 3. 同时支持多请求方法

```php
Route::rule('profile', 'User/profile', 'GET|POST');
```

### 4. 带参数约束

```php
Route::get('article/:id', 'Content/detail')->pattern(['id' => '\d+']);
```

### 5. 路由分组

```php
Route::group('api', function () {
    Route::get('config', 'Common/config');
    Route::post('login', 'User/login');
});
```

如果写在 `home` 应用路由文件中，则可访问：

- `/api/config`
- `/api/login`

### 6. 闭包路由

```php
Route::get('ping', function () {
    return 'pong';
});
```

闭包路由适合简单测试，不建议大量用于正式业务逻辑。

## 八、开发建议

### 1. 推荐分工

- 后台、模块内部页：优先默认路由
- 对外固定地址：优先应用级自定义路由
- API 对外接口：建议统一在对应应用下集中定义自定义路由
- 临时演示、调试地址：可以临时写简易路由，但上线前应整理

### 2. 推荐路由存放方式

建议按应用拆分：

- `home` 应用写 `tp6/app/home/route/*.php`
- `news` 应用写 `tp6/app/news/route/*.php`
- `admin` 应用写 `tp6/app/admin/route/*.php`

### 3. 推荐命名习惯

- URL 使用小写
- 多单词路径建议用中划线或清晰的层级结构
- 控制器目标保持简洁，不要写过长的路由映射

### 4. 当前项目最实用的原则

优先按“应用边界”组织路由，不要把所有业务统一堆到根目录全局路由里。

## 九、常见排查方法

### 1. 路由写了但不生效

先确认这次请求最终进入的是哪个应用。

例如：

- `/admin/login/index` 通常进入 `admin`
- `/news/content/index` 通常进入 `news`
- `/hello` 在当前配置下会进入 `home`

路由要写在对应应用的 `route/` 目录里。

### 2. 写在 `tp6/route/app.php` 里却没有命中

先检查当前请求是不是已经被多应用机制切到了某个应用。如果是，则更应该去检查：

```text
tp6/app/{应用名}/route/*.php
```

### 3. 路由命中了但控制器报不存在

重点检查：

- 控制器命名空间是否正确
- 控制器类名是否与文件名一致
- 路由目标是否写成了当前应用可解析的控制器/方法
- 方法名是否真实存在

### 4. 明明是前台地址，为什么报某个应用下控制器不存在

这通常意味着：

- 当前 URL 已经被识别到了某个应用
- 自定义路由没有命中
- 框架继续回退到默认控制器路由解析

此时应先确认应用归属，再决定是补应用级路由，还是直接按默认路由访问。
