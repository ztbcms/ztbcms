<?php

use app\admin\controller\Checkcode;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\Request;

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
function assertCheckcodeParam(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_checkcode_test_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    Config::set([
        'default' => 'file',
        'stores' => [
            'file' => [
                'type' => 'File',
                'path' => $cacheDir,
                'prefix' => '',
                'expire' => 0,
                'tag_prefix' => 'tag:',
                'serialize' => [],
            ],
        ],
    ], 'cache');

    Config::set([
        'default' => 'admin_checkcode_param_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_checkcode_param_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL
    )');
    Db::name('config')->insert([
        'varname' => 'checkcode_type',
        'info' => '验证码类型',
        'groupid' => 1,
        'value' => '1',
    ]);

    /** @var Checkcode $controller */
    $controller = (new ReflectionClass(Checkcode::class))->newInstanceWithoutConstructor();

    $renderCode = function (array $params) use ($app, $controller): string {
        $request = new Request();
        $request->withServer(['REQUEST_METHOD' => 'GET'])->withGet($params);
        $app->instance('request', $request);

        ob_start();
        ob_start();
        try {
            $controller->index();
        } finally {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $type = strtolower((string) ($params['type'] ?? 'verify'));
        return (string) Session::get(md5($type));
    };

    // 1. 传入合法 code_len=6
    $codeLen6 = $renderCode(['type' => 'login', 'code_len' => 6, 'refresh' => 1]);
    assertCheckcodeParam(
        strlen($codeLen6) === 6,
        '传入 code_len=6 时验证码长度应为 6，实际为 ' . strlen($codeLen6)
    );

    // 2. 传入越界 code_len=9 和 code_len=1 时应回退为 4
    $codeLen9 = $renderCode(['type' => 'login', 'code_len' => 9, 'refresh' => 1]);
    assertCheckcodeParam(
        strlen($codeLen9) === 4,
        '传入越界 code_len=9 时验证码长度应回退为 4，实际为 ' . strlen($codeLen9)
    );

    $codeLen1 = $renderCode(['type' => 'login', 'code_len' => 1, 'refresh' => 1]);
    assertCheckcodeParam(
        strlen($codeLen1) === 4,
        '传入越界 code_len=1 时验证码长度应回退为 4，实际为 ' . strlen($codeLen1)
    );

    // 3. 仅传入数字型 type=6（未传 code_len），不应被 type 误当成长度
    $codeType6 = $renderCode(['type' => '6', 'refresh' => 1]);
    assertCheckcodeParam(
        strlen($codeType6) === 4,
        '仅传入 type=6 且未传 code_len 时验证码长度应保持默认 4，实际为 ' . strlen($codeType6)
    );
} finally {
    restore_error_handler();
    if (is_dir($cacheDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($cacheDir);
    }
}

echo "admin_checkcode_param_test passed\n";
