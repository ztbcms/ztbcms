<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\queue\connector;

use think\Db;
use think\queue\connector\Database;

/**
 * 带监控字段的 database 队列驱动
 *
 * 在 think-queue database 驱动基础上，为每个新入队任务的 payload 注入：
 *  - monitor_uuid：任务链路 UUID，release 重试时沿用原 payload，可串联同一任务的多次执行
 *  - pushed_at：首次入队时间（毫秒）
 * 仅新增 payload 字段，不改变任务执行逻辑，旧 payload 仍可正常消费。
 */
class MonitorDatabase extends Database
{
    /**
     * payload 字段：任务链路 UUID
     */
    const PAYLOAD_UUID = 'monitor_uuid';

    /**
     * payload 字段：首次入队时间（毫秒）
     */
    const PAYLOAD_PUSHED_AT = 'pushed_at';

    /**
     * 容器实例化入口（父类使用 new self，需改为 new static 才能实例化子类）
     *
     * @param Db $db 数据库管理器
     * @param array $config 队列连接配置
     * @return static
     */
    public static function __make(Db $db, $config)
    {
        $connection = $db->connect($config['connection'] ?? null);

        return new static($connection, $config['table'], $config['queue'], $config['retry_after'] ?? 60);
    }

    /**
     * 构建 payload 数组，并注入监控字段
     *
     * @param mixed $job 任务类名或任务对象
     * @param mixed $data 任务数据
     * @return array
     */
    protected function createPayloadArray($job, $data = '')
    {
        $payload = parent::createPayloadArray($job, $data);

        return self::withMonitorFields($payload);
    }

    /**
     * 为 payload 补充监控字段（已存在时保持不变）
     *
     * @param array $payload 任务 payload
     * @param bool $force 是否强制重新生成（人工重试失败任务时使用）
     * @return array
     */
    public static function withMonitorFields(array $payload, bool $force = false): array
    {
        if ($force || empty($payload[self::PAYLOAD_UUID])) {
            $payload[self::PAYLOAD_UUID] = self::generateUuid();
        }
        if ($force || empty($payload[self::PAYLOAD_PUSHED_AT])) {
            $payload[self::PAYLOAD_PUSHED_AT] = (int)floor(microtime(true) * 1000);
        }

        return $payload;
    }

    /**
     * 生成 UUID v4
     *
     * @return string
     */
    public static function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
