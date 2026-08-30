<?php
/**
 * Author: jayinton
 */

namespace app\common\command\cron;

use app\common\model\cron\CronModel;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 计划任务：执行指定任务
 */
class Exec extends Command
{
    protected function configure()
    {
        $this->setName('cron:exec')
            ->addOption('id', null, Option::VALUE_OPTIONAL, '计划任务ID')
            ->addOption('class', null, Option::VALUE_OPTIONAL, '计划任务类名')
            ->setDescription('执行指定计划任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $cron_id = trim($input->getOption('id'));
        $class = trim($input->getOption('class'));
        if(empty($cron_id) && empty($class)){
            $output->error("参数异常: 请指定 id 或 class");
            return;
        }
        if (!empty($cron_id)) {
            $_cron = CronModel::where('cron_id', $cron_id)->find();
            if(!$_cron){
                $output->error('找不到计划任务ID:' . $cron_id);
                return;
            }
            $class = trim($_cron->cron_file);
        }
        if(!class_exists($class)){
            $output->error('找不到计划任务:' . $class);
            return;
        }
        $cron = new $class;
        $cron->run(0);
        $output->writeln('执行完成');
    }

}