<?php

use app\common\command\cron\Exec;
use app\common\controller\cron\Dashboard;
use app\common\cronscript\CronScript;
use app\common\cronscript\DeleteCronLogScript;
use app\common\model\cron\CronModel;
use app\common\service\cron\CronService;
use think\App;
use think\console\Input;
use think\console\Output;
use think\facade\Config;
use think\facade\Db;
use think\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

class CronArbitraryGadgetTarget
{
    public static bool $instantiated = false;

    public function __construct()
    {
        self::$instantiated = true;
    }

    public function run($cronId)
    {
        self::$instantiated = true;
        return ['status' => true];
    }
}

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

function assertCronWhitelist(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('断言失败: ' . $message);
    }
}

try {
    Config::set([
        'default' => 'cron_whitelist_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'cron_whitelist_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE cron (
        cron_id INTEGER PRIMARY KEY AUTOINCREMENT,
        type INTEGER DEFAULT 0,
        subject VARCHAR(50) NOT NULL DEFAULT "",
        loop_type VARCHAR(10) NOT NULL DEFAULT "",
        loop_daytime VARCHAR(50) NOT NULL DEFAULT "",
        cron_file VARCHAR(512) NOT NULL DEFAULT "",
        isopen INTEGER NOT NULL DEFAULT 1,
        created_time INTEGER NOT NULL DEFAULT 0,
        modified_time INTEGER NOT NULL DEFAULT 0,
        next_time INTEGER NOT NULL DEFAULT 0,
        data TEXT
    )');
    Db::execute('CREATE TABLE cron_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cron_id INTEGER NOT NULL DEFAULT 0,
        start_time INTEGER NOT NULL DEFAULT 0,
        end_time INTEGER NOT NULL DEFAULT 0,
        result INTEGER NOT NULL DEFAULT 0,
        use_time INTEGER NOT NULL DEFAULT 0,
        result_msg TEXT
    )');

    // 1. 白名单扫描验证：包含合法脚本，不含 CronScript 基类
    $allowed = CronService::getAllowedCronFiles();
    assertCronWhitelist(
        in_array(DeleteCronLogScript::class, $allowed, true),
        '白名单应包含 DeleteCronLogScript'
    );
    assertCronWhitelist(
        !in_array(CronScript::class, $allowed, true),
        '白名单不应包含抽象基类 CronScript'
    );

    // 2. Dashboard::addOrEditCron 拒绝任意非白名单类名
    $dashboardRef = new ReflectionClass(Dashboard::class);
    /** @var Dashboard $dashboard */
    $dashboard = $dashboardRef->newInstanceWithoutConstructor();

    $badReq = new Request();
    $badReq->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1']);
    $badReq->setMethod('POST');
    $badReq->withPost([
        '_action' => 'submitCron',
        'cron_id' => '',
        'form' => [
            'type' => 0,
            'subject' => '恶意任务',
            'loop_type' => 'hour',
            'cron_file' => CronArbitraryGadgetTarget::class,
            'isopen' => 1,
        ],
        'loop_data' => ['hour_minute' => 10],
    ]);
    $app->instance('request', $badReq);

    $badSaveRes = $dashboard->addOrEditCron($badReq);
    assertCronWhitelist(($badSaveRes['status'] ?? true) === false, 'addOrEditCron 必须拒绝非白名单类');
    assertCronWhitelist(Db::name('cron')->count() === 0, '非法 cron_file 不得写入数据库');

    // 即使传入抽象基类或任意 stdClass 也应拒绝
    $stdReq = new Request();
    $stdReq->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1']);
    $stdReq->setMethod('POST');
    $stdReq->withPost([
        '_action' => 'submitCron',
        'cron_id' => '',
        'form' => [
            'type' => 0,
            'subject' => '标准类测试',
            'loop_type' => 'hour',
            'cron_file' => stdClass::class,
            'isopen' => 1,
        ],
        'loop_data' => ['hour_minute' => 10],
    ]);
    $app->instance('request', $stdReq);
    $stdSaveRes = $dashboard->addOrEditCron($stdReq);
    assertCronWhitelist(($stdSaveRes['status'] ?? true) === false, 'addOrEditCron 必须拒绝 stdClass');

    // 3. Dashboard::addOrEditCron 允许合法白名单类（含前导反斜杠规范化）
    $goodReq = new Request();
    $goodReq->withServer(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1']);
    $goodReq->setMethod('POST');
    $goodReq->withPost([
        '_action' => 'submitCron',
        'cron_id' => '',
        'form' => [
            'type' => 0,
            'subject' => '清理计划任务日志',
            'loop_type' => 'hour',
            'cron_file' => '\\' . DeleteCronLogScript::class,
            'isopen' => 1,
        ],
        'loop_data' => ['hour_minute' => 10],
    ]);
    $app->instance('request', $goodReq);
    $goodSaveRes = $dashboard->addOrEditCron($goodReq);
    assertCronWhitelist(($goodSaveRes['status'] ?? false) === true, '合法白名单类应允许保存');
    assertCronWhitelist(
        Db::name('cron')->where('subject', '清理计划任务日志')->value('cron_file') === DeleteCronLogScript::class,
        '保存后应规范化去除前导反斜杠'
    );

    // 4. CronModel::runAction 在执行篡改库数据时必须拦截且不得实例化目标类
    CronArbitraryGadgetTarget::$instantiated = false;
    $poisonedId = Db::name('cron')->insertGetId([
        'type' => 0,
        'subject' => '库内篡改任务',
        'loop_type' => 'hour',
        'loop_daytime' => '0-0-10',
        'cron_file' => CronArbitraryGadgetTarget::class,
        'isopen' => 1,
        'created_time' => time(),
        'modified_time' => 0,
        'next_time' => time() + 60,
        'data' => '',
    ]);
    $poisonedCron = CronModel::where('cron_id', $poisonedId)->find();
    $runRes = $poisonedCron->runAction();
    assertCronWhitelist(($runRes['status'] ?? true) === false, 'CronModel::runAction 必须拒绝非 CronScript 子类');
    assertCronWhitelist(CronArbitraryGadgetTarget::$instantiated === false, 'CronModel::runAction 不得实例化非 CronScript 类');

    // 5. cron:exec 命令同样不得实例化非 CronScript 类
    $execCmd = new Exec();
    $execCmd->setApp($app);
    $output = new Output('buffer');
    $execCmd->run(new Input(['--class=' . CronArbitraryGadgetTarget::class]), $output);
    $execOut = $output->fetch();
    assertCronWhitelist(CronArbitraryGadgetTarget::$instantiated === false, 'cron:exec 不得实例化非 CronScript 类');
    assertCronWhitelist(strpos($execOut, '非法') !== false, 'cron:exec 应提示非法的计划任务类: ' . $execOut);

    echo "cron_script_whitelist_test passed\n";
} finally {
    restore_error_handler();
}
