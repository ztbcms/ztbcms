<?php

use app\admin\service\ConfigFieldService;
use think\App;
use think\facade\Config;
use think\facade\Db;

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
function assertConfigFieldService(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_config_field_test_cache_' . getmypid() . '_' . uniqid('', true) . '/';

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
        'default' => 'config_field_service_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'config_field_service_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE config_field (
        fid INTEGER PRIMARY KEY AUTOINCREMENT,
        fieldname VARCHAR(64) NOT NULL,
        type VARCHAR(32) NOT NULL,
        setting TEXT NOT NULL,
        createtime INTEGER NOT NULL DEFAULT 0
    )');

    Db::execute('CREATE TABLE config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        varname VARCHAR(64) NOT NULL,
        info VARCHAR(128) NOT NULL DEFAULT "",
        groupid INTEGER NOT NULL DEFAULT 1,
        value TEXT NOT NULL DEFAULT ""
    )');

    // 预置一条系统内置配置（仅存在于 config 表）
    Db::name('config')->insert([
        'varname' => 'sitename',
        'info' => '站点名称',
        'groupid' => 1,
        'value' => 'ZTBCMS',
    ]);

    // 用于模拟 config 表写入失败的触发器
    Db::execute("CREATE TRIGGER prevent_trigger_rollback_key
        BEFORE INSERT ON config
        WHEN NEW.varname = 'trigger_rollback_key'
        BEGIN
            SELECT RAISE(IGNORE);
        END");

    $service = new ConfigFieldService();

    $buildPayload = function (string $fieldname, string $title, ?int $fid = null, string $type = 'input'): array {
        $payload = [
            'fieldname' => $fieldname,
            'type' => $type,
            'setting' => [
                'title' => $title,
                'tips' => '提示',
                'option' => "选项A|1\n选项B|2",
            ],
        ];
        if ($fid !== null) {
            $payload['fid'] = $fid;
        }
        return $payload;
    };

    // 1. 新增扩展配置成功
    $addRes1 = $service->addOrUpdateConfigField($buildPayload('ext_one', '扩展一'));
    assertConfigFieldService(($addRes1['status'] ?? false) === true, '新增 ext_one 应返回成功');
    $extOne = Db::name('config_field')->where('fieldname', 'ext_one')->findOrEmpty();
    assertConfigFieldService(!empty($extOne), 'config_field 应已写入 ext_one');
    assertConfigFieldService(!empty(Db::name('config')->where('varname', 'ext_one')->findOrEmpty()), 'config 应同步写入 ext_one');

    // 2. 新增重名校验（分别与 config_field 和 config 冲突）
    $dupFieldRes = $service->addOrUpdateConfigField($buildPayload('ext_one', '重复字段'));
    assertConfigFieldService(
        ($dupFieldRes['status'] ?? true) === false && ($dupFieldRes['msg'] ?? '') === '键名已被占用',
        '新增与已有扩展字段同名的配置应被拦截'
    );
    $dupSysRes = $service->addOrUpdateConfigField($buildPayload('sitename', '重复系统配置'));
    assertConfigFieldService(
        ($dupSysRes['status'] ?? true) === false && ($dupSysRes['msg'] ?? '') === '键名已被占用',
        '新增与系统内置配置同名的配置应被拦截'
    );

    // 3. 编辑不改名更新成功
    $updateSameName = $service->addOrUpdateConfigField($buildPayload('ext_one', '扩展一修改后', (int) $extOne['fid'], 'textarea'));
    assertConfigFieldService(($updateSameName['status'] ?? false) === true, '编辑不改名时应允许更新成功');
    $updatedExtOne = Db::name('config_field')->where('fid', $extOne['fid'])->findOrEmpty();
    assertConfigFieldService(($updatedExtOne['type'] ?? '') === 'textarea', '编辑不改名应更新 type 字段');

    // 4. 新增 ext_two 后尝试改名为已有 config_field 键名或 config 键名
    $addRes2 = $service->addOrUpdateConfigField($buildPayload('ext_two', '扩展二'));
    assertConfigFieldService(($addRes2['status'] ?? false) === true, '新增 ext_two 应返回成功');
    $extTwo = Db::name('config_field')->where('fieldname', 'ext_two')->findOrEmpty();

    $renameDupField = $service->addOrUpdateConfigField($buildPayload('ext_one', '改名冲突一', (int) $extTwo['fid']));
    assertConfigFieldService(
        ($renameDupField['status'] ?? true) === false && ($renameDupField['msg'] ?? '') === '键名已被占用',
        '编辑改名为已有扩展字段键名时应返回「键名已被占用」'
    );

    $renameDupSys = $service->addOrUpdateConfigField($buildPayload('sitename', '改名冲突二', (int) $extTwo['fid']));
    assertConfigFieldService(
        ($renameDupSys['status'] ?? true) === false && ($renameDupSys['msg'] ?? '') === '键名已被占用',
        '编辑改名为已有系统配置键名时应返回「键名已被占用」'
    );

    $extTwoAfterConflict = Db::name('config_field')->where('fid', $extTwo['fid'])->findOrEmpty();
    assertConfigFieldService(($extTwoAfterConflict['fieldname'] ?? '') === 'ext_two', '改名冲突拦截后原记录 fieldname 不应被修改');

    // 5. 编辑改名为未占用的键名应成功并同步更新 config 表
    $renameSuccess = $service->addOrUpdateConfigField($buildPayload('ext_two_renamed', '扩展二改名', (int) $extTwo['fid']));
    assertConfigFieldService(($renameSuccess['status'] ?? false) === true, '改名为未占用键名应成功');
    assertConfigFieldService(
        empty(Db::name('config')->where('varname', 'ext_two')->findOrEmpty())
        && !empty(Db::name('config')->where('varname', 'ext_two_renamed')->findOrEmpty()),
        '改名成功后 config.varname 应同步更新'
    );

    // 6. 新增写入 config 表失败时应回滚并返回失败，不得再 commit 或保留残留记录
    $rollbackRes = $service->addOrUpdateConfigField($buildPayload('trigger_rollback_key', '触发回滚'));
    assertConfigFieldService(
        ($rollbackRes['status'] ?? true) === false && ($rollbackRes['msg'] ?? '') === '操作失败',
        '新增失败回滚后应返回 status=false 与「操作失败」'
    );
    assertConfigFieldService(
        empty(Db::name('config_field')->where('fieldname', 'trigger_rollback_key')->findOrEmpty()),
        '新增失败回滚后 config_field 不应残留未提交或已提交的记录'
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

echo "config_field_service_test passed\n";
