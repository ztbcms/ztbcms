<?php
/**
 * Author: jayinton
 */

namespace app\common\service\queue;

use app\common\libs\queue\connector\MonitorDatabase;
use app\common\model\queue\QueueMonitorModel;
use app\common\service\BaseService;
use Closure;
use Throwable;
use think\facade\Db;
use think\facade\Log;
use think\queue\Job;
use think\queue\job\Database as DatabaseJob;

/**
 * 队列监控-执行记录写入
 *
 * 由 QueueSubscribe 在 worker 进程中调用，所有方法均不向外抛出异常，
 * 监控写入失败不影响任务本身执行。
 */
class QueueMonitorService extends BaseService
{
    /**
     * 读取监控配置
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function config(string $key, $default = null)
    {
        return config('queue.monitor.' . $key, $default);
    }

    /**
     * 监控是否启用
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return (bool)self::config('enable', true);
    }

    /**
     * 执行记录表名（不含前缀）
     *
     * @return string
     */
    public static function table(): string
    {
        return (string)self::config('table', 'queue_monitor');
    }

    /**
     * 是否支持监控该任务（仅支持 database 驱动）
     *
     * @param mixed $job 队列任务
     * @return bool
     */
    public static function supports($job): bool
    {
        return $job instanceof DatabaseJob;
    }

