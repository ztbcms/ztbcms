<?php

use app\admin\controller\Checkcode;
use app\common\libs\checkcode\Checkcode as CheckcodeGenerator;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Session;
use think\Request;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/TestCacheHelper.php';

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
    configureIsolatedTestFileCache($cacheDir);

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

    $assertValidPng = function (string $png, string $context): array {
        assertCheckcodeParam(
            strncmp($png, "\x89PNG\r\n\x1a\n", 8) === 0,
            $context . '：输出应包含 PNG 文件头，实际长度 ' . strlen($png)
        );
        $imageInfo = getimagesizefromstring($png);
        assertCheckcodeParam(
            is_array($imageInfo) && ($imageInfo[2] ?? 0) === IMAGETYPE_PNG,
            $context . '：输出应为合法 PNG 图片'
        );

        return [
            'width' => (int) $imageInfo[0],
            'height' => (int) $imageInfo[1],
            'png' => $png,
        ];
    };

    $captureOutput = function (callable $callback, string $contextPrefix = '验证码') use ($assertValidPng): array {
        $baseLevel = ob_get_level();
        $png = '';
        ob_start();
        ob_start();
        try {
            $callback();
            $png = (string) ob_get_contents();
        } finally {
            while (ob_get_level() > $baseLevel) {
                ob_end_clean();
            }
        }

        return $assertValidPng($png, $contextPrefix);
    };

    $renderCheckcode = function (array $params) use ($app, $controller, $assertValidPng): array {
        $request = new Request();
        $request->withServer(['REQUEST_METHOD' => 'GET'])->withGet($params);
        $app->instance('request', $request);

        $baseLevel = ob_get_level();
        $png = '';
        ob_start();
        ob_start();
        try {
            $controller->index();
            $png = (string) ob_get_contents();
        } finally {
            while (ob_get_level() > $baseLevel) {
                ob_end_clean();
            }
        }

        $info = $assertValidPng($png, '控制器渲染验证码');
        $typeParam = $params['type'] ?? 'verify';
        $type = is_scalar($typeParam) ? strtolower((string) $typeParam) : 'verify';

        return [
            'code' => (string) Session::get(md5($type)),
            'png' => $info['png'],
            'width' => $info['width'],
            'height' => $info['height'],
        ];
    };

    $renderCode = function (array $params) use ($renderCheckcode): string {
        return $renderCheckcode($params)['code'];
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

    // 4. 默认参数：未传 width / height / font_size / code_len 时为 100 x 30，长度 4
    $defaultRes = $renderCheckcode([]);
    assertCheckcodeParam(
        $defaultRes['width'] === 100 && $defaultRes['height'] === 30 && strlen($defaultRes['code']) === 4,
        "默认参数应渲染 100x30 长度 4 的 PNG，实际为 {$defaultRes['width']}x{$defaultRes['height']} 长度 " . strlen($defaultRes['code'])
    );

    // 5. 登录页正常参数：width=130, height=50, font_size=20, code_len=4
    $loginRes = $renderCheckcode([
        'code_len' => 4,
        'font_size' => 20,
        'width' => 130,
        'height' => 50,
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $loginRes['width'] === 130 && $loginRes['height'] === 50 && strlen($loginRes['code']) === 4,
        "登录页参数应渲染 130x50 长度 4 的 PNG，实际为 {$loginRes['width']}x{$loginRes['height']}"
    );

    // 6. 合法最小/最大边界值
    $minRes = $renderCheckcode([
        'code_len' => 2,
        'font_size' => 10,
        'width' => 50,
        'height' => 20,
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $minRes['width'] === 50 && $minRes['height'] === 20 && strlen($minRes['code']) === 2,
        "最小边界应渲染 50x20 长度 2 的 PNG，实际为 {$minRes['width']}x{$minRes['height']} 长度 " . strlen($minRes['code'])
    );

    $maxRes = $renderCheckcode([
        'code_len' => 8,
        'font_size' => 60,
        'width' => 400,
        'height' => 200,
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $maxRes['width'] === 400 && $maxRes['height'] === 200 && strlen($maxRes['code']) === 8,
        "最大边界应渲染 400x200 长度 8 的 PNG，实际为 {$maxRes['width']}x{$maxRes['height']} 长度 " . strlen($maxRes['code'])
    );

    // 7. DoS 超大尺寸、超大字号与 64 位整数溢出拦截
    $dosRes = $renderCheckcode([
        'width' => 10000,
        'height' => 10000,
        'font_size' => 50000,
        'code_len' => 1000,
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $dosRes['width'] === 100 && $dosRes['height'] === 30 && strlen($dosRes['code']) === 4,
        "超大参数应回退为 100x30 长度 4，实际为 {$dosRes['width']}x{$dosRes['height']} 长度 " . strlen($dosRes['code'])
    );

    $overflowRes = $renderCheckcode([
        'width' => '9999999999999999999999999999',
        'height' => '9999999999999999999999999999',
        'font_size' => '9999999999999999999999999999',
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $overflowRes['width'] === 100 && $overflowRes['height'] === 30,
        "整数溢出字符串应回退为 100x30，实际为 {$overflowRes['width']}x{$overflowRes['height']}"
    );

    // 8. 刚越界、零值与负数回退默认尺寸
    $invalidSizes = [
        ['width' => 49, 'height' => 19, 'font_size' => 9, 'refresh' => 1],
        ['width' => 401, 'height' => 201, 'font_size' => 61, 'refresh' => 1],
        ['width' => 0, 'height' => 0, 'font_size' => 0, 'refresh' => 1],
        ['width' => -100, 'height' => -50, 'font_size' => -12, 'refresh' => 1],
    ];
    foreach ($invalidSizes as $case) {
        $res = $renderCheckcode($case);
        assertCheckcodeParam(
            $res['width'] === 100 && $res['height'] === 30,
            '越界/零值/负数尺寸应回退 100x30，输入=' . json_encode($case) . " 实际={$res['width']}x{$res['height']}"
        );
    }

    // 9. 非法类型与畸形参数不触发 Warning，且合法颜色能正常输出
    $malformedRes = $renderCheckcode([
        'width' => ['130'],
        'height' => '50px',
        'font_size' => '20.5',
        'type' => ['login'],
        'background' => ['#ffffff'],
        'font_color' => 'invalid',
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $malformedRes['width'] === 100 && $malformedRes['height'] === 30 && strlen($malformedRes['code']) === 4,
        '畸形参数应安全回退为默认 100x30 合法 PNG'
    );

    $colorRes = $renderCheckcode([
        'background' => '#112233',
        'font_color' => '#445566',
        'refresh' => 1,
    ]);
    assertCheckcodeParam(
        $colorRes['width'] === 100 && $colorRes['height'] === 30,
        '合法颜色参数应正常输出 100x30 合法 PNG'
    );

    // 10. 底层 Checkcode 类 normalize* 单元测试与纵深防御
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(true) === 100, '布尔 true 不应解析为整数 1');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(false) === 100, '布尔 false 应回退默认值');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(null) === 100, 'null 应回退默认值');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(['100']) === 100, '数组应回退默认值');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(120.5) === 100, '浮点数应回退默认值');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth('050') === 100, '前导零数字字符串应回退默认值');
    assertCheckcodeParam(CheckcodeGenerator::normalizeWidth(' 120 ') === 120, '带空白合法整数字符串应正确解析');
    assertCheckcodeParam(CheckcodeGenerator::normalizeHeight('200') === 200, 'height 最大边界 200 应被允许');
    assertCheckcodeParam(CheckcodeGenerator::normalizeFontSize('10') === 10, 'font_size 最小边界 10 应被允许');
    assertCheckcodeParam(CheckcodeGenerator::normalizeCodeLen('8') === 8, 'code_len 最大边界 8 应被允许');
    assertCheckcodeParam(CheckcodeGenerator::normalizeHexColor(' #AaBbCc ') === '#AaBbCc', '合法颜色应去空白并保留');
    assertCheckcodeParam(CheckcodeGenerator::normalizeHexColor('#12345', '#EDF7FF') === '#EDF7FF', '非法颜色长度应回退默认值');

    $generator = new CheckcodeGenerator(1);
    $generator->width = 99999;
    $generator->height = -20;
    $generator->fontsize = true;
    $generator->codelen = '12.5';
    $generator->background = ['#ffffff'];
    $generator->fontcolor = 'not-a-color';
    $generator->type = ['invalid'];

    $ref = new ReflectionClass($generator);
    $readProp = function (string $name) use ($ref, $generator) {
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($generator);
    };

    assertCheckcodeParam($readProp('width') === 100, '底层 Checkcode width 越界应回退 100');
    assertCheckcodeParam($readProp('height') === 30, '底层 Checkcode height 负数应回退 30');
    assertCheckcodeParam($readProp('fontsize') === 15, '底层 Checkcode fontsize 非法类型应回退 15');
    assertCheckcodeParam($readProp('codelen') === 4, '底层 Checkcode codelen 浮点串应回退 4');
    assertCheckcodeParam($readProp('background') === '#EDF7FF', '底层 Checkcode background 非法类型应回退 #EDF7FF');
    assertCheckcodeParam($readProp('fontcolor') === '', '底层 Checkcode fontcolor 非法值应回退空串');
    assertCheckcodeParam($readProp('type') === '', '底层 Checkcode type 非标量应回退空串');

    $directOut = $captureOutput(function () use ($generator): void {
        $generator->output(true);
    });
    assertCheckcodeParam(
        $directOut['width'] === 100 && $directOut['height'] === 30,
        '直接调用 Checkcode::output 应渲染 100x30 合法 PNG'
    );

    // 允许显式清空 background 触发随机浅色背景，并支持同一实例连续两次调用 output()
    $generator->background = '';
    assertCheckcodeParam($readProp('background') === '', '显式清空 background 应保留空字符串');
    $generator->fontcolor = '#112233';
    $firstOut = $captureOutput(function () use ($generator): void {
        $generator->output(true);
    });
    $secondOut = $captureOutput(function () use ($generator): void {
        $generator->output(true);
    });
    assertCheckcodeParam(
        $firstOut['width'] === 100 && $secondOut['width'] === 100 && $readProp('fontcolor') === '#112233',
        '同一实例连续两次 output() 不应覆写 fontcolor 且均应输出合法 PNG'
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
