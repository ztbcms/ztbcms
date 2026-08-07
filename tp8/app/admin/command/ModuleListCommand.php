<?php

namespace app\admin\command;

use app\admin\service\ModuleService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\Table;

/**
 * 模块列表命令
 * 用法: php think module:list
 */
class ModuleListCommand extends Command
{
    protected function configure()
    {
        $this->setName('module:list')
            ->setDescription('列出所有本地模块及安装状态');
    }

    protected function execute(Input $input, Output $output)
    {
        $service = new ModuleService();
        $res = $service->getLocalModuleList();

        if (!$res['status']) {
            $output->writeln("<error>获取模块列表失败</error>");
            return 1;
        }

        $moduleList = $res['data'];
        if (empty($moduleList)) {
            $output->writeln('<info>没有发现本地模块</info>');
            return 0;
        }

        $table = new Table();
        $table->setHeader(['模块目录', '模块名称', '版本', '安装时间']);

        foreach ($moduleList as $module) {
            $table->addRow([
                $module['module_dir'] ?? strtolower($module['module'] ?? ''),
                $module['modulename'] ?? '',
                $module['version'] ?? '',
                !empty($module['install_time']) ? $module['install_time'] : '未安装',
            ]);
        }

        $this->output->writeln($table->render());
        return 0;
    }
}
