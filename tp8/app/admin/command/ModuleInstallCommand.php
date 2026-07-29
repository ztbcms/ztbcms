<?php

namespace app\admin\command;

use app\admin\service\ModuleService;
use app\admin\service\UserOperateLogService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 模块安装命令
 * 用法: php think module:install <module_name>
 */
class ModuleInstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('module:install')
            ->addArgument('module_name', Argument::REQUIRED, '模块目录名称')
            ->setDescription('安装指定模块');
    }

    protected function execute(Input $input, Output $output)
    {
        $moduleName = $input->getArgument('module_name');
        $output->writeln("正在安装模块: {$moduleName} ...");

        UserOperateLogService::addUserOperateLog([
            'user_id'     => 0,
            'user_name'   => 'cli',
            'ip'          => '127.0.0.1',
            'source_type' => 'admin_module',
            'source'      => $moduleName,
            'content'     => '安装模块 ' . $moduleName,
        ]);

        $service = new ModuleService();
        $res = $service->install($moduleName);

        if ($res['status']) {
            $output->writeln("<info>{$res['msg']}</info>");
            return 0;
        } else {
            $output->writeln("<error>{$res['msg']}</error>");
            return 1;
        }
    }
}
