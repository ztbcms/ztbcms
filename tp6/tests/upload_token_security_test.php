<?php

use app\admin\service\AdminUserService;
use app\api\controller\BaseApi;
use app\api\middleware\ApiAuth;
use app\common\controller\upload\Api as UploadApi;
use app\common\controller\upload\Upload as UploadController;
use app\common\model\upload\AttachmentModel;
use app\common\service\jwt\JwtService;
use think\App;
use think\facade\Config;
use think\facade\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

/**
 * 断言条件成立
 *
 * @param bool $condition 条件结果
 * @param string $message 失败提示
 * @return void
 */
function assertUploadSecurity(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 使用临时 JWT 场景配置执行回调，结束后恢复原配置
 *
 * @param array $overrides 覆盖配置
 * @param callable $callback 回调函数
 * @return void
 */
function withJwtScenes(array $overrides, callable $callback): void
{
    $original = Config::get('jwt');
    $current = is_array($original) ? $original : ['scene' => []];
    foreach ($overrides as $scene => $sceneConfig) {
        $current['scene'][$scene] = array_merge($current['scene'][$scene] ?? [], $sceneConfig);
    }
    Config::set($current, 'jwt');

    try {
        $callback();
    } finally {
        Config::set($original, 'jwt');
    }
}

/**
 * 使用反射重置 AdminUserService 静态缓存的用户信息
 *
 * @param mixed $userInfo 测试用户信息或 null
 * @return void
 */
function setAdminSessionUserInfo($userInfo): void
{
    $ref = new ReflectionClass(AdminUserService::class);
    $prop = $ref->getProperty('userInfo');
    $prop->setAccessible(true);
    $prop->setValue(null, []);

    if ($userInfo === null) {
        Session::delete(AdminUserService::KEY_USER_INFO);
        return;
    }

    Session::set(AdminUserService::KEY_USER_INFO, $userInfo);
}

// 1. 验证 jwt.php 中 upload 场景配置：默认 300s 过期，支持回退及独立密钥
$uploadSceneConfig = config('jwt.scene.upload');
assertUploadSecurity(is_array($uploadSceneConfig), 'jwt.scene.upload 配置应存在');
assertUploadSecurity(intval($uploadSceneConfig['ttl'] ?? 0) === 300, 'upload 场景默认 ttl 应为 300 秒');

withJwtScenes([
    'default' => ['secret_key' => 'test-default-secret'],
    'upload' => ['secret_key' => 'test-default-secret', 'ttl' => 300],
], function () use ($app) {
    // 2. 验证控制器初始化注入的 JWT 场景与用途
    $baseApi = new class($app) extends BaseApi {
        public function ping()
        {
            return 'ok';
        }
    };
    assertUploadSecurity($app->request->jwtScene === 'default', 'BaseApi 默认 jwtScene 应为 default');
    assertUploadSecurity($app->request->jwtPurpose === '', 'BaseApi 默认 jwtPurpose 应为空字符串');

    $uploadApi = new UploadApi($app);
    assertUploadSecurity($uploadApi instanceof UploadApi, 'Upload Api 控制器实例化失败');
    assertUploadSecurity($app->request->jwtScene === 'upload', 'upload\Api 应注入 jwtScene=upload');
    assertUploadSecurity($app->request->jwtPurpose === 'upload', 'upload\Api 应注入 jwtPurpose=upload');

    // 3. 验证 Upload::getUploadToken 忽略外部 user_id / user_type 参数，强绑定登录管理员
    setAdminSessionUserInfo([
        'id' => 7,
        'username' => 'auditor',
    ]);
    $app->request->withGet([
        'user_type' => 'user',
        'user_id' => '9999',
    ]);

    $uploadController = new UploadController($app);
    $tokenResponse = $uploadController->getUploadToken();
    $tokenPayload = $tokenResponse->getData();

    assertUploadSecurity(($tokenPayload['status'] ?? false) === true, '已登录管理员调用 getUploadToken 应返回成功');
    $token = (string)($tokenPayload['data']['token'] ?? '');
    assertUploadSecurity($token !== '', 'getUploadToken 应返回非空 token');
    assertUploadSecurity(intval($tokenPayload['data']['ttl'] ?? 0) === 300, 'getUploadToken 返回 ttl 应为 300');
    assertUploadSecurity(intval($tokenPayload['data']['expire_time'] ?? 0) > time(), 'getUploadToken 应返回有效的 expire_time');

    $parsedUpload = (new JwtService('upload'))->parserToken($token);
    assertUploadSecurity(($parsedUpload['status'] ?? false) === true, 'upload 场景应能解析上传凭证');
    assertUploadSecurity(intval($parsedUpload['data']['uid'] ?? 0) === 7, '上传凭证 uid 必须绑定服务端登录管理员 ID，忽略请求传入的 user_id');
    assertUploadSecurity(
        ($parsedUpload['data']['user_type'] ?? '') === AttachmentModel::USER_TYPE_ADMIN,
        '上传凭证 user_type 必须固定为 admin，忽略请求传入的 user_type'
    );
    assertUploadSecurity(
        ($parsedUpload['data']['purpose'] ?? '') === 'upload',
        '上传凭证必须包含 purpose=upload 声明'
    );

    // 4. 验证未登录或无效管理员 ID 时拒绝签发
    setAdminSessionUserInfo(null);
    $unloginResp = $uploadController->getUploadToken()->getData();
    assertUploadSecurity(($unloginResp['status'] ?? true) === false, '未登录管理员调用 getUploadToken 应返回失败');

    setAdminSessionUserInfo(['id' => 0, 'username' => 'invalid']);
    $invalidIdResp = $uploadController->getUploadToken()->getData();
    assertUploadSecurity(($invalidIdResp['status'] ?? true) === false, '管理员 ID 无效时应拒绝签发上传凭证');

    // 5. 验证 ApiAuth 双向隔离：即使未配置独立密钥（共用 default 密钥），purpose 声明也能阻止跨接口越权
    $apiAuth = new ApiAuth();
    $normalToken = (new JwtService('default'))->createToken([
        'uid' => 101,
        'exp' => time() + 600,
    ]);

    // 5a. 上传 Token 访问前台 BaseApi（auth 模式）应返回 401
    $reqToBaseApi = clone $app->request;
    $reqToBaseApi->setAction('authInfo');
    $reqToBaseApi->skipAuthActions = [];
    $reqToBaseApi->tryAuthActions = [];
    $reqToBaseApi->jwtScene = 'default';
    $reqToBaseApi->jwtPurpose = '';
    $reqToBaseApi->withHeader(['Authorization' => 'Bearer ' . $token]);
    $reqToBaseApi->authorization = null;
    $baseApiRes = $apiAuth->handle($reqToBaseApi, function () {
        return json(['status' => true, 'code' => 200]);
    });
    $baseApiData = $baseApiRes->getData();
    assertUploadSecurity(
        ($baseApiData['status'] ?? true) === false
        && intval($baseApiData['code'] ?? 0) === 401
        && ($baseApiData['msg'] ?? '') === 'Token无效',
        '上传凭证不能通过前台 BaseApi 鉴权'
    );

    // 5b. 上传 Token 访问前台 BaseApi（tryAuth 模式）不应注入 authorization
    $reqTryAuth = clone $app->request;
    $reqTryAuth->setAction('tryAction');
    $reqTryAuth->skipAuthActions = [];
    $reqTryAuth->tryAuthActions = ['tryAction'];
    $reqTryAuth->jwtScene = 'default';
    $reqTryAuth->jwtPurpose = '';
    $reqTryAuth->withHeader(['Authorization' => 'Bearer ' . $token]);
    $reqTryAuth->authorization = null;
    $apiAuth->handle($reqTryAuth, function ($req) {
        assertUploadSecurity(empty($req->authorization), 'tryAuth 模式下上传凭证不应注入前台 authorization');
        return json(['status' => true, 'code' => 200]);
    });

    // 5c. 常规前台 API Token（无 purpose）访问上传接口（jwtPurpose=upload）应返回 401
    $reqNormalToUpload = clone $app->request;
    $reqNormalToUpload->setAction('getUploadConfig');
    $reqNormalToUpload->skipAuthActions = [];
    $reqNormalToUpload->tryAuthActions = [];
    $reqNormalToUpload->jwtScene = 'upload';
    $reqNormalToUpload->jwtPurpose = 'upload';
    $reqNormalToUpload->withHeader(['Authorization' => 'Bearer ' . $normalToken]);
    $reqNormalToUpload->authorization = null;
    $uploadRejectRes = $apiAuth->handle($reqNormalToUpload, function () {
        return json(['status' => true, 'code' => 200]);
    });
    $uploadRejectData = $uploadRejectRes->getData();
    assertUploadSecurity(
        ($uploadRejectData['status'] ?? true) === false
        && intval($uploadRejectData['code'] ?? 0) === 401
        && ($uploadRejectData['msg'] ?? '') === 'Token无效',
        '常规前台 API 凭证（无 purpose=upload）不能访问上传接口'
    );

    // 5e. 常规前台 API Token 正常通过 BaseApi 鉴权
    $reqNormalPass = clone $app->request;
    $reqNormalPass->setAction('authInfo');
    $reqNormalPass->skipAuthActions = [];
    $reqNormalPass->tryAuthActions = [];
    $reqNormalPass->jwtScene = 'default';
    $reqNormalPass->jwtPurpose = '';
    $reqNormalPass->withHeader(['Authorization' => 'Bearer ' . $normalToken]);
    $reqNormalPass->authorization = null;
    $normalPassRes = $apiAuth->handle($reqNormalPass, function ($req) {
        return json([
            'status' => true,
            'code' => 200,
            'uid' => $req->authorization['uid'] ?? 0,
        ]);
    });
    $normalPassData = $normalPassRes->getData();
    assertUploadSecurity(
        ($normalPassData['status'] ?? false) === true && intval($normalPassData['uid'] ?? 0) === 101,
        '常规前台 API 凭证应正常通过 BaseApi 鉴权'
    );

    // 5f. 伪造请求参数或请求头中的 jwtPurpose / jwtScene 不能绕过服务端校验
    $reqForgedContext = clone $app->request;
    $reqForgedContext->setAction('authInfo');
    $reqForgedContext->skipAuthActions = [];
    $reqForgedContext->tryAuthActions = [];
    $reqForgedContext->jwtScene = 'default';
    $reqForgedContext->jwtPurpose = '';
    $reqForgedContext->withGet(['jwtPurpose' => 'upload', 'jwtScene' => 'upload']);
    $reqForgedContext->withHeader([
        'Authorization' => 'Bearer ' . $token,
        'X-Jwt-Purpose' => 'upload',
        'X-Jwt-Scene' => 'upload',
    ]);
    $reqForgedContext->authorization = null;
    $forgedRes = $apiAuth->handle($reqForgedContext, function () {
        return json(['status' => true, 'code' => 200]);
    });
    $forgedData = $forgedRes->getData();
    assertUploadSecurity(
        ($forgedData['status'] ?? true) === false && intval($forgedData['code'] ?? 0) === 401,
        '伪造请求参数/请求头中的 jwtPurpose 与 jwtScene 不能绕过服务端校验'
    );

    // 5d. 上传 Token 访问上传接口（jwtScene=upload, jwtPurpose=upload）应通过鉴权
    $reqUploadPass = clone $app->request;
    $reqUploadPass->setAction('getUploadConfig');
    $reqUploadPass->skipAuthActions = [];
    $reqUploadPass->tryAuthActions = [];
    $reqUploadPass->jwtScene = 'upload';
    $reqUploadPass->jwtPurpose = 'upload';
    $reqUploadPass->withHeader(['Authorization' => 'Bearer ' . $token]);
    $reqUploadPass->authorization = null;
    $uploadPassRes = $apiAuth->handle($reqUploadPass, function ($req) {
        return json([
            'status' => true,
            'code' => 200,
            'uid' => $req->authorization['uid'] ?? 0,
            'user_type' => $req->authorization['user_type'] ?? '',
        ]);
    });
    $uploadPassData = $uploadPassRes->getData();
    assertUploadSecurity(
        ($uploadPassData['status'] ?? false) === true
        && intval($uploadPassData['uid'] ?? 0) === 7
        && ($uploadPassData['user_type'] ?? '') === AttachmentModel::USER_TYPE_ADMIN,
        '上传凭证应正常通过 upload\Api 鉴权并携带正确管理员身份'
    );
});

// 6. 验证配置独立 upload 密钥时的签名级隔离
withJwtScenes([
    'default' => ['secret_key' => 'isolated-default-secret'],
    'upload' => ['secret_key' => 'isolated-upload-secret', 'ttl' => 300],
], function () {
    $uploadJwt = new JwtService('upload');
    $defaultJwt = new JwtService('default');
    $uploadToken = $uploadJwt->createToken(['uid' => 1, 'purpose' => 'upload', 'exp' => time() + 300]);
    $defaultToken = $defaultJwt->createToken(['uid' => 1, 'exp' => time() + 300]);

    assertUploadSecurity($defaultJwt->parserToken($uploadToken)['status'] === false, '独立 upload 密钥签发的 Token 无法被 default 场景验签');
    assertUploadSecurity($uploadJwt->parserToken($defaultToken)['status'] === false, 'default 密钥签发的 Token 无法被独立 upload 场景验签');
});

// 7. 验证受限角色权限配置与菜单节点
$uploadRef = new ReflectionClass(UploadController::class);
$defaultProps = $uploadRef->getDefaultProperties();
$noNeedPermission = $defaultProps['noNeedPermission'] ?? [];
assertUploadSecurity(
    in_array('getUploadToken', $noNeedPermission, true),
    'Upload::$noNeedPermission 必须包含 getUploadToken，确保受限角色可使用上传面板'
);

$menuTree = require dirname(__DIR__) . '/app/common/install/Menu.php';
$routes = [];
$collectRoutes = function (array $items) use (&$collectRoutes, &$routes) {
    foreach ($items as $item) {
        if (!empty($item['route'])) {
            $routes[] = $item['route'];
        }
        if (!empty($item['child']) && is_array($item['child'])) {
            $collectRoutes($item['child']);
        }
    }
};
$collectRoutes($menuTree);
assertUploadSecurity(in_array('common/upload.upload/getUploadToken', $routes, true), 'common/install/Menu.php 应包含 getUploadToken 路由');
assertUploadSecurity(in_array('common/upload.upload/directUploadDemo', $routes, true), 'common/install/Menu.php 应包含 directUploadDemo 路由');

echo "upload_token_security_test passed\n";
