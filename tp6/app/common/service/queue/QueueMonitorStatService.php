<?php
/**
 * Author: jayinton
 */

namespace app\common\service\queue;

use app\common\model\queue\QueueMonitorModel;
use app\common\service\BaseService;
use think\facade\Db;
use think\queue\connector\Database;

/**
 * 队列监控-看板与统计查询
 */
class QueueMonitorStatService extends BaseService
{
    /**
     * 统计区间配置：区间秒数、趋势分桶秒数
     */
    const RANGES = [
        '1h'  => ['seconds' => 3600, 'bucket' => 60, 'label' => '近1小时'],
        '24h' => ['seconds' => 86400, 'bucket' => 3600, 'label' => '近24小时'],
        '7d'  => ['seconds' => 604800, 'bucket' => 86400, 'label' => '近7天'],
    ];

    /**
     * 单个任务类计算 P95 的最大样本数
     */
    const P95_SAMPLE_LIMIT = 5000;

    /**
     * 任务类统计最多展示的分组数
     */
    const JOB_GROUP_LIMIT = 50;

    /**
     * 判断队列连接配置是否为 database 驱动（含其子类驱动）
     *
     * @param string $connection 队列连接名
     * @return bool
     */
    public static function isDatabaseConnection(string $connection): bool
    {
        $type = (string)config('queue.connections.' . $connection . '.type', '');
        if ($type === '') {
            return false;
        }
        if (strtolower($type) === 'database') {
            return true;
        }

        return class_exists($type) && is_a($type, Database::class, true);
    }

    /**
     * 获取用于监控的 database 队列连接配置
     * 优先使用默认连接，默认连接不是 database 驱动时回退到名为 database 的连接
     *
     * @return array|null ['name', 'table', 'retry_after', 'connection', 'is_default']
     */
    public static function resolveDatabaseConnection(): ?array
    {
        $default = (string)config('queue.default', 'database');
        $name = null;
        if (self::isDatabaseConnection($default)) {
            $name = $default;
        } elseif (self::isDatabaseConnection('database')) {
            $name = 'database';
        }
        if ($name === null) {
            return null;
        }

        return [
            'name'        => $name,
            'table'       => (string)config('queue.connections.' . $name . '.table', 'queue_jobs'),
            'retry_after' => max(1, intval(config('queue.connections.' . $name . '.retry_after', 60))),
            'connection'  => config('queue.connections.' . $name . '.connection') ?: null,
            'is_default'  => $name === $default,
        ];
    }

    /**
     * 失败任务表名
     *
     * @return string
     */
    public static function failedTable(): string
    {
        return (string)config('queue.failed.table', 'queue_failed_jobs');
    }

    /**
     * 队列实时状态（基于 queue_jobs）
     *
     * @return array
     */
    public function getRealtime(): array
    {
        $conn = self::resolveDatabaseConnection();
        if (!$conn) {
            return self::createReturn(false, null, '当前队列驱动不是 database，暂不支持监控');
        }
        $now = time();
        $expiration = $now - $conn['retry_after'];
        $rows = Db::connect($conn['connection'])
            ->name($conn['table'])
            ->fieldRaw(
                'queue,'
                . "SUM(CASE WHEN reserve_time IS NULL AND available_time <= {$now} THEN 1 ELSE 0 END) AS pending_count,"
                . "SUM(CASE WHEN reserve_time IS NULL AND available_time > {$now} THEN 1 ELSE 0 END) AS delayed_count,"
                . "SUM(CASE WHEN reserve_time > {$expiration} THEN 1 ELSE 0 END) AS reserved_count,"
                . "SUM(CASE WHEN reserve_time <= {$expiration} THEN 1 ELSE 0 END) AS stale_count,"
                . "MIN(CASE WHEN reserve_time IS NULL AND available_time <= {$now} THEN available_time ELSE NULL END) AS oldest_available"
            )
            ->group('queue')
            ->select()
            ->toArray();

        $queues = [];
        foreach ($rows as $row) {
            $oldest = intval($row['oldest_available'] ?? 0);
            $queues[$row['queue']] = [
                'queue'            => (string)$row['queue'],
                'pending'          => intval($row['pending_count']),
                'delayed'          => intval($row['delayed_count']),
                'reserved'         => intval($row['reserved_count']),
                'stale'            => intval($row['stale_count']),
                'max_wait_seconds' => $oldest > 0 ? max(0, $now - $oldest) : 0,
                'failed'           => 0,
            ];
        }

        // 失败任务数
        $failedRows = Db::name(self::failedTable())
            ->fieldRaw('queue, COUNT(*) AS total')
            ->group('queue')
            ->select()
            ->toArray();
        foreach ($failedRows as $row) {
            $queue = (string)$row['queue'];
            if (!isset($queues[$queue])) {
                $queues[$queue] = self::emptyQueueRow($queue);
            }
            $queues[$queue]['failed'] = intval($row['total']);
        }

        // 近 24 小时执行过的队列，空闲时也展示
        $recentQueues = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', (time() - 86400) * 1000)
            ->group('queue')
            ->column('queue');
        foreach ($recentQueues as $queue) {
            if (!isset($queues[$queue])) {
                $queues[$queue] = self::emptyQueueRow((string)$queue);
            }
        }
        ksort($queues);

        return self::createReturn(true, [
            'connection'  => $conn['name'],
            'is_default'  => $conn['is_default'],
            'retry_after' => $conn['retry_after'],
            'monitor_enable' => QueueMonitorService::isEnabled(),
            'queues'      => array_values($queues),
            'time'        => $now,
        ]);
    }

