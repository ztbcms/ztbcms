<?php
/**
 * Author: jayinton
 */

namespace app\common\service\queue;

use app\common\libs\queue\connector\MonitorDatabase;
use app\common\service\BaseService;
use RuntimeException;
use Throwable;
use think\facade\Db;
use think\facade\Queue;

/**
 * 队列监控-失败任务管理
 */
class QueueFailedJobService extends BaseService
{
    /**
     * 批量操作单次最大数量
     */
    const BATCH_LIMIT = 100;

    /**
     * 人工重试时写入 payload 的来源字段
     */
    const PAYLOAD_RETRIED_FROM = 'retried_from_failed_id';

    /**
     * 失败任务列表
     *
     * @param array $filters 筛选条件（queue、keyword、start_date、end_date）
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getList(array $filters, int $page, int $limit): array
    {
        $query = Db::name(QueueMonitorStatService::failedTable());
        if (!empty($filters['queue'])) {
            $query->where('queue', (string)$filters['queue']);
        }
        $keyword = trim((string)($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $query->whereLike('payload', '%' . addcslashes($keyword, '%_\\') . '%');
        }
        if (!empty($filters['start_date'])) {
            $start = strtotime((string)$filters['start_date'] . ' 00:00:00');
            if ($start) {
                $query->where('fail_time', '>=', date('Y-m-d H:i:s', $start));
            }
        }
        if (!empty($filters['end_date'])) {
            $end = strtotime((string)$filters['end_date'] . ' 23:59:59');
            if ($end) {
                $query->where('fail_time', '<=', date('Y-m-d H:i:s', $end));
            }
        }

        $total = (clone $query)->count();
        $rows = $query
            ->fieldRaw('id, connection, queue, fail_time, SUBSTR(payload, 1, 2000) AS payload_head, SUBSTR(exception, 1, 500) AS exception_head')
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'                => intval($row['id']),
                'connection'        => (string)$row['connection'],
                'queue'             => (string)$row['queue'],
                'fail_time'         => (string)$row['fail_time'],
                'job_name'          => self::parseJobName((string)$row['payload_head']),
                'exception_summary' => self::summarizeException((string)$row['exception_head']),
            ];
        }
        $queues = Db::name(QueueMonitorStatService::failedTable())->group('queue')->column('queue');
        sort($queues);

        return self::createReturn(true, [
            'items'       => $items,
            'page'        => $page,
            'limit'       => $limit,
            'total_items' => intval($total),
            'total_pages' => intval(ceil($total / max(1, $limit))),
            'queues'      => array_values($queues),
        ]);
    }

    /**
     * 失败任务详情
     *
     * @param int $id 失败任务ID
     * @return array
     */
    public function getDetail(int $id): array
    {
        $row = Db::name(QueueMonitorStatService::failedTable())->where('id', $id)->find();
        if (!$row) {
            return self::createReturn(false, null, '失败任务不存在');
        }
        $payload = json_decode((string)$row['payload'], true);
        $row['job_name'] = is_array($payload) ? QueueMonitorService::resolveJobName($payload) : self::parseJobName((string)$row['payload']);
        $row['monitor_uuid'] = is_array($payload) ? (string)($payload[MonitorDatabase::PAYLOAD_UUID] ?? '') : '';

        return self::createReturn(true, ['item' => $row]);
    }

    /**
     * 重试失败任务：原 payload 重新入队（重新生成 monitor_uuid），并删除失败记录
     *
     * @param array $ids 失败任务ID
     * @return array
     */
    public function retry(array $ids): array
    {
        $ids = self::normalizeIds($ids);
        if (!$ids) {
            return self::createReturn(false, null, '请选择要重试的失败任务');
        }
        if (count($ids) > self::BATCH_LIMIT) {
            return self::createReturn(false, null, '单次最多重试 ' . self::BATCH_LIMIT . ' 条');
        }
        $success = 0;
        $errors = [];
        foreach ($ids as $id) {
            try {
                $this->retryOne($id);
                $success++;
            } catch (Throwable $e) {
                $errors[] = ['id' => $id, 'msg' => $e->getMessage()];
            }
        }
        $msg = '重试成功 ' . $success . ' 条' . ($errors ? '，失败 ' . count($errors) . ' 条' : '');

        return self::createReturn($success > 0 || !$errors, ['success' => $success, 'errors' => $errors], $msg);
    }

    /**
     * 删除失败任务
     *
     * @param array $ids 失败任务ID
     * @return array
     */
    public function delete(array $ids): array
    {
        $ids = self::normalizeIds($ids);
        if (!$ids) {
            return self::createReturn(false, null, '请选择要删除的失败任务');
        }
        if (count($ids) > self::BATCH_LIMIT) {
            return self::createReturn(false, null, '单次最多删除 ' . self::BATCH_LIMIT . ' 条');
        }
        $deleted = Db::name(QueueMonitorStatService::failedTable())->whereIn('id', $ids)->delete();

        return self::createReturn(true, ['deleted' => intval($deleted)], '已删除 ' . intval($deleted) . ' 条');
    }

    /**
     * 重试单个失败任务
     *
     * @param int $id 失败任务ID
     * @return void
     * @throws RuntimeException 任务不存在或无法重试
     */
    protected function retryOne(int $id): void
    {
        Db::transaction(function () use ($id) {
            $row = Db::name(QueueMonitorStatService::failedTable())->where('id', $id)->lock(true)->find();
            if (!$row) {
                throw new RuntimeException('失败任务不存在或已被处理');
            }
            $connection = (string)$row['connection'];
            if (!config('queue.connections.' . $connection)) {
                throw new RuntimeException('队列连接 ' . $connection . ' 未配置');
            }
            $payload = json_decode((string)$row['payload'], true);
            if (!is_array($payload) || empty($payload['job'])) {
                throw new RuntimeException('任务 payload 无法解析');
            }
            $payload = MonitorDatabase::withMonitorFields($payload, true);
            $payload[self::PAYLOAD_RETRIED_FROM] = $id;
            $raw = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($raw === false) {
                throw new RuntimeException('任务 payload 编码失败');
            }
            Queue::connection($connection)->pushRaw($raw, (string)$row['queue']);
            Db::name(QueueMonitorStatService::failedTable())->where('id', $id)->delete();
        });
    }

    /**
     * 从（可能被截断的）payload 文本中解析任务类名
     *
     * @param string $payload payload 文本
     * @return string
     */
    public static function parseJobName(string $payload): string
    {
        $decoded = json_decode($payload, true);
        if (is_array($decoded)) {
            return QueueMonitorService::resolveJobName($decoded);
        }
        if (preg_match('/"commandName"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $payload, $m)) {
            return (string)json_decode('"' . $m[1] . '"');
        }
        if (preg_match('/"job"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $payload, $m)) {
            $job = (string)json_decode('"' . $m[1] . '"');
            $pos = strpos($job, '@');

            return $pos === false ? $job : substr($job, 0, $pos);
        }

        return '';
    }

    /**
     * 异常摘要：取首行
     *
     * @param string $exception 异常文本
     * @return string
     */
    public static function summarizeException(string $exception): string
    {
        $line = strtok(str_replace("\r", '', $exception), "\n");

        return mb_substr((string)$line, 0, 200);
    }

    /**
     * 归一化 ID 列表
     *
     * @param array $ids ID 列表
     * @return int[]
     */
    private static function normalizeIds(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (is_numeric($id) && intval($id) > 0) {
                $result[] = intval($id);
            }
        }

        return array_values(array_unique($result));
    }
}