    /**
     * 当前毫秒时间戳
     *
     * @return int
     */
    public static function nowMs(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    /**
     * 记录任务开始执行
     *
     * @param Job $job 队列任务
     * @param string $connection 队列连接名
     * @return int 执行记录ID，未记录时返回 0
     */
    public function recordStart(Job $job, string $connection): int
    {
        if (!self::isEnabled() || !self::supports($job)) {
            return 0;
        }
        try {
            if (function_exists('memory_reset_peak_usage')) {
                memory_reset_peak_usage();
            }
            $payload = (array)$job->payload();
            $startedAt = self::nowMs();
            $pushedAt = max(0, intval($payload[MonitorDatabase::PAYLOAD_PUSHED_AT] ?? 0));
            $rawJob = self::readRawJob($job);
            $availableMs = intval($rawJob->available_time ?? 0) * 1000;
            $readyAtMs = $job->attempts() <= 1 ? max($pushedAt, $availableMs) : $availableMs;
            $waitMs = $readyAtMs > 0 ? $startedAt - $readyAtMs : 0;

            return intval(Db::name(self::table())->insertGetId([
                'monitor_uuid'      => substr((string)($payload[MonitorDatabase::PAYLOAD_UUID] ?? ''), 0, 36),
                'queue_job_id'      => intval($job->getJobId()),
                'connection'        => substr($connection, 0, 64),
                'queue'             => substr((string)$job->getQueue(), 0, 128),
                'job_name'          => substr(self::resolveJobName($payload), 0, 255),
                'attempt'           => max(0, intval($job->attempts())),
                'status'            => QueueMonitorModel::STATUS_RUNNING,
                'pushed_at'         => $pushedAt,
                'started_at'        => $startedAt,
                'finished_at'       => 0,
                'wait_ms'           => max(0, min($waitMs, 4294967295)),
                'duration_ms'       => 0,
                'memory_peak'       => 0,
                'hostname'          => substr((string)gethostname(), 0, 128),
                'pid'               => intval(getmypid()),
                'exception_class'   => '',
                'exception_message' => '',
                'payload'           => self::truncate((string)$job->getRawBody(), intval(self::config('payload_max_length', 16000))),
                'create_time'       => time(),
            ]));
        } catch (Throwable $e) {
            self::reportError('记录队列任务开始失败', $e);
            return 0;
        }
    }

    /**
     * 记录任务执行结束
     *
     * @param int $id 执行记录ID
     * @param string $status 结束状态
     * @param int $startedAt 开始时间（毫秒）
     * @param Throwable|null $exception 异常
     * @return void
     */
    public function recordFinish(int $id, string $status, int $startedAt, ?Throwable $exception = null): void
    {
        if ($id <= 0) {
            return;
        }
        try {
            $finishedAt = self::nowMs();
            $data = [
                'status'      => $status,
                'finished_at' => $finishedAt,
                'duration_ms' => max(0, min($finishedAt - $startedAt, 4294967295)),
                'memory_peak' => min(memory_get_peak_usage(true), 4294967295),
            ];
            if ($exception) {
                $data['exception_class'] = substr(get_class($exception), 0, 255);
                $data['exception_message'] = self::formatException($exception);
            }
            Db::name(self::table())->where('id', $id)->update($data);
        } catch (Throwable $e) {
            self::reportError('记录队列任务结束失败', $e);
        }
    }

    /**
     * 将执行中的记录标记为指定状态（worker 退出时调用）
     *
     * @param int $id 执行记录ID
     * @param string $status 状态
     * @param int $startedAt 开始时间（毫秒）
     * @param string $message 说明
     * @return void
     */
    public function markInterrupted(int $id, string $status, int $startedAt, string $message): void
    {
        if ($id <= 0) {
            return;
        }
        try {
            $finishedAt = self::nowMs();
            Db::name(self::table())
                ->where('id', $id)
                ->where('status', QueueMonitorModel::STATUS_RUNNING)
                ->update([
                    'status'            => $status,
                    'finished_at'       => $finishedAt,
                    'duration_ms'       => max(0, min($finishedAt - $startedAt, 4294967295)),
                    'memory_peak'       => min(memory_get_peak_usage(true), 4294967295),
                    'exception_message' => $message,
                ]);
        } catch (Throwable $e) {
            self::reportError('标记队列任务中断失败', $e);
        }
    }

    /**
     * 根据 Job 状态推断 JobProcessed 后的结束状态
     *
     * @param Job $job 队列任务
     * @return string
     */
    public static function resolveProcessedStatus(Job $job): string
    {
        if ($job->hasFailed()) {
            return QueueMonitorModel::STATUS_FAILED;
        }
        if ($job->isReleased()) {
            return QueueMonitorModel::STATUS_RELEASED;
        }
        if ($job->isDeleted()) {
            return QueueMonitorModel::STATUS_SUCCEEDED;
        }

        return QueueMonitorModel::STATUS_UNACKED;
    }

    /**
     * 根据 Job 状态推断执行异常后的结束状态
     *
     * @param Job $job 队列任务
     * @return string
     */
    public static function resolveExceptionStatus(Job $job): string
    {
        return $job->hasFailed() ? QueueMonitorModel::STATUS_FAILED : QueueMonitorModel::STATUS_EXCEPTION;
    }

    /**
     * 从 payload 解析任务类名
     *
     * @param array $payload 任务 payload
     * @return string
     */
    public static function resolveJobName(array $payload): string
    {
        $job = (string)($payload['job'] ?? '');
        if ($job === 'think\queue\CallQueuedHandler@call' && !empty($payload['data']['commandName'])) {
            return (string)$payload['data']['commandName'];
        }
        $pos = strpos($job, '@');

        return $pos === false ? $job : substr($job, 0, $pos);
    }

    /**
     * 读取 database 驱动任务的原始行数据（含 available_time 等字段）
     *
     * @param Job $job 队列任务
     * @return object|null
     */
    public static function readRawJob(Job $job)
    {
        if (!$job instanceof DatabaseJob) {
            return null;
        }
        try {
            $reader = Closure::bind(function () {
                return $this->job;
            }, $job, DatabaseJob::class);
            $raw = $reader();

            return is_object($raw) ? $raw : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * 格式化异常信息（类名、消息、位置与截断后的堆栈）
     *
     * @param Throwable $exception 异常
     * @return string
     */
    public static function formatException(Throwable $exception): string
    {
        $text = get_class($exception) . ': ' . $exception->getMessage()
            . "\n" . $exception->getFile() . ':' . $exception->getLine()
            . "\n" . $exception->getTraceAsString();

        return self::truncate($text, intval(self::config('exception_max_length', 8000)));
    }

    /**
     * 按字节截断 UTF-8 字符串
     *
     * @param string $text 原文
     * @param int $maxBytes 最大字节数
     * @return string
     */
    public static function truncate(string $text, int $maxBytes): string
    {
        $maxBytes = max(0, min($maxBytes, 65000));
        if (strlen($text) <= $maxBytes) {
            return $text;
        }

        return mb_strcut($text, 0, $maxBytes, 'UTF-8');
    }

    /**
     * 记录监控自身错误（不抛出）
     *
     * @param string $message 说明
     * @param Throwable $e 异常
     * @return void
     */
    private static function reportError(string $message, Throwable $e): void
    {
        try {
            Log::warning('[queue-monitor] ' . $message . ': ' . $e->getMessage());
        } catch (Throwable $ignored) {
        }
    }
}
