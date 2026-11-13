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
function assertAdminMessageDispatch(bool $condition, string $message): void
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
function setTestAdminMessageUserInfo(array $userInfo): void
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

/**
 * 重建测试消息数据
 *
 * @return void
 */
function seedAdminMessageRows(): void
{
    Db::execute('DELETE FROM admin_message');
    Db::name('admin_message')->insertAll([
        [
            'id' => 1,
            'title' => '系统通知1',
            'content' => '系统消息内容1',
            'target' => '1',
            'target_type' => 'test',
            'sender' => '1',
            'sender_type' => 'admin',
            'receiver' => '10',
            'receiver_type' => 'admin',
            'type' => 'system',
            'read_time' => 0,
            'create_time' => 1700000001,
            'read_status' => 0,
        ],
        [
            'id' => 2,
            'title' => '业务通知2',
            'content' => '普通消息内容2',
            'target' => '2',
            'target_type' => 'test',
            'sender' => '1',
            'sender_type' => 'admin',
            'receiver' => '10',
            'receiver_type' => 'admin',
            'type' => 'notice',
            'read_time' => 0,
            'create_time' => 1700000002,
            'read_status' => 0,
        ],
        [
            'id' => 3,
            'title' => '系统通知3',
            'content' => '系统消息内容3',
            'target' => '3',
            'target_type' => 'test',
            'sender' => '1',
            'sender_type' => 'admin',
            'receiver' => '10',
            'receiver_type' => 'admin',
            'type' => 'system',
            'read_time' => 0,
            'create_time' => 1700000003,
            'read_status' => 0,
        ],
        [
            'id' => 4,
            'title' => '他人未读消息',
            'content' => '其他管理员消息',
            'target' => '4',
            'target_type' => 'test',
            'sender' => '1',
            'sender_type' => 'admin',
            'receiver' => '99',
            'receiver_type' => 'admin',
            'type' => 'system',
            'read_time' => 0,
            'create_time' => 1700000004,
            'read_status' => 0,
        ],
    ]);
}

$cacheDir = sys_get_temp_dir() . '/ztbcms_admin_msg_dispatch_cache_' . getmypid() . '_' . uniqid('', true) . '/';

