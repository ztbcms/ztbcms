<?php

use app\common\controller\upload\Api as UploadApi;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\file\UploadedFile;
use think\Request;

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

function assertSvgBlocked(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

$tmpDir = sys_get_temp_dir() . '/ztbcms_svg_block_test_' . uniqid('', true);
$tmpCacheDir = $tmpDir . '/cache';
mkdir($tmpCacheDir, 0777, true);

try {
    Config::set([
        'default' => 'upload_svg_block_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'upload_svg_block_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    configureIsolatedTestFileCache($tmpCacheDir);
    Cache::clear();

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        value TEXT NOT NULL
    )');
    Db::name('config')->insertAll([
        ['varname' => 'attachment_driver', 'value' => 'Local'],
        ['varname' => 'attachment_local_domain', 'value' => 'http://127.0.0.1'],
        ['varname' => 'uploadmaxsize', 'value' => '2048'],
        ['varname' => 'uploadallowext', 'value' => 'jpg|png|svg|svgz'],
        ['varname' => 'qtuploadmaxsize', 'value' => '2048'],
        ['varname' => 'qtuploadallowext', 'value' => 'jpg|png|svg|svgz'],
    ]);

    // 1. IMAGE_EXTENSIONS 不再包含 svg/svgz，且类型推断不将 svg 识别为图片
    assertSvgBlocked(!in_array('svg', UploadService::IMAGE_EXTENSIONS, true), 'IMAGE_EXTENSIONS 不应包含 svg');
    assertSvgBlocked(!in_array('svgz', UploadService::IMAGE_EXTENSIONS, true), 'IMAGE_EXTENSIONS 不应包含 svgz');
    assertSvgBlocked(UploadService::inferModuleByExt('svg') !== AttachmentModel::MODULE_IMAGE, 'svg 不应被推断为 image 模块');
    assertSvgBlocked(UploadService::isBlockedExtension('SVG') === true, '大写 SVG 应被判定为禁止扩展名');

    // 2. getUploadConfig 即使数据库遗留 svg 配置也必须过滤
    $uploadConfig = UploadService::getUploadConfig();
    assertSvgBlocked(!in_array('svg', $uploadConfig['allow_ext'], true), 'getUploadConfig 应过滤 svg');
    assertSvgBlocked(!in_array('svgz', $uploadConfig['allow_ext'], true), 'getUploadConfig 应过滤 svgz');
    assertSvgBlocked(in_array('png', $uploadConfig['allow_ext'], true), 'getUploadConfig 应保留安全图片扩展名 png');

    // 3. 即使配置中包含 svg，uploadAny / uploadDirectOnly 也必须拒绝上传 .svg 文件
    $svgFilePath = $tmpDir . '/xss.svg';
    file_put_contents($svgFilePath, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>');

    $uploadedSvg = new UploadedFile($svgFilePath, 'xss.svg', 'image/svg+xml', UPLOAD_ERR_OK, true);
    $request = new class($uploadedSvg) extends Request {
        private UploadedFile $mockFile;

        public function __construct(UploadedFile $mockFile)
        {
            parent::__construct();
            $this->mockFile = $mockFile;
        }

        public function file(string $name = '')
        {
            if ($name === '') {
                return ['file' => $this->mockFile];
            }
            return $name === 'file' ? $this->mockFile : null;
        }
    };
    $request->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1']);
    $app->instance('request', $request);

    $service = new UploadService('Local');
    $resAny = $service->uploadAny(0, 1, 'admin');
    assertSvgBlocked($resAny === false, 'uploadAny 必须拒绝上传 .svg 文件');
    assertSvgBlocked(strpos($service->getError(), '不支持') !== false, 'uploadAny 应返回不支持提示');

    $resDirect = $service->uploadDirectOnly(1, 'user', 'image');
    assertSvgBlocked($resDirect === false, 'uploadDirectOnly 必须拒绝上传 .svg 文件');

    // 4. 直传凭证与保存直传记录接口同样拒绝 svg / svgz
    $api = new UploadApi($app);
    $credReq = new Request();
    $credReq->withGet(['filename' => 'evil.svg']);
    $app->instance('request', $credReq);
    $credData = $api->getDirectUploadCredential()->getData();
    assertSvgBlocked(($credData['status'] ?? true) === false, 'getDirectUploadCredential 必须拒绝 .svg');

    $saveReq = new Request();
    $saveReq->withPost([
        'filepath' => 'image/2026/11/28/evil.svg',
        'filename' => 'evil.svg',
        'filesize' => 128,
    ]);
    $app->instance('request', $saveReq);
    $saveData = $api->saveDirectUploadRecord()->getData();
    assertSvgBlocked(($saveData['status'] ?? true) === false, 'saveDirectUploadRecord 必须拒绝 .svg');

    // 5. 阿里云 OSS STS 会话策略限制到单一服务端核准对象键，且驱动层拒绝 svg 对象键生成
    $aliyunDriver = new \app\common\libs\upload\AliyunDriver([
        'attachment_aliyun_key_id' => 'test-ak',
        'attachment_aliyun_key_secret' => 'test-sk',
        'attachment_aliyun_endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
        'attachment_aliyun_bucket' => 'demo-bucket',
        'attachment_aliyun_sts_role_arn' => 'acs:ram::123456:role/upload',
    ]);
    $policyMethod = new ReflectionMethod(\app\common\libs\upload\AliyunDriver::class, 'buildUploadSessionPolicy');
    $policyMethod->setAccessible(true);
    $policyJson = $policyMethod->invoke($aliyunDriver, 'image/2026/11/28/demo.png');
    $policyArr = json_decode((string) $policyJson, true);
    assertSvgBlocked(is_array($policyArr), 'Aliyun STS 会话策略应为合法 JSON');
    assertSvgBlocked(
        ($policyArr['Statement'][0]['Action'] ?? []) === ['oss:PutObject'],
        'Aliyun STS 会话策略仅应允许 oss:PutObject'
    );
    assertSvgBlocked(
        ($policyArr['Statement'][0]['Resource'] ?? []) === ['acs:oss:*:*:demo-bucket/image/2026/11/28/demo.png'],
        'Aliyun STS 会话策略必须精确限制到单个服务端核准对象键'
    );

    $buildKeyMethod = new ReflectionMethod(\app\common\libs\upload\UploadDriver::class, 'buildObjectKey');
    $buildKeyMethod->setAccessible(true);
    $svgKeyRejected = false;
    try {
        $buildKeyMethod->invoke($aliyunDriver, 'image', 'xss.svg', 'svg');
    } catch (\Throwable $e) {
        $svgKeyRejected = true;
    }
    assertSvgBlocked($svgKeyRejected, 'UploadDriver::buildObjectKey 必须拒绝生成 svg 对象键');

    echo "upload_svg_block_test passed\n";
} finally {
    restore_error_handler();
    if (is_dir($tmpDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tmpDir);
    }
}