    /**
     * 统计数据：汇总、按任务类统计、趋势
     *
     * @param string $range 统计区间（1h/24h/7d）
     * @return array
     */
    public function getStats(string $range): array
    {
        $rangeConfig = self::RANGES[$range] ?? self::RANGES['1h'];
        $range = array_search($rangeConfig, self::RANGES, true);
        $nowMs = QueueMonitorService::nowMs();
        $sinceMs = $nowMs - $rangeConfig['seconds'] * 1000;

        return self::createReturn(true, [
            'range'   => $range,
            'summary' => $this->getSummary($sinceMs),
            'jobs'    => $this->getJobStats($sinceMs),
            'trend'   => $this->getTrend($sinceMs, $nowMs, $rangeConfig['bucket']),
        ]);
    }

    /**
     * 汇总统计
     *
     * @param int $sinceMs 起始毫秒
     * @return array
     */
    public function getSummary(int $sinceMs): array
    {
        $statusCounts = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->group('status')
            ->column('COUNT(*)', 'status');
        $statusCounts = array_map('intval', $statusCounts);
        $total = array_sum($statusCounts);
        $running = $statusCounts[QueueMonitorModel::STATUS_RUNNING] ?? 0;
        $succeeded = $statusCounts[QueueMonitorModel::STATUS_SUCCEEDED] ?? 0;
        $errors = 0;
        foreach (QueueMonitorModel::ERROR_STATUSES as $status) {
            $errors += $statusCounts[$status] ?? 0;
        }
        $finished = $total - $running;

        $agg = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->whereNotIn('status', [QueueMonitorModel::STATUS_RUNNING, QueueMonitorModel::STATUS_LOST])
            ->fieldRaw('AVG(duration_ms) AS avg_duration, MAX(duration_ms) AS max_duration, AVG(wait_ms) AS avg_wait')
            ->find();

        return [
            'total'          => $total,
            'running'        => $running,
            'succeeded'      => $succeeded,
            'errors'         => $errors,
            'status_counts'  => $statusCounts,
            'success_rate'   => $finished > 0 ? round($succeeded * 100 / $finished, 2) : null,
            'error_rate'     => $finished > 0 ? round($errors * 100 / $finished, 2) : null,
            'avg_duration'   => self::roundOrNull($agg['avg_duration'] ?? null),
            'max_duration'   => isset($agg['max_duration']) ? intval($agg['max_duration']) : null,
            'p95_duration'   => $this->percentile($sinceMs, null, null, 95),
            'avg_wait'       => self::roundOrNull($agg['avg_wait'] ?? null),
            'failed_jobs'    => intval(Db::name(self::failedTable())->count()),
        ];
    }