try {
    // 1. 检查结构与源码去重
    $controllerRef = new ReflectionClass(AdminMessage::class);
    assertAdminMessageDispatch(
        $controllerRef->hasMethod('handleMessagePage'),
        'AdminMessage 应抽取私有方法 handleMessagePage'
    );
    $helperMethod = $controllerRef->getMethod('handleMessagePage');
    assertAdminMessageDispatch(
        $helperMethod->isPrivate(),
        'handleMessagePage 必须为 private 方法'
    );
    assertAdminMessageDispatch(
        $helperMethod->getNumberOfParameters() === 1,
        'handleMessagePage 应接收 1 个模板参数'
    );

    $source = file_get_contents(dirname(__DIR__) . '/app/admin/controller/AdminMessage.php');
    assertAdminMessageDispatch(
        $source !== false && substr_count($source, "\$_action == 'getAdminMsgList'") === 1,
        'AdminMessage.php 中消息列表页面 _action 分发逻辑应只保留 1 份'
    );
    $lines = $source !== false ? file(dirname(__DIR__) . '/app/admin/controller/AdminMessage.php') : [];
    foreach (['index', 'noRead', 'system'] as $tpl) {
        $entryRef = $controllerRef->getMethod($tpl);
        $entryBody = implode('', array_slice(
            $lines,
            $entryRef->getStartLine() - 1,
            $entryRef->getEndLine() - $entryRef->getStartLine() + 1
        ));
        assertAdminMessageDispatch(
            strpos($entryBody, "\$this->handleMessagePage('{$tpl}')") !== false
            && strpos($entryBody, '_action') === false,
            "{$tpl}() 应仅委托调用 \$this->handleMessagePage('{$tpl}')"
        );
    }

    // 2. 配置临时缓存与内存 SQLite 数据库
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
        'default' => 'admin_message_dispatch_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_message_dispatch_test' => [
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

    setTestAdminMessageUserInfo([
        'id' => 10,
        'username' => 'msg_tester',
        'role_id' => 1,
    ]);

    /** @var AdminMessage $controller */
    $controller = $controllerRef->newInstanceWithoutConstructor();

    // 3. 逐一验证 index / noRead / system 三个入口的 _action 分发行为一致
    foreach (['index', 'noRead', 'system'] as $entryMethod) {
        seedAdminMessageRows();

        // 3.1 _action=getAdminMsgList
        $reqList = new Request();
        $reqList->withServer(['REQUEST_METHOD' => 'GET'])->withGet([
            '_action' => 'getAdminMsgList',
            'page' => 1,
            'limit' => 15,
        ]);
        $app->instance('request', $reqList);

        $listRes = $controller->{$entryMethod}()->getData();
        assertAdminMessageDispatch(
            ($listRes['status'] ?? false) === true && ($listRes['code'] ?? 0) === 200,
            "{$entryMethod} _action=getAdminMsgList 应返回成功"
        );
        assertAdminMessageDispatch(
            ($listRes['data']['total_items'] ?? -1) === 3,
            "{$entryMethod} 仅应返回当前管理员(10)的 3 条消息，实际：" . ($listRes['data']['total_items'] ?? 'null')
        );

        // 3.2 _action=readMsg（携带他人消息 ID=4，验证越权消息不会被置为已读）
        $reqRead = new Request();
        $reqRead->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
            '_action' => 'readMsg',
            'ids' => [1, 4],
        ]);
        $app->instance('request', $reqRead);

        $readRes = $controller->{$entryMethod}()->getData();
        assertAdminMessageDispatch(
            ($readRes['status'] ?? false) === true,
            "{$entryMethod} _action=readMsg 应返回成功"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 1)->value('read_status') === 1,
            "{$entryMethod} _action=readMsg 应将 id=1 标记为已读"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 2)->value('read_status') === 0,
            "{$entryMethod} _action=readMsg 不应修改未指定的 id=2"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 4)->value('read_status') === 0,
            "{$entryMethod} _action=readMsg 不应修改他人消息 id=4"
        );

        // 3.3 _action=readMsgAll（先按 type=system 全部已读，再全量已读）
        $reqReadAllSystem = new Request();
        $reqReadAllSystem->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
            '_action' => 'readMsgAll',
            'type' => 'system',
        ]);
        $app->instance('request', $reqReadAllSystem);

        $readAllSystemRes = $controller->{$entryMethod}()->getData();
        assertAdminMessageDispatch(
            ($readAllSystemRes['status'] ?? false) === true,
            "{$entryMethod} _action=readMsgAll(type=system) 应返回成功"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 3)->value('read_status') === 1,
            "{$entryMethod} _action=readMsgAll(type=system) 应将 id=3 标记为已读"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 2)->value('read_status') === 0,
            "{$entryMethod} _action=readMsgAll(type=system) 不应标记非 system 类型消息 id=2"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 4)->value('read_status') === 0,
            "{$entryMethod} _action=readMsgAll(type=system) 不应标记他人消息 id=4"
        );

        $reqReadAll = new Request();
        $reqReadAll->withServer(['REQUEST_METHOD' => 'POST'])->withPost([
            '_action' => 'readMsgAll',
        ]);
        $app->instance('request', $reqReadAll);

        $readAllRes = $controller->{$entryMethod}()->getData();
        assertAdminMessageDispatch(
            ($readAllRes['status'] ?? false) === true,
            "{$entryMethod} _action=readMsgAll(全部) 应返回成功"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 2)->value('read_status') === 1,
            "{$entryMethod} _action=readMsgAll(全部) 应将 id=2 标记为已读"
        );
        assertAdminMessageDispatch(
            (int) Db::name('admin_message')->where('id', 4)->value('read_status') === 0,
            "{$entryMethod} _action=readMsgAll(全部) 不应标记他人消息 id=4"
        );
    }
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    restore_error_handler();
    exit(1);
} finally {
    setTestAdminMessageUserInfo([]);
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

echo "admin_message_dispatch_test passed\n";
