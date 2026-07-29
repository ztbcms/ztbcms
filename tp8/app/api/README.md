# API 模块说明

## 快速上手

`api` 模块的控制器建议继承 `app\api\controller\BaseApi`。

继承后默认具备两类能力：

- 登录鉴权
- 接口限流

最简单的控制器示例：

```php
<?php

namespace app\api\controller;

use think\Request;

class User extends BaseApi
{
    /**
     * 跳过登录校验的方法
     */
    protected $skipAuthActions = ['login'];

    public function login()
    {
        return self::returnSuccessJson([
            'token' => '请替换成真实 token',
        ]);
    }

    public function profile(Request $request)
    {
        return self::returnSuccessJson($request->authorization);
    }
}
```

说明：

- `login` 被加入 `$skipAuthActions` 后，无需登录也可以访问
- `profile` 没有被排除，默认需要登录后访问
- 登录成功后，请在请求头中带上 `Authorization: Bearer token`

## 如何设置指定登录授权

### 1. 默认规则

只要控制器继承 `BaseApi`，默认就是“除白名单方法外，其余方法都要登录”。

### 2. 跳过指定方法的登录校验

如果只想让部分方法免登录，可以设置 `$skipAuthActions`：

```php
<?php

namespace app\api\controller;

class Article extends BaseApi
{
    protected $skipAuthActions = ['list', 'detail'];

    public function list()
    {
        return self::returnSuccessJson(['msg' => '列表免登录']);
    }

    public function detail()
    {
        return self::returnSuccessJson(['msg' => '详情免登录']);
    }

    public function favorite()
    {
        return self::returnSuccessJson(['msg' => '收藏需要登录']);
    }
}
```

上面的效果是：

- `list` 不需要登录
- `detail` 不需要登录
- `favorite` 需要登录

### 3. 跳过全部方法的登录校验

如果当前控制器全部接口都不需要登录，可以这样写：

```php
protected $skipAuthActions = ['*'];
```

### 4. 指定方法走“尝试登录”

有些接口允许游客访问，但如果用户已登录，又希望顺便拿到登录信息，可以设置 `$tryAuthActions`：

```php
<?php

namespace app\api\controller;

use think\Request;

class Feed extends BaseApi
{
    protected $skipAuthActions = ['index'];
    protected $tryAuthActions = ['index'];

    public function index(Request $request)
    {
        return self::returnSuccessJson([
            'user' => $request->authorization ?? null,
        ]);
    }
}
```

说明：

- 不带 token 也能访问
- 带了有效 token 时，可以从 `$request->authorization` 里拿到登录信息

## 如何配置限流

### 1. 默认行为

`BaseApi` 已经接入限流中间件，但默认是关闭的：

```php
protected $apiRateLimit = [
    'enabled' => false,
    'actions' => ['*'],
    'except' => [],
    'max_requests' => 60,
    'decay_seconds' => 60,
    'rules' => [],
];
```

含义：

- `enabled`：是否启用限流
- `actions`：哪些方法启用限流，`['*']` 表示全部
- `except`：排除哪些方法
- `max_requests`：时间窗口内允许的最大请求次数
- `decay_seconds`：时间窗口，单位秒
- `rules`：给单个方法单独设置规则

### 2. 给整个控制器开启限流

示例：同一 IP 访问当前控制器下的接口，60 秒最多 60 次。

```php
<?php

namespace app\api\controller;

class Sms extends BaseApi
{
    protected $skipAuthActions = ['sendCode'];

    protected $apiRateLimit = [
        'enabled' => true,
        'actions' => ['*'],
        'except' => [],
        'max_requests' => 60,
        'decay_seconds' => 60,
        'rules' => [],
    ];

    public function sendCode()
    {
        return self::returnSuccessJson(['msg' => '发送成功']);
    }
}
```

### 3. 只限制指定方法

如果只想限制某几个接口，可以这样写：

```php
protected $apiRateLimit = [
    'enabled' => true,
    'actions' => ['sendCode', 'login'],
    'except' => [],
    'max_requests' => 30,
    'decay_seconds' => 60,
    'rules' => [],
];
```

效果：

- `sendCode` 和 `login` 会被限流
- 其他方法不受这组限流规则影响

### 4. 排除指定方法

如果大部分接口都要限流，但想排除个别方法：

```php
protected $apiRateLimit = [
    'enabled' => true,
    'actions' => ['*'],
    'except' => ['ping'],
    'max_requests' => 60,
    'decay_seconds' => 60,
    'rules' => [],
];
```

效果：

- `ping` 不限流
- 其他方法按默认规则限流

### 5. 给单个方法单独设置更严格规则

例如登录接口和短信接口通常需要更严格限制：

```php
<?php

namespace app\api\controller;

class Auth extends BaseApi
{
    protected $skipAuthActions = ['login', 'sendSms'];

    protected $apiRateLimit = [
        'enabled' => true,
        'actions' => ['*'],
        'except' => [],
        'max_requests' => 60,
        'decay_seconds' => 60,
        'rules' => [
            'login' => [
                'max_requests' => 20,
                'decay_seconds' => 60,
            ],
            'sendSms' => [
                'max_requests' => 5,
                'decay_seconds' => 60,
            ],
        ],
    ];
}
```

效果：

- 大多数接口仍然是 60 秒最多 60 次
- `login` 单独收紧为 60 秒最多 20 次
- `sendSms` 单独收紧为 60 秒最多 5 次

## 返回结果说明

### 未登录时

如果访问了需要登录的接口，但没有带有效凭证，会直接返回失败信息。

### 被限流时

如果请求过于频繁，会返回提示“请求过于频繁，请稍后再试”，状态码为 `429`。

## 建议

- 登录、短信发送、验证码校验这类接口，建议单独收紧频率
- 对外开放的查询接口，建议至少做基础限流
- 如果接口完全公开，仍建议保留限流，不建议全部关闭
