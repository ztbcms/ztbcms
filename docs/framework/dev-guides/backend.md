# 3. 后端开发手册（增强版）

## 3.1 适用范围

本文面向本项目日常后端开发，重点解决“新接口怎么写、写到什么程度算合格”的问题。

- 管理后台页面、后台接口：继承 `AdminController`
- 通用 API 接口：继承 `app\api\controller\BaseApi`
- 不需要后台登录态的公共控制器：继承 `BaseController`
- 业务逻辑尽量下沉到 Service，Controller 负责接参数、调 Service、返回结果

## 3.2 Controller 选型与基础约定

### 管理后台

- 管理后台请继承 `AdminController`
- 继承后会自动启用 Session、后台登录校验、权限校验、操作日志中间件
- 可通过 `$noNeedLogin` 放行免登录方法
- 可通过 `$noNeedPermission` 放行登录后免权限方法

```php
class Region extends AdminController
{
    public $noNeedPermission = ['getList', 'getDetail'];
}
```

### API 接口

- 对外 API 推荐继承 `BaseApi`
- `BaseApi` 已内置接口鉴权与限流中间件
- 完全免鉴权的方法放在 `$skipAuthActions`
- “有 token 就识别，没有也可访问”的方法放在 `$tryAuthActions`

```php
class DemoApi extends BaseApi
{
    protected $skipAuthActions = ['ping'];
    protected $tryAuthActions = ['detail'];
}
```

## 3.3 标准开发流程

一个新的接口，默认按下面顺序写：

1. 选对控制器基类
2. 明确是页面入口还是纯数据接口
3. 统一接收参数并做基础清洗
4. 做必填、类型、范围校验
5. 调用 Service 处理业务
6. 按统一结构返回
7. 需要留痕的操作补操作日志
8. 自测成功、失败、边界三类场景

推荐写法：

```php
public function save()
{
    $id = input('id', 0, 'intval');
    $data = [
        'title' => input('title', '', 'trim'),
        'sort' => input('sort', 0, 'intval'),
        'status' => input('status', 1, 'intval'),
    ];

    if ($data['title'] === '') {
        return json(self::createReturn(false, null, '请填写标题'));
    }

    $service = DemoService::getInstance();
    $result = $id > 0 ? $service->update($id, $data) : $service->add($data);

    return json($result);
}
```

## 3.4 参数获取与参数校验

### 参数获取

- 优先使用 `input()` 或 `request()->param()` 取参
- 按类型加过滤器，常用 `intval`、`trim`
- 数组参数显式按数组取值，避免混入脏数据

常见写法：

```php
$id = input('id', 0, 'intval');
$keyword = input('keyword', '', 'trim');
$page = input('page', 1, 'intval');
$limit = input('limit', 20, 'intval');
$ids = input('ids/a', []);
```

### 校验原则

- Controller 至少做必填校验和明显错误拦截
- 复杂规则优先放到 Validate 或 Service
- 校验失败直接返回统一错误结果，不继续往下执行
- 需要批量规则时，使用 `validate()` 或独立验证器

基础校验示例：

```php
if (empty($regionId)) {
    return json(self::createReturn(false, null, '参数错误'));
}

if ($data['region_name'] === '') {
    return json(self::createReturn(false, null, '请填写地区名称'));
}
```

验证器示例：

```php
use think\exception\ValidateException;

try {
    $this->validate($data, [
        'title' => 'require|max:100',
        'sort' => 'integer|egt:0',
    ]);
} catch (ValidateException $e) {
    return json(self::createReturn(false, null, $e->getMessage()));
}
```

## 3.5 返回规范

### 统一返回结构

本项目统一返回以下结构：

```php
[
    'status' => true,
    'code' => 200,
    'data' => [],
    'msg' => '',
]
```

常用方法：

- `self::createReturn($status, $data, $msg, $code)`
- `self::makeJsonReturn(...)`
- `self::returnSuccessJson(...)`
- `self::returnErrorJson(...)`
- `BaseService::createReturn(...)`

最小示例：

```php
return self::returnSuccessJson(['id' => $id], '保存成功');
```

```php
return self::returnErrorJson('参数错误');
```

### 状态码说明

- `200` 正常
- `400` 业务失败或参数错误
- `401` 未登录或凭证无效
- `403` 已登录但没有权限，或账号不可用
- `404` 找不到资源

### 返回内容建议

- 成功时 `msg` 可留空，也可返回“保存成功”“删除成功”
- 失败时 `msg` 必须能让调用方知道哪里错了
- `data` 没有内容时建议返回 `[]` 或 `null`，保持前后统一
- 不要直接把调试信息、堆栈信息返回给调用方

## 3.6 登录态与权限

### 后台登录态