    /**
     * 按队列 + 任务类统计
     *
     * @param int $sinceMs 起始毫秒
     * @return array
     */
    public function getJobStats(int $sinceMs): array
    {
        $succeeded = QueueMonitorModel::STATUS_SUCCEEDED;
        $running = QueueMonitorModel::STATUS_RUNNING;
        $lost = QueueMonitorModel::STATUS_LOST;
        $errorIn = "'" . implode("','", QueueMonitorModel::ERROR_STATUSES) . "'";
        $rows = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->fieldRaw(
                'queue, job_name, COUNT(*) AS total,'
                . "SUM(CASE WHEN status = '{$succeeded}' THEN 1 ELSE 0 END) AS succeeded,"
                . "SUM(CASE WHEN status = '{$running}' THEN 1 ELSE 0 END) AS running,"
                . "SUM(CASE WHEN status IN ({$errorIn}) THEN 1 ELSE 0 END) AS errors,"
                . "AVG(CASE WHEN status NOT IN ('{$running}','{$lost}') THEN duration_ms ELSE NULL END) AS avg_duration,"
                . "MAX(CASE WHEN status NOT IN ('{$running}','{$lost}') THEN duration_ms ELSE NULL END) AS max_duration,"
                . 'AVG(wait_ms) AS avg_wait,'
                . 'MAX(started_at) AS last_started_at'
            )
            ->group('queue, job_name')
            ->order('total', 'desc')
            ->limit(self::JOB_GROUP_LIMIT)
            ->select()
            ->toArray();

        $result = [];
        foreach ($rows as $row) {
            $total = intval($row['total']);
            $finished = $total - intval($row['running']);
            $result[] = [
                'queue'           => (string)$row['queue'],
                'job_name'        => (string)$row['job_name'],
                'total'           => $total,
                'succeeded'       => intval($row['succeeded']),
                'running'         => intval($row['running']),
                'errors'          => intval($row['errors']),
                'success_rate'    => $finished > 0 ? round(intval($row['succeeded']) * 100 / $finished, 2) : null,
                'avg_duration'    => self::roundOrNull($row['avg_duration']),
                'max_duration'    => isset($row['max_duration']) ? intval($row['max_duration']) : null,
                'p95_duration'    => $this->percentile($sinceMs, (string)$row['queue'], (string)$row['job_name'], 95),
                'avg_wait'        => self::roundOrNull($row['avg_wait']),
                'last_started_at' => intval($row['last_started_at']),
            ];
        }

        return $result;
    }

    /**
     * 吞吐量趋势（按状态分组计数）
     *
     * @param int $sinceMs 起始毫秒
     * @param int $nowMs 当前毫秒
     * @param int $bucketSeconds 分桶秒数
     * @return array ['bucket_seconds', 'points' => [['time', 'total', 'succeeded', 'released', 'errors', 'other']]]
     */
    public function getTrend(int $sinceMs, int $nowMs, int $bucketSeconds): array
    {
        $bucketMs = $bucketSeconds * 1000;
        // 按本地时区对齐分桶（例如按天统计时以本地零点为界）
        $offsetMs = intval(date('Z')) * 1000;
        $bucketExpr = "((started_at + {$offsetMs}) - ((started_at + {$offsetMs}) % {$bucketMs}) - {$offsetMs})";
        $rows = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->fieldRaw("{$bucketExpr} AS bucket, status, COUNT(*) AS total")
            ->group('bucket, status')
            ->select()
            ->toArray();

        $points = [];
        $start = ($sinceMs + $offsetMs) - (($sinceMs + $offsetMs) % $bucketMs) - $offsetMs;
        for ($t = $start; $t <= $nowMs; $t += $bucketMs) {
            $points[$t] = ['time' => intval($t / 1000), 'total' => 0, 'succeeded' => 0, 'released' => 0, 'errors' => 0, 'other' => 0];
        }
        foreach ($rows as $row) {
            $bucket = intval($row['bucket']);
            if (!isset($points[$bucket])) {
                continue;
            }
            $count = intval($row['total']);
            $points[$bucket]['total'] += $count;
            if ($row['status'] === QueueMonitorModel::STATUS_SUCCEEDED) {
                $points[$bucket]['succeeded'] += $count;
            } elseif ($row['status'] === QueueMonitorModel::STATUS_RELEASED) {
                $points[$bucket]['released'] += $count;
            } elseif (in_array($row['status'], QueueMonitorModel::ERROR_STATUSES, true)) {
                $points[$bucket]['errors'] += $count;
            } else {
                $points[$bucket]['other'] += $count;
            }
        }

        return ['bucket_seconds' => $bucketSeconds, 'points' => array_values($points)];
    }

