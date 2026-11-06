<?php

use think\migration\Migrator;

/**
 * 创建队列监控执行记录表并注册维护计划任务
 */
class CreateQueueMonitor extends Migrator
{
    /**
     * 创建队列监控表并注册清理、丢失兜底计划任务
     *
     * @return void
     */
    public function up()
    {
        $table = $this->quotedTable('queue_monitor');
        $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS {$table} (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `monitor_uuid` char(36) NOT NULL DEFAULT '' COMMENT '任务链路UUID（同一任务多次重试共用）',
  `queue_job_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '队列表任务ID（queue_jobs.id）',
  `connection` varchar(64) NOT NULL DEFAULT '' COMMENT '队列连接名',
  `queue` varchar(128) NOT NULL DEFAULT '' COMMENT '队列名称',
  `job_name` varchar(255) NOT NULL DEFAULT '' COMMENT '任务类名',
  `attempt` int unsigned NOT NULL DEFAULT '0' COMMENT '第几次尝试执行',
  `status` varchar(16) NOT NULL DEFAULT '' COMMENT '状态：running/succeeded/released/failed/exception/timeout/interrupted/unacked/lost',
  `pushed_at` bigint unsigned NOT NULL DEFAULT '0' COMMENT '首次入队毫秒时间戳',
  `started_at` bigint unsigned NOT NULL DEFAULT '0' COMMENT '开始执行毫秒时间戳',
  `finished_at` bigint unsigned NOT NULL DEFAULT '0' COMMENT '结束执行毫秒时间戳',
  `wait_ms` int unsigned NOT NULL DEFAULT '0' COMMENT '本次排队等待时长（毫秒）',
  `duration_ms` int unsigned NOT NULL DEFAULT '0' COMMENT '执行耗时（毫秒）',
  `memory_peak` int unsigned NOT NULL DEFAULT '0' COMMENT '进程内存峰值（字节）',
  `hostname` varchar(128) NOT NULL DEFAULT '' COMMENT 'Worker主机名',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT 'Worker进程PID',
  `exception_class` varchar(255) NOT NULL DEFAULT '' COMMENT '异常类名',
  `exception_message` text COMMENT '异常消息与截断堆栈',
  `payload` text COMMENT '任务Payload（截断）',
  `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间（秒）',
  PRIMARY KEY (`id`),
  KEY `idx_started_at` (`started_at`),
  KEY `idx_queue_started` (`queue`, `started_at`),
  KEY `idx_job_started` (`job_name`, `started_at`),
  KEY `idx_status_started` (`status`, `started_at`),
  KEY `idx_uuid` (`monitor_uuid`),
  KEY `idx_host_pid` (`hostname`, `pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='队列-执行监控记录表';
SQL
        );

        $this->upsertCron(
            '队列监控记录清理',
            'day',
            '0-4-0',
            'app\\\\common\\\\cronscript\\\\QueueMonitorCleanScript'
        );
        $this->upsertCron(
            '队列监控丢失记录兜底',
            'now',
            '0-0-10',
            'app\\\\common\\\\cronscript\\\\QueueMonitorLostScript'
        );
    }

    /**
     * 回滚队列监控表及计划任务
     *
     * @return void
     */
    public function down()
    {
        if ($this->tableExists($this->tableName('cron'))) {
            $cronTable = $this->quotedTable('cron');
            $this->execute("DELETE FROM {$cronTable} WHERE `cron_file` IN ('app\\\\common\\\\cronscript\\\\QueueMonitorCleanScript', 'app\\\\common\\\\cronscript\\\\QueueMonitorLostScript')");
        }
        $table = $this->quotedTable('queue_monitor');
        $this->execute("DROP TABLE IF EXISTS {$table}");
    }

    /**
     * 幂等写入计划任务
     *
     * @param string $subject 任务名称
     * @param string $loopType 循环类型
     * @param string $loopDaytime 循环时间
     * @param string $escapedClass SQL 转义后的类名
     * @return void
     */
    private function upsertCron(string $subject, string $loopType, string $loopDaytime, string $escapedClass): void
    {
        if (!$this->tableExists($this->tableName('cron'))) {
            return;
        }
        $cronTable = $this->quotedTable('cron');
        $this->execute("INSERT INTO {$cronTable} (`type`,`subject`,`loop_type`,`loop_daytime`,`cron_file`,`isopen`,`created_time`,`modified_time`,`next_time`,`data`) SELECT 0,'{$subject}','{$loopType}','{$loopDaytime}','{$escapedClass}',1,UNIX_TIMESTAMP(),0,UNIX_TIMESTAMP(),NULL WHERE NOT EXISTS (SELECT 1 FROM {$cronTable} WHERE `cron_file` = '{$escapedClass}')");
    }

    /**
     * 判断数据表是否存在
     *
     * @param string $table 完整表名
     * @return bool
     */
    private function tableExists(string $table): bool
    {
        return (bool)$this->fetchRow("SHOW TABLES LIKE '{$table}'");
    }

    /**
     * 获取带前缀的表名
     *
     * @param string $name 不含前缀的表名
     * @return string
     */
    private function tableName(string $name): string
    {
        $connection = (string)config('database.default', 'mysql');
        $prefix = (string)config("database.connections.{$connection}.prefix", env('database.prefix', 'ztb_'));

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
