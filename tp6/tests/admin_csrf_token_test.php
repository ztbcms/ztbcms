<?php

use think\App;
use think\facade\Config;
use think\facade\Session;
use think\facade\View;
use think\Request;
use think\Response;
use app\common\controller\cron\Dashboard;
use app\common\controller\upload\Panel;
use app\common\controller\upload\Upload;
use app\common\middleware\AdminCsrf;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));
$app->initialize();
$app->http->name('admin');
$app->setAppPath($app->getBasePath() . 'admin' . DIRECTORY_SEPARATOR);
$middleware = new AdminCsrf();

function assertCsrfTokenSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

/** @return Response */
$dispatch = static function (string $method, array $headers = [], array $post = []) use ($app, $middleware) {
    $request = (new Request())
        ->withServer(['REQUEST_METHOD' => $method])
        ->withHeader($headers)
        ->withPost($post)
        ->withSession($app->session);

    return $middleware->handle($request, static function () use ($method) {
        if ($method !== 'GET') {
            return json(['action' => 'reached']);
        }
        return response(View::fetch('login/index'));
    });
};

Config::set(['csrf_enabled' => false], 'admin');
assertCsrfTokenSame(200, $dispatch('POST')->getCode(), '旧实例关闭校验时应继续接受原有 POST');

Config::set(['csrf_enabled' => true], 'admin');
Session::clear();
assertCsrfTokenSame(403, $dispatch('POST')->getCode(), '缺少会话 Token 时应拒绝写请求');

$page = $dispatch('GET');
assertCsrfTokenSame('no-store', $page->getHeader('Cache-Control'), '含 Token 的 HTML 页面应禁止缓存');
assertCsrfTokenSame(1, preg_match('/<meta name="csrf-token" content="([a-f0-9]{64})">/', $page->getContent(), $matches), '后台模板应输出随机 Token');
$token = $matches[1];
assertCsrfTokenSame(true, strpos($dispatch('GET')->getContent(), $token) !== false, '同一会话打开其他页面后 Token 应保持有效');
assertCsrfTokenSame(403, $dispatch('POST')->getData()['code'], '开启校验后缺少请求头应返回 403');
assertCsrfTokenSame(403, $dispatch('POST', ['X-CSRF-TOKEN' => 'incorrect'])->getCode(), '错误 Token 应被拒绝');
assertCsrfTokenSame(403, $dispatch('POST', ['X-CSRF-TOKEN' => [$token]])->getCode(), '非字符串 Token 应被拒绝');
assertCsrfTokenSame(403, $dispatch('POST', [], ['_method' => 'GET'])->getCode(), '方法覆盖后仍应校验原始 POST');

foreach (['POST', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
    assertCsrfTokenSame(['action' => 'reached'], $dispatch($method, ['X-CSRF-TOKEN' => $token])->getData(), '同一 Token 应支持连续写请求');
}

foreach (['HEAD', 'OPTIONS'] as $method) {
    assertCsrfTokenSame(200, $dispatch($method)->getCode(), '只读请求可以省略 Token');
}

Session::clear();
$expiredResponse = $dispatch('POST', ['X-CSRF-TOKEN' => $token]);
assertCsrfTokenSame(403, $expiredResponse->getCode(), '清空会话后旧 Token 应失效');
assertCsrfTokenSame(
    'csrf_validation_failed',
    $expiredResponse->getData()['data']['reason'] ?? null,
    '后台页面应能区分 CSRF 校验失败与普通权限拒绝'
);
assertCsrfTokenSame(false, strpos($dispatch('GET')->getContent(), $token) !== false, '新会话应生成新的 Token');

$cron = (new ReflectionClass(Dashboard::class))->newInstanceWithoutConstructor();
$panel = (new ReflectionClass(Panel::class))->newInstanceWithoutConstructor();
$upload = (new ReflectionClass(Upload::class))->newInstanceWithoutConstructor();
$getRequest = static function (array $query = []) use ($app): Request {
    $request = (new Request())->withServer(['REQUEST_METHOD' => 'GET'])->withGet($query);
    $app->instance('request', $request);
    return $request;
};

foreach (['runAction', 'deleteCron'] as $action) {
    assertCsrfTokenSame(405, $cron->$action($getRequest())->getCode(), '计划任务写操作应拒绝 GET');
}
foreach (['setCronEnable', 'setCronSecretKey'] as $action) {
    assertCsrfTokenSame(405, $cron->index($getRequest(['_action' => $action]))->getCode(), '计划任务设置应拒绝 GET');
}
foreach (['deleteFiles', 'moveGalleryGroup', 'delGalleryGroup', 'editGalleryGroup', 'addGalleryGroup'] as $action) {
    assertCsrfTokenSame(405, $panel->$action($getRequest())->getCode(), '附件面板写操作应拒绝 GET');
}
foreach (['doDelete', 'doBatchDelete'] as $action) {
    $getRequest(['_action' => $action]);
    assertCsrfTokenSame(405, $upload->attachments()->getCode(), '附件删除应拒绝 GET');
}

echo "admin_csrf_token_test passed\n";