    /**
     * 活跃 worker（根据最近执行记录推断）
     *
     * @return array
     */
    public function getWorkers(): array
    {
        $activeSeconds = max(60, intval(QueueMonitorService::config('worker_active_seconds', 300)));
        $sinceMs = QueueMonitorService::nowMs() - $activeSeconds * 1000;
        $running = QueueMonitorModel::STATUS_RUNNING;
        $rows = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->fieldRaw(
                'hostname, pid, COUNT(*) AS processed, MAX(started_at) AS last_started_at, MAX(id) AS last_id,'
                . "SUM(CASE WHEN status = '{$running}' THEN 1 ELSE 0 END) AS running"
            )
            ->group('hostname, pid')
            ->order('last_started_at', 'desc')
            ->select()
            ->toArray();
        $lastIds = array_column($rows, 'last_id');
        $lastJobs = $lastIds ? Db::name(QueueMonitorService::table())
            ->whereIn('id', $lastIds)
            ->column('queue, job_name, status', 'id') : [];

        $workers = [];
        foreach ($rows as $row) {
            $last = $lastJobs[$row['last_id']] ?? [];
            $workers[] = [
                'hostname'        => (string)$row['hostname'],
                'pid'             => intval($row['pid']),
                'processed'       => intval($row['processed']),
                'is_busy'         => intval($row['running']) > 0,
                'last_started_at' => intval($row['last_started_at']),
                'last_queue'      => (string)($last['queue'] ?? ''),
                'last_job_name'   => (string)($last['job_name'] ?? ''),
                'last_status'     => (string)($last['status'] ?? ''),
            ];
        }

        return self::createReturn(true, [
            'active_seconds' => $activeSeconds,
            'workers'        => $workers,
        ]);
    }

    /**
     * 执行记录列表
     *
     * @param array $filters 筛选条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getMonitorList(array $filters, int $page, int $limit): array
    {
        $query = Db::name(QueueMonitorService::table());
        if (!empty($filters['queue'])) {
            $query->where('queue', (string)$filters['queue']);
        }
        if (!empty($filters['job_name'])) {
            $query->where('job_name', (string)$filters['job_name']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', (string)$filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $start = strtotime((string)$filters['start_date'] . ' 00:00:00');
            if ($start) {
                $query->where('started_at', '>=', $start * 1000);
            }
        }
        if (!empty($filters['end_date'])) {
            $end = strtotime((string)$filters['end_date'] . ' 23:59:59');
            if ($end) {
                $query->where('started_at', '<=', $end * 1000 + 999);
            }
        }
        if (isset($filters['min_duration']) && $filters['min_duration'] !== '' && is_numeric($filters['min_duration'])) {
            $query->where('duration_ms', '>=', max(0, intval($filters['min_duration'])));
        }
        if (!empty($filters['hostname'])) {
            $query->where('hostname', (string)$filters['hostname']);
        }
        if (!empty($filters['pid'])) {
            $query->where('pid', intval($filters['pid']));
        }
        $keyword = trim((string)($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            if (ctype_digit($keyword)) {
                $query->where('queue_job_id', intval($keyword));
            } else {
                $query->where('monitor_uuid', $keyword);
            }
        }

        $total = (clone $query)->count();
        $items = $query
            ->field('id, monitor_uuid, queue_job_id, connection, queue, job_name, attempt, status, pushed_at, started_at, finished_at, wait_ms, duration_ms, memory_peak, hostname, pid, exception_class')
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        foreach ($items as &$item) {
            $item['status_text'] = QueueMonitorModel::STATUS_TEXT[$item['status']] ?? $item['status'];
        }
        unset($item);

        return self::createReturn(true, [
            'items'       => $items,
            'page'        => $page,
            'limit'       => $limit,
            'total_items' => intval($total),
            'total_pages' => intval(ceil($total / max(1, $limit))),
        ]);
    }

    /**
     * 执行记录详情（含同一任务链路）
     *
     * @param int $id 执行记录ID
     * @return array
     */
    public function getMonitorDetail(int $id): array
    {
        $row = Db::name(QueueMonitorService::table())->where('id', $id)->find();
        if (!$row) {
            return self::createReturn(false, null, '执行记录不存在');
        }
        $row['status_text'] = QueueMonitorModel::STATUS_TEXT[$row['status']] ?? $row['status'];
        $chain = [];
        if (!empty($row['monitor_uuid'])) {
            $chain = Db::name(QueueMonitorService::table())
                ->where('monitor_uuid', $row['monitor_uuid'])
                ->field('id, queue_job_id, attempt, status, started_at, finished_at, wait_ms, duration_ms, hostname, pid, exception_class')
                ->order('id', 'asc')
                ->limit(100)
                ->select()
                ->toArray();
            foreach ($chain as &$item) {
                $item['status_text'] = QueueMonitorModel::STATUS_TEXT[$item['status']] ?? $item['status'];
            }
            unset($item);
        }

        return self::createReturn(true, ['item' => $row, 'chain' => $chain]);
    }

