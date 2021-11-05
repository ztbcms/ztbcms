<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\queue;

use think\queue\Job;

/**
 * 队列任务基类
 * 所有任务都继承这个类
 */
abstract class BaseQueueJob
{
    /**
     * 任务执行
     * 如果任务执行成功后,务必删除任务，否则任务会重复执行，直到达到最大重试次数后失败后，执行failed方法；也可以重新发布这个任务
     *
     * @param  Job  $job
     * @param  array  $data
     *
     * @return mixed
     */
    abstract function fire(Job $job, $data);

    /**
     * 任务达到最大重试次数后，失败回调
     *
     * @param  array  $data
     *
     * @return mixed
     */
    abstract function failed($data);
}