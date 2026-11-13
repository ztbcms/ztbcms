<?php

use app\admin\controller\AdminMessage;
use app\admin\service\AdminUserService;
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
function assertAdminMessageReadAll(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * 重置测试管理员登录态信息
 *
 * @param array $userInfo 管理员信息
 * @return void
 */
function setTestReadAllAdminUserInfo(array $userInfo): void
{
    $service = AdminUserService::getInstance();
    $ref = new ReflectionObject($service);
    if ($ref->hasProperty('userInfo')) {
        $prop = $ref->getProperty('userInfo');
        $prop->setAccessible(true);
        if ($prop->isStatic()) {
            $prop->setValue(null, null);
        } else {
            $prop->setValue($service, null);
        }
    }
    Session::set(AdminUserService::KEY_USER_ID, $userInfo['id'] ?? null);
    Session::set(AdminUserService::KEY_USER_INFO, $userInfo ?: null);
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_admin_msg_readall_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 验证 readMsgAll() 方法源码不再包含无用的 $where 变量
    $controllerRef = new ReflectionClass(AdminMessage::class);
    $methodRef = $controllerRef->getMethod('readMsgAll');
    $lines = file($methodRef->getFileName());
    assertAdminMessageReadAll($lines !== false, '无法读取 AdminMessage.php 源码');
    $methodSource = implode('', array_slice(
        $lines,
        $methodRef->getStartLine() - 1,
        $methodRef->getEndLine() - $methodRef->getStartLine() + 1
    ));
    assertAdminMessageReadAll(
        strpos($methodSource, '$where') === false,
        'AdminMessage::readMsgAll 中不应保留未使用的 $where 变量'
    );

    // 2. 初始化临时缓存与 SQLite 内存库
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
        'default' => 'admin_message_read_msg_all_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_message_read_msg_all_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE admin_message (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL DEFAULT "",
        content VARCHAR(255) NOT NULL DEFAULT "",
        target VARCHAR(255) NOT NULL DEFAULT "",
        target_type VARCHAR(255) NOT NULL DEFAULT "",
        sender VARCHAR(255) NOT NULL DEFAULT "",
        sender_type VARCHAR(255) NOT NULL DEFAULT "",
        receiver VARCHAR(255) NOT NULL DEFAULT "",
        receiver_type VARCHAR(255) NOT NULL DEFAULT "",
        type VARCHAR(255) NOT NULL DEFAULT "",
        read_time INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        read_status INTEGER NOT NULL DEFAULT 0
    )');

    Db::name('admin_message')->insertAll([
        [
            'id' => 1,
            'title' => '系统消息',
            'content' => '系统消息1',
            'receiver' => '15',
            'type' => 'system',
            'read_status' => 0,
            'read_time' => 0,
            'create_time' => 1700000100,
        ],
        [
            'id' => 2,
            'title' => '订单消息',
            'content' => '订单消息2',
            'receiver' => '15',
            'type' => 'order',
            'read_status' => 0,
            'read_time' => 0,
            'create_time' => 1700000200,
        ],
        [
            'id' => 3,
            'title' => '他人系统消息',
            'content' => '他人消息3',
            'receiver' => '88',
            'type' => 'system',
            'read_status' => 0,
            'read_time' => 0,
            'create_time' => 1700000300,
        ],
    ]);

    setTestReadAllAdminUserInfo([
        'id' => 15,
        'username' => 'readall_tester',
        'role_id' => 1,
    ]);

    /** @var AdminMessage $controller */
    $controller = $controllerRef->newInstanceWithoutConstructor();

    // 3. 指定 type=system 全部已读
    $reqType = new Request();
    $reqType->withServer(['REQUEST_METHOD' => 'POST'])->withPost(['type' => 'system']);
    $app->instance('request', $reqType);

    $resType = $controller->readMsgAll()->getData();
    assertAdminMessageReadAll(
        ($resType['status'] ?? false) === true,
        'readMsgAll(type=system) 应返回成功'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 1)->value('read_status') === 1,
        '当前接收者 system 类型消息应被标记为已读'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 1)->value('read_time') > 0,
        '已读消息应更新 read_time'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 2)->value('read_status') === 0,
        '非 system 类型消息 id=2 不应被提前标记'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 3)->value('read_status') === 0,
        '其他接收者消息 id=3 不应受到影响'
    );

    // 4. 不传 type，标记当前接收者剩余全部未读消息为已读
    $reqAll = new Request();
    $reqAll->withServer(['REQUEST_METHOD' => 'POST'])->withPost([]);
    $app->instance('request', $reqAll);

    $resAll = $controller->readMsgAll()->getData();
    assertAdminMessageReadAll(
        ($resAll['status'] ?? false) === true,
        'readMsgAll(不限制 type) 应返回成功'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 2)->value('read_status') === 1,
        '当前接收者剩余未读消息 id=2 应被标记为已读'
    );
    assertAdminMessageReadAll(
        (int) Db::name('admin_message')->where('id', 3)->value('read_status') === 0,
        '全量已读仍不得修改其他接收者消息 id=3'
    );
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    setTestReadAllAdminUserInfo([]);
    Session::delete(AdminUserService::KEY_USER_ID);
    Session::delete(AdminUserService::KEY_USER_INFO);
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

echo "admin_message_read_msg_all_test passed\n";
