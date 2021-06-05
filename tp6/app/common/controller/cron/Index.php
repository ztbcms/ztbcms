<?php

namespace app\common\controller\cron;

use app\BaseController;
use app\common\model\cron\CronConfigModel;
use app\common\model\cron\CronModel;
use app\common\model\cron\CronSchedulingLogModel;

class Index extends BaseController
{
    private $errorCount = 0;
    private $cronCount = 0;

    /**
     * 计划任调度入口
     *
     * @param  string  $cron_secret_key
     *
     * @return \think\response\Json
     */
    function index($cron_secret_key = '')
    {
        $cronConfig = CronConfigModel::column('value', 'key');
        //判断计划任务是否关闭
        if ($cronConfig[CronConfigModel::KEY_ENABLE_CRON] != CronConfigModel::ENABLE_YES) {
            return json(['used_time' => 0, 'msg' => 'Cron status: stop']);
        }
        // 密钥检测
        if ($cron_secret_key != $cronConfig[CronConfigModel::KEY_ENABLE_SECRET_KEY]) {
            return json(['used_time' => 0, 'msg' => 'Secret key invalidated']);
        }
        $start_at = $end_at = time();
        // 锁定自动执行
        $lockfile = runtime_path().'cron.lock';
        if (file_exists($lockfile)) {
            return json(['used_time' => 0, 'msg' => 'Cron is Locked']);
        } else {
            //设置指定文件的访问和修改时间
            touch($lockfile);
        }
        // 不限制执行时间
        set_time_limit(0);
        ignore_user_abort(true);
        $schedulingLogModel = new CronSchedulingLogModel();
        //日志信息
        $startTime = time();
        //执行计划任务
        $this->runCron();
        //记录执行日志
        $endEime = time();
        $schedulingLogModel->start_time = $startTime;
        $schedulingLogModel->end_time = $endEime;
        $schedulingLogModel->use_time = (int) ($endEime - $startTime);
        $schedulingLogModel->error_count = $this->errorCount;
        $schedulingLogModel->cron_count = $this->cronCount;
        //记录执行日志
        $schedulingLogModel->save();
        // 解除锁定
        unlink($lockfile);
        $end_at = time();
        $used_time = $end_at - $start_at;
        return json(['used_time' => $used_time, 'msg' => 'Cron status: finish']);
    }

    /**
     * 执行计划任务
     *
     */
    private function runCron()
    {
        $_time = time();
        $cron = CronModel::where('isopen', CronModel::OPEN_STATUS_ON)
            ->where('next_time', '<', $_time)
            ->order('next_time', 'ASC')->findOrEmpty();
        //检测是否还有需要执行的任务
        if ($cron->isEmpty()) {
            return;
        }
        //记录cron数量
        $this->cronCount += 1;
        list($day, $hour, $minute) = explode('-', $cron['loop_daytime']);
        //获取下一次执行时间
        $nexttime = CronModel::getNextTime($cron->loop_type, $day, $hour, $minute);
        //更新计划任务的下次执行时间
        CronModel::where('cron_id', $cron->cron_id)->update([
            'modified_time' => $_time,
            'next_time'     => $nexttime
        ]);
        // 执行
        $cron->runAction();
        // 下一个
        $this->runCron();
    }
}