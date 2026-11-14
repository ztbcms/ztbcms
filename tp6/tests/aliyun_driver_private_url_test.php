<?php

use app\common\libs\upload\AliyunDriver;
use OSS\OssClient;
use think\App;
use think\facade\Cache;
use think\facade\Config;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(dirname(__DIR__));
$app->initialize();

error_reporting(E_ALL);

set_error_handler(
    /**
     * 将 PHP 警告与提示转换为异常
     *
     * @param int $severity 错误级别
     * @param string $message 错误信息
     * @param string $file 错误文件
     * @param int $line 错误行号
     * @return bool
     */
    function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

/**
 * 断言条件成立
 *
 * @param bool $condition 条件结果
 * @param string $message 失败提示
 * @return void
 */
function assertAliyunPrivateUrl(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 模拟 OssClient，记录 signUrl 收到的有效期参数
 */
class FakeSignUrlOssClient extends OssClient
{
    /** @var array */
    public $signCalls = [];

    /**
     * 跳过真实 OSS 凭证校验
     */
    public function __construct()
    {
    }

    /**
     * 记录签发参数并返回固定格式签名 URL
     *
     * @param string $bucket
     * @param string $object
     * @param int $timeout
     * @param string $method
     * @param array|null $options
     * @return string
     */
    public function signUrl($bucket, $object, $timeout = 60, $method = self::OSS_HTTP_GET, $options = null)
    {
        $this->signCalls[] = [
            'bucket' => $bucket,
            'object' => $object,
            'timeout' => (int) $timeout,
        ];

        return sprintf('https://%s.example/%s?expires=%d', $bucket, $object, (int) $timeout);
    }
}

/**
 * 覆写 createOssClient 以注入模拟 OSS 客户端
 */
class TestableAliyunDriver extends AliyunDriver
{
    /** @var FakeSignUrlOssClient */
    public $fakeClient;

    public function __construct(array $config, FakeSignUrlOssClient $fakeClient)
    {
        $this->fakeClient = $fakeClient;
        parent::__construct($config);
    }

    /**
     * 返回模拟 OSS 客户端
     *
     * @return OssClient
     */
    protected function createOssClient(): OssClient
    {
        return $this->fakeClient;
    }
}

/**
 * 从 ThinkPHP File 缓存文件中读取实际写入的过期 TTL（秒）
 *
 * @param string $cacheKey
 * @return int
 */
function readFileCacheTtl(string $cacheKey): int
{
    $store = Cache::store('file');
    $method = new ReflectionMethod($store, 'getCacheKey');
    $method->setAccessible(true);
    $filename = $method->invoke($store, $cacheKey);
    assertAliyunPrivateUrl(is_string($filename) && is_file($filename), '缓存文件必须存在: ' . (string) $filename);

    $content = file_get_contents($filename);
    assertAliyunPrivateUrl(is_string($content) && strlen($content) >= 20, '缓存文件内容格式异常');

    return (int) substr($content, 8, 12);
}

$originalCacheConfig = Config::get('cache');
$tempCacheDir = sys_get_temp_dir() . '/ztbcms_aliyun_private_url_test_' . uniqid('', true);
if (!is_dir($tempCacheDir)) {
    mkdir($tempCacheDir, 0777, true);
}

try {
    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $tempCacheDir . DIRECTORY_SEPARATOR,
                'prefix' => '',
                'expire' => 0,
                'data_compress' => false,
            ],
        ],
    ], 'cache');
    Cache::clear();

    $baseConfig = [
        'attachment_aliyun_key_id' => 'test-ak',
        'attachment_aliyun_key_secret' => 'test-sk',
        'attachment_aliyun_endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
        'attachment_aliyun_bucket' => 'test-bucket',
        'attachment_aliyun_privilege' => '2',
        'attachment_aliyun_expire_time' => '1800',
    ];

    // 1. 不传 $expireSeconds 时应使用配置项 attachment_aliyun_expire_time (1800)，缓存 TTL 应为 1200，且命中缓存不再重复签发
    $fakeClient1 = new FakeSignUrlOssClient();
    $driver1 = new TestableAliyunDriver($baseConfig, $fakeClient1);
    $url1 = $driver1->getPrivateUrl('a.jpg');
    $url1Cached = $driver1->getPrivateUrl('a.jpg');

    assertAliyunPrivateUrl(count($fakeClient1->signCalls) === 1, '第二次调用应命中缓存，不应重复调用 signUrl');
    assertAliyunPrivateUrl($fakeClient1->signCalls[0]['timeout'] === 1800, '默认过期时间应取配置 attachment_aliyun_expire_time=1800');
    assertAliyunPrivateUrl($url1 === 'https://test-bucket.example/a.jpg?expires=1800' && $url1Cached === $url1, '返回值应为签发 URL 并与缓存一致');
    assertAliyunPrivateUrl(readFileCacheTtl('aliyun_private_url_' . md5('a.jpg')) === 1200, '1800 秒对应的缓存 TTL 应为 1200');

    // 2. 显式传入不同 expireSeconds（包含小于等于 600 秒与极小值 1 秒），验证签名时长与正整数缓存 TTL
    $cases = [
        'short_300.jpg' => ['expire' => 300, 'expectedTtl' => 150],
        'boundary_600.jpg' => ['expire' => 600, 'expectedTtl' => 300],
        'tiny_1.jpg' => ['expire' => 1, 'expectedTtl' => 1],
        'long_2400.jpg' => ['expire' => 2400, 'expectedTtl' => 1800],
    ];
    foreach ($cases as $path => $case) {
        $fakeClient = new FakeSignUrlOssClient();
        $driver = new TestableAliyunDriver($baseConfig, $fakeClient);
        $signed = $driver->getPrivateUrl($path, $case['expire']);
        $actualTtl = readFileCacheTtl('aliyun_private_url_' . md5($path));

        assertAliyunPrivateUrl(count($fakeClient->signCalls) === 1, "用例 {$path} 应签发一次");
        assertAliyunPrivateUrl($fakeClient->signCalls[0]['timeout'] === $case['expire'], "用例 {$path} signUrl 超时应为 {$case['expire']}");
        assertAliyunPrivateUrl($signed === "https://test-bucket.example/{$path}?expires={$case['expire']}", "用例 {$path} 返回 URL 异常");
        assertAliyunPrivateUrl($actualTtl === $case['expectedTtl'] && $actualTtl > 0, "用例 {$path} 缓存 TTL 应为 {$case['expectedTtl']}，实际为 {$actualTtl}");
    }

    // 3. 配置 attachment_aliyun_expire_time 为 0、负数或未设置时回退 3600 秒
    foreach (['0', '-100', null] as $idx => $invalidExpire) {
        $cfg = $baseConfig;
        if ($invalidExpire === null) {
            unset($cfg['attachment_aliyun_expire_time']);
        } else {
            $cfg['attachment_aliyun_expire_time'] = $invalidExpire;
        }
        $path = "fallback_{$idx}.jpg";
        $fakeClient = new FakeSignUrlOssClient();
        $driver = new TestableAliyunDriver($cfg, $fakeClient);
        $driver->getPrivateUrl($path);

        assertAliyunPrivateUrl($fakeClient->signCalls[0]['timeout'] === 3600, "无效配置 {$path} 应回退到 3600 秒");
        assertAliyunPrivateUrl(readFileCacheTtl('aliyun_private_url_' . md5($path)) === 3000, "回退 3600 秒时缓存 TTL 应为 3000");
    }

    echo "aliyun_driver_private_url_test passed\n";
} finally {
    if (is_array($originalCacheConfig)) {
        Config::set($originalCacheConfig, 'cache');
    }
    restore_error_handler();
    if (is_dir($tempCacheDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempCacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($tempCacheDir);
    }
}
