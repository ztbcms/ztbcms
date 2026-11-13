<?php

use app\admin\model\RoleModel;
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
function assertRoleModelCleanup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    // 1. 验证废弃方法已移除，核心常量与保留方法存在
    $ref = new ReflectionClass(RoleModel::class);
    $removedMethods = [
        'getAccessList',
        'getArrchildid',
        'getTree',
        'roleDelete',
        'isCompetence',
        'privArrStr',
        'getRoleIdName',
        'getMenuAccessList',
        'getMenuList',
        'getSelectMenuId',
    ];

    foreach ($removedMethods as $method) {
        assertRoleModelCleanup(
            !$ref->hasMethod($method),
            "RoleModel::{$method} 应已移除"
        );
    }

    assertRoleModelCleanup(
        RoleModel::SUPER_ADMIN_ROLE_ID === 1,
        'RoleModel::SUPER_ADMIN_ROLE_ID 应为 1'
    );
    assertRoleModelCleanup(
        RoleModel::STATUS_YES === 1 && RoleModel::STATUS_NO === 0,
        'RoleModel 状态常量应保留'
    );
    assertRoleModelCleanup(
        $ref->hasMethod('getChildrenRoleIdList'),
        'RoleModel::getChildrenRoleIdList 应保留'
    );

    // 2. 验证 RoleModel::getChildrenRoleIdList 在内存 SQLite 下工作正常
    Config::set([
        'default' => 'admin_role_model_cleanup_test',
        'auto_timestamp' => false,
        'datetime_format' => 'Y-m-d H:i:s',
        'connections' => [
            'admin_role_model_cleanup_test' => [
                'type' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'fields_strict' => true,
            ],
        ],
    ], 'database');

    Db::execute('CREATE TABLE role (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(64) NOT NULL,
        parentid INTEGER NOT NULL DEFAULT 0,
        status INTEGER NOT NULL DEFAULT 1,
        remark VARCHAR(255) NOT NULL DEFAULT "",
        listorder INTEGER NOT NULL DEFAULT 0,
        create_time INTEGER NOT NULL DEFAULT 0,
        update_time INTEGER NOT NULL DEFAULT 0
    )');

    Db::name('role')->insertAll([
        ['id' => 1, 'name' => '超级管理员', 'parentid' => 0, 'status' => 1, 'listorder' => 0],
        ['id' => 2, 'name' => '运营总监', 'parentid' => 1, 'status' => 1, 'listorder' => 1],
        ['id' => 3, 'name' => '运营专员', 'parentid' => 2, 'status' => 1, 'listorder' => 2],
        ['id' => 4, 'name' => '内容编辑', 'parentid' => 2, 'status' => 1, 'listorder' => 1],
        ['id' => 5, 'name' => '独立访客', 'parentid' => 0, 'status' => 1, 'listorder' => 3],
    ]);

    $roleModel = new RoleModel();

    // 不包含自身
    $childrenWithoutSelf = array_map('intval', $roleModel->getChildrenRoleIdList(1, false));
    sort($childrenWithoutSelf);
    assertRoleModelCleanup(
        $childrenWithoutSelf === [2, 3, 4],
        '角色 1 的子角色列表（不含自身）应为 [2, 3, 4]，实际为 ' . json_encode($childrenWithoutSelf)
    );

    // 包含自身
    $childrenWithSelf = array_map('intval', $roleModel->getChildrenRoleIdList(2, true));
    sort($childrenWithSelf);
    assertRoleModelCleanup(
        $childrenWithSelf === [2, 3, 4],
        '角色 2 的子角色列表（含自身）应为 [2, 3, 4]，实际为 ' . json_encode($childrenWithSelf)
    );

    // 无子角色时
    $leafChildren = $roleModel->getChildrenRoleIdList(5, false);
    assertRoleModelCleanup(
        $leafChildren === [],
        '叶子角色 5 的子角色列表（不含自身）应为空数组'
    );
    $leafChildrenWithSelf = array_map('intval', $roleModel->getChildrenRoleIdList(5, true));
    assertRoleModelCleanup(
        $leafChildrenWithSelf === [5],
        '叶子角色 5 的子角色列表（含自身）应为 [5]'
    );
} finally {
    restore_error_handler();
}

echo "admin_role_model_cleanup_test passed\n";
