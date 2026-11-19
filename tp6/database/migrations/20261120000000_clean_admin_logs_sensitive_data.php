<?php

use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use think\migration\Migrator;

/**
 * 清理后台登录日志与操作日志中的历史敏感密码数据
 */
class CleanAdminLogsSensitiveData extends Migrator
{
    /**
     * 清理历史登录日志密码列及操作日志敏感请求参数
     *
     * @return void
     */
    public function up()
    {
        LoginlogModel::cleanHistoryPasswords();
        OperationlogModel::cleanHistoryParams();
    }

    /**
     * 安全清理不可逆，不执行回滚操作
     *
     * @return void
     */
    public function down()
    {
        // 单向安全脱敏清理，不恢复历史明文密码数据
    }
}
