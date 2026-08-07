<?php

namespace app\admin\command;

use app\admin\service\ModuleService;
use app\admin\service\UserOperateLogService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * 模块卸载命令
 * 用法: php think module:uninstall <module_name> [--force]
 */
class ModuleUninstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('module:uninstall')
            ->addArgument('module_name', Argument::REQUIRED, '模块目录名称')
            ->addOption('force', 'f', Option::VALUE_NONE, '跳过确认直接卸载')
            ->setDescription('卸载指定模块');
    }

    protected function execute(Input $input, Output $output)
    {
        $moduleName = $input->getArgument('module_name');

        // 交互确认
        if (!$input->getOption('force')) {
            $output->writeln("<warning>即将卸载模块: {$moduleName}</warning>");
            $confirm = $output->ask($input, "确认要卸载模块 {$moduleName} 吗？(yes/no)", 'no');
            if (strtolower($confirm) !== 'yes') {
                $output->writeln('已取消卸载');
                return 0;
            }
        }

        $output->writeln("正在卸载模块: {$moduleName} ...");

        UserOperateLogService::addUserOperateLog([
            'user_id'     => 0,
            'user_name'   => 'cli',
            'ip'          => '127.0.0.1',
            'source_type' => 'admin_module',
            'source'      => $moduleName,
            'content'     => '卸载模块 ' . $moduleName,
        ]);

        $service = new ModuleService();
        $res = $service->uninstall($moduleName);

        if ($res['status']) {
            $output->writeln("<info>{$res['msg']}</info>");
            return 0;
        } else {
            $output->writeln("<error>{$res['msg']}</error>");
            return 1;
        }
    }
}