    /**
     * 筛选项：近 7 天出现过的队列、任务类，以及状态枚举
     *
     * @return array
     */
    public function getFilterOptions(): array
    {
        $sinceMs = (time() - 7 * 86400) * 1000;
        $queues = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->group('queue')
            ->column('queue');
        $jobNames = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->group('job_name')
            ->column('job_name');
        sort($queues);
        sort($jobNames);
        $statuses = [];
        foreach (QueueMonitorModel::STATUS_TEXT as $value => $label) {
            $statuses[] = ['value' => $value, 'label' => $label];
        }

        return [
            'queues'    => array_values($queues),
            'job_names' => array_values($jobNames),
            'statuses'  => $statuses,
        ];
    }

    /**
     * 计算耗时百分位（取最近 P95_SAMPLE_LIMIT 条已结束记录）
     *
     * @param int $sinceMs 起始毫秒
     * @param string|null $queue 队列名
     * @param string|null $jobName 任务类名
     * @param int $percent 百分位
     * @return int|null
     */
    public function percentile(int $sinceMs, ?string $queue, ?string $jobName, int $percent): ?int
    {
        $query = Db::name(QueueMonitorService::table())
            ->where('started_at', '>=', $sinceMs)
            ->whereNotIn('status', [QueueMonitorModel::STATUS_RUNNING, QueueMonitorModel::STATUS_LOST]);
        if ($queue !== null) {
            $query->where('queue', $queue);
        }
        if ($jobName !== null) {
            $query->where('job_name', $jobName);
        }
        $values = $query->order('id', 'desc')->limit(self::P95_SAMPLE_LIMIT)->column('duration_ms');

        return self::calcPercentile(array_map('intval', $values), $percent);
    }

    /**
     * 百分位计算（nearest-rank）
     *
     * @param int[] $values 样本
     * @param int $percent 百分位
     * @return int|null
     */
    public static function calcPercentile(array $values, int $percent): ?int
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $rank = (int)ceil($percent / 100 * $count);

        return $values[max(0, min($count - 1, $rank - 1))];
    }

    /**
     * 空的队列状态行
     *
     * @param string $queue 队列名
     * @return array
     */
    private static function emptyQueueRow(string $queue): array
    {
        return [
            'queue'            => $queue,
            'pending'          => 0,
            'delayed'          => 0,
            'reserved'         => 0,
            'stale'            => 0,
            'max_wait_seconds' => 0,
            'failed'           => 0,
        ];
    }

    /**
     * 数值保留 1 位小数，空值返回 null
     *
     * @param mixed $value 数值
     * @return float|null
     */
    private static function roundOrNull($value): ?float
    {
        return $value === null ? null : round(floatval($value), 1);
    }
}
