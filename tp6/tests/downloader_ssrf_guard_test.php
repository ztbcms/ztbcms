<?php

use app\common\libs\downloader\DownloaderTool;
use app\common\libs\downloader\SsrfGuard;
use app\common\model\upload\AttachmentModel;
use app\common\service\downloader\DownloaderService;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

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

function assertSsrf(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

$tempCacheDir = sys_get_temp_dir() . '/ztbcms_ssrf_guard_cache_' . uniqid('', true);
mkdir($tempCacheDir, 0777, true);
$createdTargets = [];

try {
    Config::set([
        'default' => 'ssrf_guard_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'ssrf_guard_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    configureIsolatedTestFileCache($tempCacheDir);
    Cache::clear();

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'downloader_domain',
        'value' => 'https://cdn.example.com/',
    ]);

    Db::execute('CREATE TABLE downloader (
        downloader_id INTEGER PRIMARY KEY AUTOINCREMENT,
        downloader_url TEXT NOT NULL,
        downloader_url_hash VARCHAR(255) DEFAULT NULL,
        downloader_state INTEGER NOT NULL DEFAULT 0,
        downloader_result VARCHAR(255) NOT NULL DEFAULT "",
        downloader_duration INTEGER NOT NULL DEFAULT 0,
        process_start_time INTEGER NOT NULL DEFAULT 0,
        process_end_time INTEGER NOT NULL DEFAULT 0,
        file_name VARCHAR(255) NOT NULL DEFAULT "",
        file_path VARCHAR(255) NOT NULL DEFAULT "",
        file_url VARCHAR(255) NOT NULL DEFAULT "",
        file_thumb VARCHAR(256) NOT NULL DEFAULT "",
        file_hash VARCHAR(255) NOT NULL DEFAULT "",
        file_size INTEGER NOT NULL DEFAULT 0,
        file_ext VARCHAR(32) NOT NULL DEFAULT "",
        downloader_implement_num INTEGER NOT NULL DEFAULT 0,
        downloader_next_implement_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER DEFAULT 0,
        update_time INTEGER DEFAULT 0,
        delete_time INTEGER DEFAULT 0
    )');

    // 1. 直接 IP / 保留网段 / 云元数据 / 特殊主机名拦截
    $blockedUrls = [
        'http://127.0.0.1/test.txt',
        'http://127.0.1.1/test.txt',
        'http://0.0.0.0/test.txt',
        'http://10.0.0.1/test.txt',
        'http://172.16.0.1/test.txt',
        'http://172.31.255.254/test.txt',
        'http://192.168.1.1/test.txt',
        'http://169.254.169.254/latest/meta-data/iam/security-credentials/',
        'http://100.100.100.200/latest/meta-data/',
        'http://[::1]/test.txt',
        'http://[::ffff:127.0.0.1]/test.txt',
        'http://[::ffff:169.254.169.254]/latest/meta-data/',
        'http://[fd00:ec2::254]/latest/meta-data/',
        'http://[fe80::1]/test.txt',
        'http://localhost/test.txt',
        'http://sub.localhost./test.txt',
        'http://metadata.google.internal/computeMetadata/v1/',
        'http://service.internal/secret.txt',
        'http://93.184.216.34:8080/test.txt',
        'https://93.184.216.34:8443/test.txt',
        'http://127.0.0.1\\@93.184.216.34/test.txt',
        'https://example.com./test.txt',
        'http://user:pass@93.184.216.34/test.txt',
        'file:///etc/passwd',
        'gopher://127.0.0.1:6379/_INFO',
        'ftp://93.184.216.34/test.txt',
        'httpx://93.184.216.34/test.txt',
    ];

    foreach ($blockedUrls as $badUrl) {
        $guardRes = SsrfGuard::validateUrl($badUrl);
        assertSsrf(($guardRes['status'] ?? true) === false, "SsrfGuard 应拦截危险 URL: {$badUrl}");

        $taskRes = DownloaderService::createDownloaderTask($badUrl);
        assertSsrf(($taskRes['status'] ?? true) === false, "createDownloaderTask 应拦截危险 URL: {$badUrl}");
    }
    assertSsrf(Db::name('downloader')->count() === 0, '被拦截的 URL 不得入库');

    // 2. DNS 解析到内网或混合公私网 IP（含重绑定风险）拦截
    $privateDnsRes = SsrfGuard::validateUrl('https://evil.example.test/a.txt', function () {
        return ['10.20.30.40'];
    });
    assertSsrf(($privateDnsRes['status'] ?? true) === false, 'DNS 解析为私有 IP 应拦截');

    $mixedDnsRes = SsrfGuard::validateUrl('https://dual.example.test/a.txt', function () {
        return ['93.184.216.34', '127.0.0.1'];
    });
    assertSsrf(($mixedDnsRes['status'] ?? true) === false, 'DNS 同时返回公网与内网 IP 应拦截');

    // 3. 合法公网 IP 与域名（80/443）放行
    $validIpRes = SsrfGuard::validateUrl('https://93.184.216.34/file.png');
    assertSsrf(($validIpRes['status'] ?? false) === true, '合法公网 IP 应放行');

    $validDomainRes = SsrfGuard::validateUrl('http://public.example.test:80/file.png', function () {
        return ['93.184.216.34'];
    });
    assertSsrf(($validDomainRes['status'] ?? false) === true, '合法公网域名应放行');
    assertSsrf(($validDomainRes['data']['primary_ip'] ?? '') === '93.184.216.34', '应返回解析出的主 IP');

    // 4. 302 重定向到内网地址时必须拦截（禁用自动跳转且二次校验 Location）
    $redirectToInternalRes = DownloaderTool::doDownloadFile(
        'https://public.example.test/start.txt',
        'redirect_blocked.txt',
        'txt',
        AttachmentModel::MODULE_FILE,
        function (string $host): array {
            if ($host === 'public.example.test') {
                return ['93.184.216.34'];
            }
            return ['127.0.0.1'];
        },
        function (string $url): array {
            return createReturn(true, [
                'http_code' => 302,
                'location'  => 'http://169.254.169.254/latest/meta-data/',
                'body'      => 'secret-token',
            ]);
        }
    );
    assertSsrf(($redirectToInternalRes['status'] ?? true) === false, '302 重定向到元数据 IP 必须被拦截');

    // 5. 302 重定向到另一个公网地址应安全跟随并下载
    $publicRedirectUrl = 'https://cdn-redirect.example.test/final.txt';
    $redirectBucket = substr(md5('https://public.example.test/redirect.txt'), 0, 2);
    $createdTargets[] = $app->getRootPath() . 'public/downloader/' . $redirectBucket . '/redirect_ok.txt';

    $redirectToPublicRes = DownloaderTool::doDownloadFile(
        'https://public.example.test/redirect.txt',
        'redirect_ok.txt',
        'txt',
        AttachmentModel::MODULE_FILE,
        function (): array {
            return ['93.184.216.34'];
        },
        function (string $url) use ($publicRedirectUrl): array {
            if ($url === 'https://public.example.test/redirect.txt') {
                return createReturn(true, [
                    'http_code' => 302,
                    'location'  => $publicRedirectUrl,
                    'body'      => '',
                ]);
            }
            return createReturn(true, [
                'http_code' => 200,
                'location'  => '',
                'body'      => 'safe-public-body',
            ]);
        }
    );
    assertSsrf(($redirectToPublicRes['status'] ?? false) === true, '公网 302 重定向到公网地址应允许下载');
    assertSsrf(
        file_get_contents($createdTargets[0]) === 'safe-public-body',
        '重定向后文件内容应正确写入'
    );

    // 6. 路径穿越文件名拦截
    $traversalRes = DownloaderTool::doDownloadFile(
        'https://public.example.test/a.txt',
        '../evil.php',
        'txt',
        AttachmentModel::MODULE_FILE
    );
    assertSsrf(($traversalRes['status'] ?? true) === false, '包含路径穿越的文件名应被拦截');

    // 7. 重定向相对 URI（含 ?query、#fragment、../ 路径段）解析与规范化 URL 校验
    assertSsrf(
        SsrfGuard::resolveRedirectUrl('https://example.com/files/report.pdf', '?version=2')
        === 'https://example.com/files/report.pdf?version=2',
        '仅含查询参数的重定向应保留原始路径'
    );
    assertSsrf(
        SsrfGuard::resolveRedirectUrl('https://example.com/a/b/c.pdf', '../d.pdf?v=1')
        === 'https://example.com/a/d.pdf?v=1',
        '含 ../ 的相对路径重定向应按 RFC 3986 规范化'
    );
    $upperHostRes = SsrfGuard::validateUrl('HTTPS://PUBLIC.EXAMPLE.TEST/file.png', function () {
        return ['93.184.216.34'];
    });
    assertSsrf(
        ($upperHostRes['data']['url'] ?? '') === 'https://public.example.test/file.png',
        'validateUrl 应返回规范化后的 URL 供 cURL 与 CURLOPT_RESOLVE 共用'
    );
    $dotSegUrlRes = SsrfGuard::validateUrl('https://public.example.test/files/archive/..', function () {
        return ['93.184.216.34'];
    });
    assertSsrf(
        ($dotSegUrlRes['data']['url'] ?? '') === 'https://public.example.test/files/',
        'validateUrl 应规范化请求路径中的 .. 段与 libcurl 保持一致'
    );
    assertSsrf(
        SsrfGuard::resolveRedirectUrl($dotSegUrlRes['data']['url'], 'report.pdf')
        === 'https://public.example.test/files/report.pdf',
        '基于规范化后的基准路径解析相对重定向应命中 /files/report.pdf'
    );

    // 8. 真实 cURL 传输层回归：验证禁用继承环境代理且 CURLOPT_RESOLVE 绑定生效
    $toolSource = file_get_contents(dirname(__DIR__) . '/app/common/libs/downloader/DownloaderTool.php');
    assertSsrf(
        is_string($toolSource) && strpos($toolSource, 'CURLOPT_FOLLOWLOCATION, false') !== false,
        'DownloaderTool 必须显式禁用 CURLOPT_FOLLOWLOCATION'
    );
    assertSsrf(
        strpos($toolSource, 'CURLOPT_RESOLVE') !== false
        && strpos($toolSource, "CURLOPT_PROXY, ''") !== false,
        'DownloaderTool 必须使用 CURLOPT_RESOLVE 并显式禁用环境代理'
    );

    $httpServerPhp = <<<'PHP'
$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (!$server) { exit(2); }
$name = stream_socket_get_name($server, false);
echo $name . "\n";
fflush(STDOUT);
$conn = @stream_socket_accept($server, 5);
if ($conn) {
    fread($conn, 4096);
    $body = "real-curl-ok";
    fwrite($conn, "HTTP/1.1 200 OK\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
    fclose($conn);
}
fclose($server);
PHP;
    $proc = proc_open(
        escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($httpServerPhp),
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );
    assertSsrf(is_resource($proc), '未能启动本地测试 HTTP 服务');
    $boundAddr = trim((string) fgets($pipes[1]));
    $portPos = strrpos($boundAddr, ':');
    assertSsrf($portPos !== false, '未能读取本地测试服务端口: ' . $boundAddr);
    $listenPort = (int) substr($boundAddr, $portPos + 1);

    $prevHttpProxy = getenv('http_proxy');
    putenv('http_proxy=http://127.0.0.1:9');
    try {
        $execCurlMethod = new ReflectionMethod(DownloaderTool::class, 'executeCurlRequest');
        $execCurlMethod->setAccessible(true);
        $realCurlRes = $execCurlMethod->invoke(
            null,
            "http://curl-pin.example.test:{$listenPort}/ok.txt",
            [
                'url'        => "http://curl-pin.example.test:{$listenPort}/ok.txt",
                'host'       => 'curl-pin.example.test',
                'port'       => $listenPort,
                'primary_ip' => '127.0.0.1',
            ]
        );
        assertSsrf(($realCurlRes['status'] ?? false) === true, '真实 cURL 应忽略 http_proxy 并通过 CURLOPT_RESOLVE 请求成功: ' . ($realCurlRes['msg'] ?? ''));
        assertSsrf(($realCurlRes['data']['body'] ?? '') === 'real-curl-ok', '真实 cURL 返回体不一致');
    } finally {
        if ($prevHttpProxy === false) {
            putenv('http_proxy');
        } else {
            putenv('http_proxy=' . $prevHttpProxy);
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
    }

    echo "downloader_ssrf_guard_test passed\n";
} finally {
    restore_error_handler();
    foreach ($createdTargets as $target) {
        if (is_file($target)) {
            unlink($target);
        }
        $dir = dirname($target);
        if (is_dir($dir) && empty(array_diff(scandir($dir), ['.', '..']))) {
            rmdir($dir);
        }
    }
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
