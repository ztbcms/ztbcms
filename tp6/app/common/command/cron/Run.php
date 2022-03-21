<?php
/**
 * Author: jayinton
 */

namespace app\common\command\cron;

use app\common\model\cron\CronConfigModel;
use app\common\model\cron\CronModel;
use app\common\model\cron\CronSchedulingLogModel;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 计划任务：启动
 */
class Run extends Command
{
    protected function configure()
    {
        $this->setName('cron:run')
            ->addOption('progress', 'p', Option::VALUE_OPTIONAL, '进度显示:1显示')
            ->setDescription('启动计划任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $cronConfig = CronConfigModel::column('value', 'key');
        //判断计划任务是否关闭
        if ($cronConfig[CronConfigModel::KEY_ENABLE_CRON] != CronConfigModel::ENABLE_YES) {
            $output->error("计划任务功能已关闭");
            return;
        }
        // 锁定自动执行
        $lockfile = runtime_path() . 'cron.lock';
        if (file_exists($lockfile)) {
            $output->error("已存在文件锁 cron.lock ");
            return;
        } else {
            //设置指定文件的访问和修改时间
            touch($lockfile);
        }
        $schedulingLogModel = new CronSchedulingLogModel();
        $cron = $this->getNext(0);
        $errorCount = 0;
        $cronCount = 0;
        $startTime = time();
        while ($cron) {
            list($day, $hour, $minute) = explode('-', $cron->loop_daytime);
            //获取下一次执行时间
            $nexttime = CronModel::getNextTime($cron->loop_type, $day, $hour, $minute);
            //更新计划任务的下次执行时间
            CronModel::where('cron_id', $cron->cron_id)->update([
                'modified_time' => time(),
                'next_time' => $nexttime
            ]);
            // 执行
            $this->debugInfo('Processing:'.$cron->cron_file);
            $res = $cron->runAction();
            $this->debugInfo('Processed :'.$cron->cron_file);
            if (!$res['status']) {
                $errorCount++;
            }
            $cronCount++;
            // 下一个
            $cron = $this->getNext($cron->cron_id);
        }
        //记录执行日志
        $endEime = time();
        $schedulingLogModel->start_time = $startTime;
        $schedulingLogModel->end_time = $endEime;
        $schedulingLogModel->use_time = ($endEime - $startTime) <= 0 ? 1 : ($endEime - $startTime);
        $schedulingLogModel->error_count = $errorCount;
        $schedulingLogModel->cron_count = $cronCount;
        $schedulingLogModel->save();
        // 解除锁定
        unlink($lockfile);
        $output->writeln(json_encode([
            'used_time' => $schedulingLogModel->use_time,
            'msg' => 'Cron status: finish',
            'total_amount' => $cronCount,
            'error_amount' => $errorCount,
        ]));
    }

    function getNext($last_cron_id)
    {
        return CronModel::where([
            ['isopen', '=', CronModel::OPEN_STATUS_ON],
            ['next_time', '<', time()],
            ['cron_id', '>', $last_cron_id],
        ])->order('next_time', 'ASC')->find();
    }

    function debugInfo($msg)
    {
        if ($this->input->getOption('progress') == '1') {
            $this->output->info($msg);
        }
    }
}