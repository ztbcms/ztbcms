<?php

use think\migration\Migrator;

/**
 * 扩展登录日志、操作日志与管理员表的 IP 字段长度以支持 IPv6（45 字符）
 */
class WidenLogIpColumns extends Migrator
{
    /**
     * 扩展 IP 字段宽度至 VARCHAR(45)
     *
     * @return void
     */
    public function up()
    {
        if ($this->tableExists($this->tableName('login_log'))) {
            $table = $this->quotedTable('login_log');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `loginip` varchar(45) NOT NULL DEFAULT '' COMMENT '登录IP'");
        }
        if ($this->tableExists($this->tableName('operation_log'))) {
            $table = $this->quotedTable('operation_log');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP'");
        }
        if ($this->tableExists($this->tableName('user'))) {
            $table = $this->quotedTable('user');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `last_login_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '上次登录IP'");
        }
    }

    /**
     * 回滚 IP 字段宽度
     *
     * @return void
     */
    public function down()
    {
        if ($this->tableExists($this->tableName('login_log'))) {
            $table = $this->quotedTable('login_log');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `loginip` char(20) NOT NULL DEFAULT '' COMMENT '登录IP'");
        }
        if ($this->tableExists($this->tableName('operation_log'))) {
            $table = $this->quotedTable('operation_log');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `ip` char(20) NOT NULL DEFAULT '' COMMENT 'IP'");
        }
        if ($this->tableExists($this->tableName('user'))) {
            $table = $this->quotedTable('user');
            $this->execute("ALTER TABLE {$table} MODIFY COLUMN `last_login_ip` varchar(40) NOT NULL DEFAULT '' COMMENT '上次登录IP'");
        }
    }

    /**
     * 判断数据表是否存在
     *
     * @param string $table 完整表名
     * @return bool
     */
    private function tableExists(string $table): bool
    {
        return (bool) $this->fetchRow("SHOW TABLES LIKE '{$table}'");
    }

    /**
     * 获取带前缀的表名
     *
     * @param string $name 不含前缀的表名
     * @return string
     */
    private function tableName(string $name): string
    {
        $connection = (string) config('database.default', 'mysql');
        $prefix = (string) config("database.connections.{$connection}.prefix", env('database.prefix', 'ztb_'));

        return $prefix . $name;
    }

    /**
     * 获取带反引号的完整表名
     *
     * @param string $name 不含前缀的表名
     * @return string
     */
    private function quotedTable(string $name): string
    {
        return '`' . str_replace('`', '``', $this->tableName($name)) . '`';
    }
}