- 后台控制器继承 `AdminController` 后，默认启用登录校验
- 未登录的 Ajax 请求会返回 `401`
- 已登录但无权限的 Ajax 请求会返回 `403`
- 账号被禁用时也会返回 `403`

常用控制项：

```php
public $noNeedLogin = ['login'];
public $noNeedPermission = ['getList', 'getDetail'];
```

使用建议：

- 登录页、验证码、公开查询页可放入 `$noNeedLogin`
- 下拉列表、详情读取等页面内公共接口，可按需放入 `$noNeedPermission`
- 不确定是否需要放行时，默认不要放开

### API 登录态

- API 鉴权走 `Authorization: Bearer <token>`
- 当前登录人信息解析后写入 `request()->authorization`
- 需要登录的接口，从中读取 `uid`、`user_type`

```php
$authorization = request()->authorization ?? [];
$userId = intval($authorization['uid'] ?? 0);
$userType = $authorization['user_type'] ?? 'user';
```

## 3.7 分页写法

### 推荐写法

新业务 Service 优先使用 `BaseService::createReturnList()` 返回分页结果：

```php
$total = $query->count();
$list = $query->page($page, $limit)->select()->toArray();

return self::createReturnList(true, $list, $page, $limit, $total, ceil($total / $limit));
```

返回结构示例：

```php
[
    'status' => true,
    'code' => 200,
    'data' => [
        'items' => [],
        'page' => 1,
        'limit' => 20,
        'total_items' => 100,
        'total_pages' => 5,
    ],
    'msg' => '',
]
```

### 分页约定

- 页码字段统一用 `page`
- 每页条数字段统一用 `limit`
- 默认第一页从 `1` 开始
- `limit` 要给默认值，并限制合理范围

```php
$page = max(1, input('page', 1, 'intval'));
$limit = input('limit', 20, 'intval');
$limit = min(max($limit, 1), 100);
```

### 兼容旧页面

项目里存在部分旧页面直接消费分页对象字段，例如 `current_page`、`per_page`、`last_page`、`total`。新开发优先统一用推荐结构；如果是接入旧页面，不要为了“统一”硬改旧接口，先兼容现有页面使用方式。

## 3.8 上传场景

### 后台上传

- 后台上传一般由后台登录态进入
- 需要上传身份时，可先获取上传临时 token
- 上传配置、附件管理、上传面板已在公共模块中提供现成能力

获取后台上传 token 的返回示例：

```php
return self::returnSuccessJson(['token' => $token]);
```

### 前台或 API 上传

- 前台上传统一走 `app\common\controller\upload\Api`
- 常见接口包括：
  - 获取上传配置
  - 普通上传
  - 本地直传
  - 获取直传凭证
  - 直传完成后保存附件记录

普通上传成功后的常见返回字段：

```php
[
    'aid' => 1,
    'filename' => 'demo.jpg',
    'fileurl' => 'https://...',
    'filethumb' => 'https://...',
]
```

### 上传开发注意事项

- 上传前先明确文件类型、大小限制、是否私有读
- 上传成功后，返回前端真正要用的字段，不要只返回内部路径
- 需要记录归属时，写清用户、分组、模块、驱动
- 删除附件时，数据库记录和物理文件要一起处理

## 3.9 异常处理

### 推荐原则

- 能直接判断的业务错误，优先直接返回统一失败结果
- 只有在确实可能抛异常的地方，再用 `try/catch`
- 捕获异常后，返回可读错误，不把异常堆栈暴露给前端

示例：

```php
try {
    $attachment->save($data);
    return self::createReturn(true, ['id' => $attachment->id], '保存成功');
} catch (\Exception $e) {
    return self::createReturn(false, null, '保存失败：' . $e->getMessage());
}
```

### 建议区分三类失败

- 参数类失败：如“参数错误”“请填写标题”
- 业务类失败：如“数据不存在”“编码已存在”
- 系统类失败：如上传失败、数据库写入失败、第三方调用失败

## 3.10 用户操作日志

涉及后台人工操作、配置改动、删除、安装、审核等动作时，建议补操作日志。

示例：

```php
$logData = [
    'source_type' => 'install_module',
    'source' => 'demo',
    'content' => '安装模块：demo',
];

UserOperateLogService::addUserOperateLog($logData);
```

日志最少应包含：

- 操作分类
- 操作来源
- 操作内容

## 3.11 开发检查清单

接口完成前，至少逐项检查：

- 是否继承了正确的基类
- 参数是否做了默认值、类型转换、必填校验
- 返回结构是否统一
- 登录态、权限是否符合预期
- 分页字段是否与页面对得上
- 上传接口是否限制了类型、大小、权限
- 异常时是否能返回可读错误
- 需要留痕的操作是否写了日志
- 已验证成功、失败、边界三类场景
