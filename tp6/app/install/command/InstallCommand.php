<?php

namespace app\install\command;

use app\install\service\InstallService;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('ztbcms:install')
            ->setDescription('安装 ZTBCMS 系统')
            ->addOption('db_host', null, Option::VALUE_OPTIONAL, '数据库地址', '127.0.0.1')
            ->addOption('db_port', null, Option::VALUE_OPTIONAL, '数据库端口', '3306')
            ->addOption('db_name', null, Option::VALUE_OPTIONAL, '数据库名称')
            ->addOption('db_user', null, Option::VALUE_OPTIONAL, '数据库用户名', 'root')
            ->addOption('db_pwd', null, Option::VALUE_OPTIONAL, '数据库密码')
            ->addOption('db_prefix', null, Option::VALUE_OPTIONAL, '数据表前缀', 'ztb_')
            ->addOption('manager', null, Option::VALUE_OPTIONAL, '管理员账号', 'admin')
            ->addOption('manager_pwd', null, Option::VALUE_OPTIONAL, '管理员密码')
            ->addOption('manager_email', null, Option::VALUE_OPTIONAL, '管理员邮箱', 'admin@admin.com')
            ->addOption('sitename', null, Option::VALUE_OPTIONAL, '网站名称', 'ZTBCMS')
            ->addOption('siteurl', null, Option::VALUE_OPTIONAL, '网站完整URL', 'http://localhost')
            ->addOption('siteinfo', null, Option::VALUE_OPTIONAL, '网站简介', '全新的管理系统')
            ->addOption('sitekeywords', null, Option::VALUE_OPTIONAL, '网站关键词', 'ztbcms')
            ->addOption('force', 'f', Option::VALUE_NONE, '静默无交互安装')
            ->addOption('checkEnv', null, Option::VALUE_NONE, '仅检测环境依赖并输出报告，不执行后续安装');
    }

    protected function execute(Input $input, Output $output)
    {
        $isCheckEnv = $input->getOption('checkEnv');

        if (!$isCheckEnv && is_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'install.lock')) {
            $output->error('系统已安装！如需重新安装，请先删除 tp6/app/install/install.lock 文件。');
            return 1;
        }

        try {
            $isForce = $input->getOption('force');

            $output->writeln('欢迎使用 ZTBCMS 安装程序 (CLI)');

            // 环境检测
            $output->writeln('正在进行环境检测...');
            $installService = new InstallService();
            $envRes = $installService->checkEnvironment();

            foreach ($envRes['items'] as $item) {
                if (!$item['status']) {
                    $output->writeln(" - {$item['name']}: 需要 {$item['require']}，当前为 {$item['current']} [<error>不通过</error>]");
                } else {
                    $output->writeln(" - {$item['name']}: 需要 {$item['require']}，当前为 {$item['current']} [<info>通过</info>]");
                }
            }

            if (!$envRes['status']) {
                if (!$isForce) {
                    $output->writeln("<error>环境检测未通过，请处理后重试。</error>");
                    return 1;
                }
                $output->writeln("<warning>由于使用了 --force 参数，强制跳过环境检测进行安装！</warning>");
            } else {
                $output->writeln("<info>环境检测全部通过！</info>\n");
            }

            // 如果仅仅是为了检测环境
            if ($isCheckEnv) {
                return $envRes['status'] ? 0 : 1;
            }

            // 收集配置
            $config = [];
            $config['db_host'] = $this->getParam($input, $output, 'db_host', '数据库地址', $isForce, '127.0.0.1');
            $config['db_port'] = $this->getParam($input, $output, 'db_port', '数据库端口', $isForce, '3306');
            $config['db_name'] = $this->getParam($input, $output, 'db_name', '数据库名称', $isForce, '', true);
            $config['db_user'] = $this->getParam($input, $output, 'db_user', '数据库用户名', $isForce, 'root');
            $config['db_pwd'] = $this->getParam($input, $output, 'db_pwd', '数据库密码', $isForce, '', true);
            $config['db_prefix'] = $this->getParam($input, $output, 'db_prefix', '数据表前缀', $isForce, 'ztb_');

            $config['manager'] = $this->getParam($input, $output, 'manager', '超管账号', $isForce, 'admin');
            $config['manager_pwd'] = $this->getParam($input, $output, 'manager_pwd', '超管密码', $isForce, '', true);
            $config['manager_email'] = $this->getParam($input, $output, 'manager_email', '管理员邮箱', $isForce, 'admin@admin.com');

            $config['sitename'] = $this->getParam($input, $output, 'sitename', '网站名称', $isForce, 'ZTBCMS');
            $config['siteurl'] = $this->getParam($input, $output, 'siteurl', '网站完整URL', $isForce, 'http://localhost');
            $config['siteinfo'] = $this->getParam($input, $output, 'siteinfo', '网站简介', $isForce, '全新的管理系统');
            $config['sitekeywords'] = $this->getParam($input, $output, 'sitekeywords', '网站关键词', $isForce, 'ztbcms');
            $config['ip'] = '127.0.0.1'; // 命令行默认 IP

            $output->info('配置收集完毕，开始测试数据库链接...');

            $installService = new InstallService();
            $testRes = $installService->testDbConnection($config);
            if (!$testRes['status']) {
                $output->error($testRes['msg']);
                return 1;
            }
            $output->info('数据库链接成功，开始安装数据表...');

            $n = 0;
            while (true) {
                $res = $installService->executeInstallStep($config, $n);
                if (!$res['status']) {
                    $output->error('安装中断: ' . $this->formatMessage($res['msg']));
                    return 1;
                }

                if (isset($res['data']['msg'])) {
                    $cleanMsg = $this->formatMessage($res['data']['msg']);
                    $output->writeln($cleanMsg);
                }

                if (isset($res['data']['n'])) {
                    $n = $res['data']['n'];
                    if ($n >= 999999) {
                        $output->info('安装全部完成！锁定安装程序。');
                        touch(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'install.lock');
                        return 0;
                    }
                } else {
                    $output->error('安装中断: 未收到完整的安装结果。');
                    return 1;
                }
            }
        } catch (\Throwable $exception) {
            $output->error('安装中断: ' . $exception->getMessage());
            return 1;
        }
    }

    /**
     * 获取参数，如果是交互模式并必要，则提问
     */
    protected function getParam(Input $input, Output $output, $name, $question, $isForce, $default = '', $isRequired = false)
    {
        $val = $input->getOption($name);

        // 如果是静默安装且未提供则使用默认值，如果提供了值直接返回
        if ($isForce || ($val !== null && $val !== '')) {
            $finalVal = ($val !== null && $val !== '') ? $val : $default;
            if ($isRequired && ($finalVal === null || $finalVal === '')) {
                throw new \InvalidArgumentException("参数 --{$name} 是必填项，请提供后重试。");
            }
            return $finalVal;
        }

        // 交互获取
        $prompt = "请输入{$question} " . ($default !== '' ? "[默认: {$default}]: " : ": ");

        while (true) {
            $answer = $output->ask($input, $prompt, $default);
            if ($isRequired && empty($answer)) {
                $output->error("{$question} 不能为空，请重新输入。");
            } else {
                return $answer;
            }
        }
    }

    protected function formatMessage($message)
    {
        $message = str_replace('&radic;', '√', (string) $message);
        return trim(html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
