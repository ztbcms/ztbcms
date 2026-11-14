<?php
/**
 * Author: jayinton
 */

namespace app\common\command\cron;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use app\common\service\cron\CronService;

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
        $showProgress = $input->getOption('progress') == '1';
        $res = CronService::runScheduler([
            'check_secret' => false,
            'unlimited_time' => false,
            'on_progress' => function (string $message) use ($showProgress, $output): void {
                if ($showProgress) {
                    $output->info($message);
                }
            },
        ]);

        $data = $res['data'] ?? [];
        $reason = $data['reason'] ?? '';
        if ($reason === CronService::STATUS_DISABLED) {
            $output->error('计划任务功能已关闭');
            return;
        }
        if ($reason === CronService::STATUS_LOCKED) {
            $output->error('已存在文件锁 cron.lock ');
            return;
        }

        $output->writeln(json_encode([
            'used_time' => $data['used_time'] ?? 1,
            'msg' => 'Cron status: finish',
            'total_amount' => $data['total_amount'] ?? 0,
            'error_amount' => $data['error_amount'] ?? 0,
        ]));
    }
}
