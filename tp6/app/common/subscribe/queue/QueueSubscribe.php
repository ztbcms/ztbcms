<?php
/**
 * Author: jayinton
 */

namespace app\common\subscribe\queue;

use think\console\Input;
use think\Event;
use think\facade\App;
use think\facade\Console;
use think\facade\Log;
use think\queue\event\JobExceptionOccurred;
use think\queue\event\JobFailed;
use think\queue\event\JobProcessed;
use think\queue\event\JobProcessing;
use think\queue\event\WorkerStopping;

/**
 * 队列时间订阅
 */
class QueueSubscribe
{
    /**
     * 开始执行
     */
    public function onJobProcessing(JobProcessing $event)
    {
//        Log::info($event->job->getJobId().' starting');
    }

    /**
     * 执行完成(成功)
     */
    public function onJobProcessed(JobProcessed $event)
    {
//        Log::info($event->job->getJobId().' success');
    }

    /**
     * 执行失败
     */
    public function onJobFailed(JobFailed $event)
    {
//        Log::info($event->job->getJobId().' faild');
    }

    /**
     * 任务异常
     */
    public function onJobExceptionOccurred(JobExceptionOccurred $event)
    {
//        Log::info($event->job->getJobId().' Excepiton');
    }

    /**
     * 停止
     */
    public function onWorkerStopping(WorkerStopping $event)
    {
//        Log::info('workder stop, status='.$event->status);
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