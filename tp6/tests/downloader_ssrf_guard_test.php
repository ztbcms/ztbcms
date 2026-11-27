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
        'http://user:pass@93.184.216.34/test.txt',
        'http://93.184.216.34@127.0.0.1/test.txt',
        'http://127.0.0.1#@93.184.216.34/test.txt',
        'http://127.0.0.1\\@93.184.216.34/test.txt',
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

    // 7. DownloaderTool 源码必须禁用 CURLOPT_FOLLOWLOCATION 并绑定 CURLOPT_RESOLVE
    $toolSource = file_get_contents(dirname(__DIR__) . '/app/common/libs/downloader/DownloaderTool.php');
    assertSsrf(
        is_string($toolSource) && strpos($toolSource, 'CURLOPT_FOLLOWLOCATION, false') !== false,
        'DownloaderTool 必须显式禁用 CURLOPT_FOLLOWLOCATION'
    );
    assertSsrf(
        strpos($toolSource, 'CURLOPT_RESOLVE') !== false,
        'DownloaderTool 必须使用 CURLOPT_RESOLVE 锁定解析 IP 防范 DNS Rebinding'
    );

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
