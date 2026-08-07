<?php
/**
 * Author: jayinton
 */

namespace app\common\command\cron;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 计划任务：清理
 */
class Clean extends Command
{
    protected function configure()
    {
        $this->setName('cron:clean')
            ->setDescription('计划任务清理缓存');
    }

    protected function execute(Input $input, Output $output)
    {
        $lockfile = runtime_path() . 'cron.lock';
        if (file_exists($lockfile)) {
            unlink($lockfile);
            $this->output->writeln('已清理文件：cron.lock');
        }
        $this->output->writeln('已清理完成');
    }
}