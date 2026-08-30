<?php

namespace app\admin\command;

use Throwable;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\console\Table;
use app\admin\service\ModuleService;
use app\admin\service\UserOperateLogService;

/**
 * 模块菜单同步命令
 * 用法 php think module:menu-sync <module_name> [--dry-run] [--force]
 */
class ModuleMenuSyncCommand extends Command
{
    /**
     * 配置模块菜单同步命令
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('module:menu-sync')
            ->addArgument('module_name', Argument::REQUIRED, '模块目录名称')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只预览菜单差异，不写数据库')
            ->addOption('force', 'f', Option::VALUE_NONE, '跳过确认直接同步')
            ->setDescription('同步已安装模块的 Menu.php 菜单配置');
    }

    /**
     * 执行模块菜单同步命令
     *
     * @param Input $input 命令输入
     * @param Output $output 命令输出
     * @return int
     */
    protected function execute(Input $input, Output $output)
    {
        $moduleName = strtolower(trim((string) $input->getArgument('module_name')));
        $dryRun = (bool) $input->getOption('dry-run');
        $force = (bool) $input->getOption('force');

        if (!$input->isInteractive() && !$dryRun && !$force) {
            $output->writeln('<error>非交互环境必须显式传入 --dry-run 或 --force</error>');
            return 1;
        }

        $service = new ModuleService();
        $analysis = $service->analyzeMenuSync($moduleName);
        if (is_array($analysis['data'] ?? null) && isset($analysis['data']['items'])) {
            $this->renderPlan($output, $analysis['data']);
            $this->renderSummary($output, $analysis['data']['summary']);
        }
        if (!$analysis['status']) {
            $output->writeln('<error>'.$analysis['msg'].'</error>');
            return 1;
        }

        $plan = $analysis['data'];
        if ($dryRun) {
            $output->writeln('<info>仅预览菜单差异，未写入数据库</info>');
            return 0;
        }

        if ($plan['summary']['executable'] === 0) {
            if ($plan['summary']['stale'] > 0) {
                $output->writeln("<warning>没有可执行变更，存在 {$plan['summary']['stale']} 条遗留节点</warning>");
            } else {
                $output->writeln('<info>菜单配置已同步</info>');
            }
            return 0;
        }

        if (!$force) {
            $output->writeln("<warning>即将同步模块 {$moduleName} 的菜单配置</warning>");
            $confirm = $output->ask($input, "确认同步模块 {$moduleName} 菜单吗？(yes/no)", 'no');
            if (strtolower((string) $confirm) !== 'yes') {
                $output->writeln('已取消菜单同步');
                return 0;
            }
        }

        $result = $service->syncMenu($moduleName, $plan['fingerprint']);
        $resultData = is_array($result['data'] ?? null) ? $result['data'] : [];
        if (($resultData['reason'] ?? '') === 'fingerprint_changed' && isset($resultData['items'])) {
            $output->writeln('<warning>菜单数据或配置已变化，最新差异如下</warning>');
            $this->renderPlan($output, $resultData);
            $this->renderSummary($output, $resultData['summary']);
        }

        if (!empty($resultData['data_committed'])) {
            if (!$this->writeOperateLog($output, $moduleName, $resultData)) {
                return 1;
            }
        }

        if (!$result['status']) {
            $output->writeln('<error>'.$result['msg'].'</error>');
            return 1;
        }

        $output->writeln('<info>'.$result['msg'].'</info>');
        return 0;
    }

    /**
     * 输出菜单同步计划
     *
     * @param Output $output 命令输出
     * @param array $plan 同步计划
     * @return void
     */
    private function renderPlan(Output $output, array $plan): void
    {
        $labels = [
            'add' => 'ADD',
            'update' => 'UPDATE',
            'move' => 'MOVE',
            'stale' => 'STALE',
            'error' => 'ERROR',
        ];
        $table = new Table();
        $table->setHeader(['类型', '菜单 ID', '菜单路径', '路由', '变化']);
        $hasRows = false;
        foreach ($labels as $type => $label) {
            foreach ($plan['items'][$type] ?? [] as $item) {
                $table->addRow([
                    $label,
                    $item['menu_id'] === null ? '-' : (string) $item['menu_id'],
                    $item['path'] ?? '',
                    $item['route'] ?? '',
                    $this->formatChanges($item),
                ]);
                $hasRows = true;
            }
        }
        if ($hasRows) {
            $output->writeln($table->render());
        }
    }

    /**
     * 输出菜单同步汇总
     *
     * @param Output $output 命令输出
     * @param array $summary 同步汇总
     * @return void
     */
    private function renderSummary(Output $output, array $summary): void
    {
        $output->writeln(sprintf(
            '汇总 ADD=%d UPDATE=%d MOVE=%d STALE=%d UNCHANGED=%d ERROR=%d',
            $summary['add'] ?? 0,
            $summary['update'] ?? 0,
            $summary['move'] ?? 0,
            $summary['stale'] ?? 0,
            $summary['unchanged'] ?? 0,
            $summary['error'] ?? 0
        ));
        if (($summary['add'] ?? 0) > 0) {
            $output->writeln('<warning>新增菜单或权限节点不会自动授予普通角色，请同步后重新授权</warning>');
        }
        if (($summary['move'] ?? 0) > 0) {
            $output->writeln('<warning>存在菜单移动，请复核普通角色是否拥有目标父菜单权限</warning>');
        }
    }

    /**
     * 格式化计划项变化内容
     *
     * @param array $item 计划项
     * @return string
     */
    private function formatChanges(array $item): string
    {
        $parts = [];
        foreach ($item['changes'] ?? [] as $field => $change) {
            $oldValue = $change['old'] === null ? 'NULL' : (string) $change['old'];
            $newValue = $change['new'] === null ? 'NULL' : (string) $change['new'];
            $parts[] = "{$field}: {$oldValue} => {$newValue}";
        }
        if (!empty($item['message'])) {
            $parts[] = $item['message'];
        }
        return empty($parts) ? '-' : implode('; ', $parts);
    }

    /**
     * 写入 CLI 菜单同步操作日志
     *
     * @param Output $output 命令输出
     * @param string $moduleName 模块目录名称
     * @param array $resultData 同步结果
     * @return bool
     */
    private function writeOperateLog(Output $output, string $moduleName, array $resultData): bool
    {
        $summary = $resultData['summary'] ?? [];
        $content = sprintf(
            '同步模块菜单 %s，新增 %d，更新 %d，移动 %d，遗留 %d',
            $moduleName,
            $summary['add'] ?? 0,
            $summary['update'] ?? 0,
            $summary['move'] ?? 0,
            $summary['stale'] ?? 0
        );
        if (empty($resultData['cache_cleared'])) {
            $content .= '，RBAC 缓存清理失败';
        }

        try {
            UserOperateLogService::addUserOperateLog([
                'user_id' => 0,
                'user_name' => 'cli',
                'ip' => '127.0.0.1',
                'source_type' => 'admin_module',
                'source' => $moduleName,
                'content' => $content,
            ]);
            return true;
        } catch (Throwable $e) {
            $output->writeln('<error>菜单已写入，但操作日志记录失败：'.$e->getMessage().'</error>');
            return false;
        }
    }
}
