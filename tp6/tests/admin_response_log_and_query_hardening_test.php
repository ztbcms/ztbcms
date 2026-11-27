<?php

use app\admin\controller\AdminManager;
use app\admin\controller\Logs;
use app\admin\model\OperationlogModel;
use app\common\libs\helper\SqlHelper;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\Request;
use think\Response;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/TestCacheHelper.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

set_error_handler(
    function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

function assertHardening(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

$tmpCacheDir = sys_get_temp_dir() . '/ztbcms_hardening_cache_' . uniqid('', true);
mkdir($tmpCacheDir, 0777, true);

try {
    Config::set([
        'default' => 'hardening_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'hardening_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    configureIsolatedTestFileCache($tmpCacheDir);

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'admin_operation_switch',
        'info' => '是否启用后台操作日志',
        'groupid' => 1,
        'value' => '1',
    ]);

    Db::execute('CREATE TABLE operation_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uid INTEGER NOT NULL DEFAULT 0,
        time INTEGER NOT NULL DEFAULT 0,
        ip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        method VARCHAR(32) NOT NULL DEFAULT "",
        url VARCHAR(512) NOT NULL DEFAULT "",
        params TEXT NOT NULL DEFAULT "",
        response TEXT NOT NULL DEFAULT ""
    )');

    Db::execute('CREATE TABLE login_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL DEFAULT "",
        logintime INTEGER NOT NULL DEFAULT 0,
        loginip VARCHAR(64) NOT NULL DEFAULT "",
        status INTEGER NOT NULL DEFAULT 0,
        password VARCHAR(64) NOT NULL DEFAULT "",
        info VARCHAR(255) NOT NULL DEFAULT ""
    )');

    // 1. OperationlogModel::record 对响应体进行敏感字段脱敏与超长摘要截断
    $req = new Request();
    $req->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1', 'REQUEST_URI' => '/admin/Config/save']);
    $req->setMethod('POST');
    $req->setController('Config');
    $req->setAction('save');
    $req->withPost(['sitename' => 'demo']);
    $app->instance('request', $req);

    $sensitiveResp = Response::create(json_encode([
        'status' => true,
        'code'   => 200,
        'msg'    => 'ok',
        'data'   => [
            'token'                       => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.secret',
            'attachment_aliyun_key_secret' => 'AliyunSecretXYZ',
            'attachment_aliyun_key_id'     => 'LTAI5tPublicId',
            'nested'                      => [
                'access_token' => 'NestToken!999',
                'title'        => '保留业务标题',
            ],
        ],
    ], JSON_UNESCAPED_UNICODE), 'html', 200);

    assertHardening(OperationlogModel::record($sensitiveResp) === true, '操作日志应记录成功');
    $savedRow = Db::name('operation_log')->order('id', 'desc')->find();
    $savedResp = (string) ($savedRow['response'] ?? '');
    assertHardening(strpos($savedResp, 'eyJhbGciOi') === false, '响应体中的 token 必须脱敏');
    assertHardening(strpos($savedResp, 'AliyunSecretXYZ') === false, '响应体中的 key_secret 必须脱敏');
    assertHardening(strpos($savedResp, 'NestToken!999') === false, '嵌套 access_token 必须脱敏');
    assertHardening(strpos($savedResp, 'LTAI5tPublicId') !== false, '非敏感字段 key_id 应保留');
    assertHardening(strpos($savedResp, '保留业务标题') !== false, '普通业务字段应保留');

    // 超大响应体应脱敏并截断为合法 JSON 摘要
    $largePayload = json_encode([
        'status' => true,
        'code'   => 200,
        'msg'    => 'large ok',
        'data'   => ['blob' => str_repeat('A', 8000), 'app_secret' => 'LargeSecretValue!'],
    ], JSON_UNESCAPED_UNICODE);
    $sanitizedLarge = OperationlogModel::sanitizeResponseContent($largePayload, 4096);
    $largeDecoded = json_decode($sanitizedLarge, true);
    assertHardening(is_array($largeDecoded), '超长响应脱敏截断后仍应为合法 JSON');
    assertHardening(($largeDecoded['_truncated'] ?? false) === true, '超长响应应标记 _truncated=true');
    assertHardening(strpos($sanitizedLarge, 'LargeSecretValue!') === false, '超长响应不得泄露密钥');

    // 历史未脱敏响应体在 adminOperationLogList 查询时也必须兜底脱敏
    Db::name('operation_log')->insert([
        'uid'      => 1,
        'time'     => time(),
        'ip'       => '127.0.0.1',
        'status'   => 1,
        'method'   => 'POST',
        'url'      => '/admin/Test/getSecret',
        'params'   => '{}',
        'response' => '{"status":true,"data":{"secret_key":"HistRespSecret!"}}',
    ]);
    $listReq = new Request();
    $listReq->withServer(['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1']);
    $listReq->withGet(['_action' => 'getList', 'page' => 1, 'limit' => 20]);
    $app->instance('request', $listReq);
    $logsCtrl = new Logs($app);
    $opListData = $logsCtrl->adminOperationLogList($listReq)->getData();
    $opJson = (string) json_encode($opListData, JSON_UNESCAPED_UNICODE);
    assertHardening(strpos($opJson, 'HistRespSecret!') === false, 'adminOperationLogList 读取历史日志应兜底脱敏响应体');

    // 私有附件签名 URL 在响应写入及历史列表读取时均必须脱敏
    $signedReq = new Request();
    $signedReq->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1', 'REQUEST_URI' => '/common/upload.Upload/attachments']);
    $signedReq->setMethod('POST');
    $signedReq->setController('upload.Upload');
    $signedReq->setAction('attachments');
    $app->instance('request', $signedReq);

    $signedResp = Response::create(json_encode([
        'status' => true,
        'data'   => [
            'items' => [
                [
                    'aid'       => 10,
                    'fileurl'   => 'https://bucket.oss-cn-hangzhou.aliyuncs.com/image/a.png?OSSAccessKeyId=LTAI5tDemo&Expires=1799999999&Signature=SecretSig123',
                    'filethumb' => 'https://cdn.example.com/a.png?e=1799999999&token=QiniuSignedToken456',
                ],
                [
                    'aid'       => 11,
                    'fileurl'   => 'https://cdn.example.com/public/b.png?v=1',
                    'filethumb' => 'https://cdn.example.com/public/b.png',
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE), 'html', 200);
    assertHardening(OperationlogModel::record($signedResp) === true, '带签名 URL 的响应应记录日志');
    $signedLogRow = Db::name('operation_log')->order('id', 'desc')->find();
    $signedRowResp = (string) ($signedLogRow['response'] ?? '');
    assertHardening(strpos($signedRowResp, 'SecretSig123') === false, '响应日志不得保留 OSS Signature');
    assertHardening(strpos($signedRowResp, 'QiniuSignedToken456') === false, '响应日志不得保留七牛签名 token');
    $signedRowDecoded = json_decode($signedRowResp, true);
    assertHardening(
        ($signedRowDecoded['data']['items'][0]['fileurl'] ?? '') === 'https://bucket.oss-cn-hangzhou.aliyuncs.com/image/a.png?***REDACTED***',
        '私有 OSS 签名 URL 查询串应脱敏'
    );
    assertHardening(
        ($signedRowDecoded['data']['items'][0]['filethumb'] ?? '') === 'https://cdn.example.com/a.png?***REDACTED***',
        '私有七牛签名缩略图 URL 查询串应脱敏'
    );
    assertHardening(
        ($signedRowDecoded['data']['items'][1]['fileurl'] ?? '') === 'https://cdn.example.com/public/b.png?v=1',
        '普通静态版本号查询串应保留'
    );

    Db::name('operation_log')->insert([
        'uid'      => 1,
        'time'     => time(),
        'ip'       => '127.0.0.1',
        'status'   => 1,
        'method'   => 'POST',
        'url'      => '/common/upload.Upload/attachments',
        'params'   => '{}',
        'response' => '{"status":true,"data":{"fileurl":"https://bucket.oss.com/x.png?Signature=OldLeakSig999&Expires=1700000000"}}',
    ]);
    $histListReq = new Request();
    $histListReq->withServer(['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1']);
    $histListReq->withGet(['_action' => 'getList', 'page' => 1, 'limit' => 20]);
    $app->instance('request', $histListReq);
    $histRespJson = (string) json_encode($logsCtrl->adminOperationLogList($histListReq)->getData(), JSON_UNESCAPED_UNICODE);
    assertHardening(strpos($histRespJson, 'OldLeakSig999') === false, '历史响应日志读取时同样应脱敏签名 URL');

    // 2. SqlHelper::escapeLike 转义 % _ \
    assertHardening(SqlHelper::escapeLike('100%_a\\b') === '100\\%\\_a\\\\b', 'SqlHelper::escapeLike 应转义 %, _, \\');

    // 传入 % 或 _ 搜索 loginLogList 不得匹配无关记录
    Db::name('login_log')->insertAll([
        ['username' => 'alice', 'logintime' => time(), 'loginip' => '127.0.0.1', 'status' => 1, 'password' => '', 'info' => 'ok'],
        ['username' => 'bob', 'logintime' => time(), 'loginip' => '127.0.0.1', 'status' => 1, 'password' => '', 'info' => 'ok'],
    ]);
    $wildcardReq = new Request();
    $wildcardReq->withServer(['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1']);
    $wildcardReq->withGet(['_action' => 'getList', 'username' => '%']);
    $app->instance('request', $wildcardReq);
    $wildcardRes = $logsCtrl->loginLogList($wildcardReq)->getData();
    assertHardening(
        count($wildcardRes['data']['items'] ?? []) === 0,
        'LIKE 查询输入 % 不应枚举出所有记录'
    );

    // 3. AdminManager.php 不再使用 where('id=' . ...) 动态拼接
    $adminManagerSource = file_get_contents(dirname(__DIR__) . '/app/admin/controller/AdminManager.php');
    assertHardening(
        is_string($adminManagerSource) && strpos($adminManagerSource, "where('id='") === false,
        'AdminManager.php 不应再存在动态 SQL 拼接 where(\'id=\' . ...)'
    );

    echo "admin_response_log_and_query_hardening_test passed\n";
} finally {
    restore_error_handler();
    if (is_dir($tmpCacheDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpCacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tmpCacheDir);
    }
}
