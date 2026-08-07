<?php

use app\admin\controller\Login;
use think\App;
use think\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);

$app = new App();
$app->initialize();
$controller = (new ReflectionClass(Login::class))->newInstanceWithoutConstructor();

$invalidCredentialCases = [
    '缺失 form' => [],
    'form 为字符串' => ['form' => 'invalid'],
    'form 为空数组' => ['form' => []],
    'form 缺少 password' => ['form' => [
        'username' => base64_encode('admin'),
        'code' => '',
    ]],
    'username 为数组' => ['form' => [
        'username' => [],
        'password' => base64_encode('password'),
        'code' => '',
    ]],
    'password 为 null' => ['form' => [
        'username' => base64_encode('admin'),
        'password' => null,
        'code' => '',
    ]],
    'username 为非法 Base64' => ['form' => [
        'username' => '%%%',
        'password' => base64_encode('password'),
        'code' => '',
    ]],
];

foreach ($invalidCredentialCases as $caseName => $postData) {
    $request = new Request();
    $request->withServer(['REQUEST_METHOD' => 'POST'])->withPost($postData);
    $app->instance('request', $request);
    $responseData = $controller->doLogin()->getData();
    if (($responseData['status'] ?? true) !== false) {
        throw new RuntimeException($caseName . ' 应返回失败状态');
    }
    if (($responseData['msg'] ?? '') !== '用户名或者密码不能为空，请重新输入') {
        throw new RuntimeException($caseName . ' 返回提示不符合预期');
    }
}

$request = new Request();
$request->withServer(['REQUEST_METHOD' => 'POST'])->withPost(['form' => [
    'username' => base64_encode(rawurlencode('admin@example.com')),
    'password' => base64_encode(rawurlencode('p@ss word')),
    'code' => [],
]]);
$app->instance('request', $request);
$invalidCodeResponse = $controller->doLogin()->getData();
if (($invalidCodeResponse['status'] ?? true) !== false
    || ($invalidCodeResponse['msg'] ?? '') !== '请输入验证码') {
    throw new RuntimeException('验证码为数组时应返回验证码提示');
}

$request = new Request();
$request->withServer(['REQUEST_METHOD' => 'POST'])->withPost(['form' => [
    'username' => base64_encode(rawurlencode('admin@example.com')),
    'password' => base64_encode(rawurlencode('p@ss word')),
    'code' => '',
]]);
$app->instance('request', $request);
$validDecodeResponse = $controller->doLogin()->getData();
if (($validDecodeResponse['status'] ?? true) !== false
    || ($validDecodeResponse['msg'] ?? '') !== '请输入验证码') {
    throw new RuntimeException('合法编码凭据应通过解码并进入验证码校验');
}

echo "admin_login_form_php83_test passed\n";
