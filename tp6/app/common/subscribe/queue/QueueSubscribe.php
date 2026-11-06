<?php
/**
 * Author: jayinton
 */

namespace app\common\subscribe\queue;

use app\common\model\queue\QueueMonitorModel;
use app\common\service\queue\QueueMonitorService;
use Throwable;
use think\Event;
use think\queue\event\JobExceptionOccurred;
use think\queue\event\JobFailed;
use think\queue\event\JobProcessed;
use think\queue\event\JobProcessing;
use think\queue\event\WorkerStopping;
use think\queue\Job;

/**
 * 队列时间订阅
 *
 * 负责队列监控执行记录的写入（见 QueueMonitorService），仅在命令行（worker）进程中生效。
 */
class QueueSubscribe
{
    /**
     * WorkerStopping 状态：任务执行超时被强制终止（Worker::kill(1)）
     */
    const WORKER_STATUS_TIMEOUT = 1;

    /**
     * 当前执行中的任务监控上下文
     * ['id' => 执行记录ID, 'job' => Job, 'started_at' => 开始毫秒]
     *
     * @var array|null
     */
    protected $current = null;

    /**
     * @var QueueMonitorService
     */
    protected $monitor;

    /**
     * 构造
     */
    public function __construct()
    {
        $this->monitor = new QueueMonitorService();
    }

    /**
     * 开始执行
     */
    public function onJobProcessing(JobProcessing $event)
    {
//        Log::info($event->job->getJobId().' starting');
        $this->current = null;
        $startedAt = QueueMonitorService::nowMs();
        $id = $this->monitor->recordStart($event->job, (string)$event->connection);
        if ($id > 0) {
            $this->current = ['id' => $id, 'job' => $event->job, 'started_at' => $startedAt];
        }
    }

    /**
     * 执行完成(成功)
     */
    public function onJobProcessed(JobProcessed $event)
    {
//        Log::info($event->job->getJobId().' success');
        $this->finishCurrent($event->job, QueueMonitorService::resolveProcessedStatus($event->job));
    }

    /**
     * 执行失败
     */
    public function onJobFailed(JobFailed $event)
    {
//        Log::info($event->job->getJobId().' faild');
        $this->finishCurrent($event->job, QueueMonitorModel::STATUS_FAILED, $event->exception, false);
    }

    /**
     * 任务异常
     */
    public function onJobExceptionOccurred(JobExceptionOccurred $event)
    {
//        Log::info($event->job->getJobId().' Excepiton');
        $this->finishCurrent($event->job, QueueMonitorService::resolveExceptionStatus($event->job), $event->exception);
    }

    /**
     * 停止
     */
    public function onWorkerStopping(WorkerStopping $event)
    {
//        Log::info('workder stop, status='.$event->status);
        if (!$this->current) {
            return;
        }
        $current = $this->current;
        $this->current = null;
        if (intval($event->status) === self::WORKER_STATUS_TIMEOUT) {
            $this->monitor->markInterrupted(
                $current['id'],
                QueueMonitorModel::STATUS_TIMEOUT,
                $current['started_at'],
                '任务执行超时，worker 被强制终止'
            );
        } else {
            $this->monitor->markInterrupted(
                $current['id'],
                QueueMonitorModel::STATUS_INTERRUPTED,
                $current['started_at'],
                '任务执行中 worker 退出，status=' . intval($event->status)
            );
        }
    }

    /**
     * 结束当前任务的执行记录
     *
     * @param Job $job 队列任务
     * @param string $status 结束状态
     * @param mixed $exception 异常
     * @param bool $release 是否释放当前上下文（JobFailed 之后可能还有 JobExceptionOccurred）
     * @return void
     */
    protected function finishCurrent(Job $job, string $status, $exception = null, bool $release = true): void
    {
        if (!$this->current || $this->current['job'] !== $job) {
            return;
        }
        $current = $this->current;
        if ($release) {
            $this->current = null;
        }
        $this->monitor->recordFinish(
            $current['id'],
            $status,
            $current['started_at'],
            $exception instanceof Throwable ? $exception : null
        );
    }

    /**
     * 自定义订阅
     *
     * @param  Event  $event
     */
    public function subscribe(Event $event)
    {
        if (app()->runningInConsole()) {
            $event->listen(JobProcessing::class, [$this, 'onJobProcessing']);
            $event->listen(JobProcessed::class, [$this, 'onJobProcessed']);
            $event->listen(JobFailed::class, [$this, 'onJobFailed']);
            $event->listen(JobExceptionOccurred::class, [$this, 'onJobExceptionOccurred']);
            $event->listen(WorkerStopping::class, [$this, 'onWorkerStopping']);
        }
    }
}
