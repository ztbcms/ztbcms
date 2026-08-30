<?php

namespace app\install\service;

use think\facade\App;
use think\facade\Db;
use think\facade\Log;
use app\admin\service\ModuleService;
use app\common\libs\helper\SqlHelper;
use app\common\libs\helper\StringHelper;

class InstallService
{
    const INSTALL_STATE_KEY = 'ztbcms_install_state';

    /**
     * 命令行安装状态缓存
     *
     * @var array|null
     */
    protected $runtimeInstallState;

    /**
     * 检查系统环境
     *
     * @return array
     */
    public function checkEnvironment()
    {
        $err = 0;
        $items = [];

        $_php_version = phpversion();
        $mini_php_version = '8.2';
        $isPhpValid = version_compare($_php_version, $mini_php_version, '>=');
        $items[] = [
            'name' => 'PHP版本',
            'require' => '>=' . $mini_php_version,
            'current' => $_php_version,
            'status' => $isPhpValid
        ];
        if (!$isPhpValid) {
            $err++;
        }

        $isUploadValid = ini_get('file_uploads');
        $items[] = [
            'name' => '文件上传',
            'require' => '开启',
            'current' => $isUploadValid ? ini_get('upload_max_filesize') : '禁止',
            'status' => (bool) $isUploadValid
        ];
        if (!$isUploadValid) {
            $err++;
        }

        $isSessionValid = function_exists('session_start');
        $items[] = [
            'name' => 'Session',
            'require' => '支持',
            'current' => $isSessionValid ? '支持' : '不支持',
            'status' => $isSessionValid
        ];
        if (!$isSessionValid) {
            $err++;
        }

        $folders = [
            'public/',
            'app/install/',
            'config/',
        ];
        foreach ($folders as $dir) {
            $path = root_path() . $dir;
            $isReadable = is_readable($path);
            $isWritable = is_writable($path);
            $isValid = $isReadable && $isWritable;
            $items[] = [
                'name' => '目录权限: ' . $dir,
                'require' => '可读写',
                'current' => ($isReadable ? '可读 ' : '不可读 ') . ($isWritable ? '可写' : '不可写'),
                'status' => $isValid
            ];
            if (!$isValid) {
                $err++;
            }
        }

        $functions = ['mb_strlen', 'curl_init'];
        foreach ($functions as $func) {
            $isValid = function_exists($func);
            $items[] = [
                'name' => '核心函数: ' . $func,
                'require' => '支持',
                'current' => $isValid ? '支持' : '不支持',
                'status' => $isValid
            ];
            if (!$isValid) {
                $err++;
            }
        }

        return [
            'status' => $err === 0,
            'err_count' => $err,
            'items' => $items
        ];
    }

    /**
     * 测试链接数据库
     *
     * @param array $config
     * @return array
     */
    public function testDbConnection($config)
    {
        $db_config = config('database');
        $db_config['connections']['install'] = [
            'type'     => 'mysql',
            'hostname' => $config['db_host'] ?? '',
            'database' => $config['db_name'] ?? '',
            'username' => $config['db_user'] ?? '',
            'password' => $config['db_pwd'] ?? '',
            'hostport' => $config['db_port'] ?? '',
            'prefix'   => $config['db_prefix'] ?? '',
            'params'   => [],
        ];
        $db_config['connections']['default'] = 'install';
        config($db_config, 'database');

        try {
            $res = Db::connect('install', true)->execute('show databases like \'' . ($config['db_name'] ?? '') . '\'');
            if ($res == 0) {
                return ['status' => false, 'data' => null, 'msg' => '请先创建数据库' . ($config['db_name'] ?? '')];
            }

            return ['status' => true, 'data' => $db_config['connections']['install'], 'msg' => '数据库链接成功'];
        } catch (\Throwable $exception) {
            return ['status' => false, 'data' => null, 'msg' => '数据库链接配置失败:' . $exception->getMessage()];
        }
    }

    /**
     * 执行分步安装
     *
     * @param array $config
     * @param int $n
     * @return array
     */
    public function executeInstallStep($config, $n = 0)
    {
        $n = (int) $n;
        $prefixMessage = '';

        if ($n > 0) {
            $stateRes = $this->validateRunningState($config);
            if (!$stateRes['status']) {
                return $stateRes;
            }
        }

        $res = $this->testDbConnection($config);
        if (!$res['status']) {
            if ($n > 0) {
                $this->markInstallFailed();
            }
            return $this->createResponse(false, null, $this->buildLogItem(false, $res['msg']), $res['msg']);
        }

        $db_config = config('database');
        $db_config['default'] = 'install';
        config($db_config, 'database');

        if ($n === 0) {
            $startRes = $this->prepareInstallStart($config);
            if (!$startRes['status']) {
                return $startRes;
            }
            $prefixMessage = $startRes['data']['msg'] ?? '';
        }

        $sqlFormat = $this->loadInstallSqlStatements($config['db_prefix'] ?? '');
        $counts = count($sqlFormat);

        for ($i = $n; $i < $counts; $i++) {
            $sql = trim($sqlFormat[$i]);
            if ($sql === '') {
                continue;
            }

            if (stripos($sql, 'CREATE TABLE') !== false) {
                $tableName = $this->extractTableName($sql);

                try {
                    Db::execute($sql);
                    $this->appendCreatedTable($tableName);
                    $message = $this->prependMessage(
                        $prefixMessage,
                        $this->buildLogItem(true, '创建数据表 ' . $tableName . '，完成')
                    );

                    return $this->createResponse(true, $i + 1, $message, '创建数据表 ' . $tableName . '，完成');
                } catch (\Throwable $e) {
                    $this->markInstallFailed();
                    $this->writeInstallLog('创建数据表失败', $e, $sql);

                    $message = $this->prependMessage(
                        $prefixMessage,
                        $this->buildLogItem(false, '创建数据表 ' . $tableName . '，失败: ' . $this->escapeMessage($e->getMessage()))
                    );

                    return $this->createResponse(false, null, $message, '创建数据表 ' . $tableName . ' 失败: ' . $e->getMessage());
                }
            }

            try {
                Db::execute($sql);
            } catch (\Throwable $e) {
                $this->markInstallFailed();
                $this->writeInstallLog('SQL执行失败', $e, $sql);

                $shortSql = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($sql))), 0, 60);
                $message = $this->prependMessage(
                    $prefixMessage,
                    $this->buildLogItem(false, '执行异常: ' . $this->escapeMessage($shortSql) . ' (' . $this->escapeMessage($e->getMessage()) . ')')
                );

                return $this->createResponse(false, null, $message, '安装 SQL 执行失败: ' . $e->getMessage());
            }
        }

        return $this->finishInstall($config, $prefixMessage);
    }

    /**
     * 完成安装剩余操作
     *
     * @param array $config
     * @param string $prefixMessage
     * @return array
     */
    protected function finishInstall(array $config, string $prefixMessage = '')
    {
        try {
            Db::name('config')->where('varname', 'sitename')->update(['value' => $config['sitename'] ?? '']);
            Db::name('config')->where('varname', 'siteurl')->update(['value' => $config['siteurl'] ?? '']);
            Db::name('config')->where('varname', 'siteinfo')->update(['value' => $config['siteinfo'] ?? '']);
            Db::name('config')->where('varname', 'sitekeywords')->update(['value' => $config['sitekeywords'] ?? '']);
            Db::name('config')->where('varname', 'siteemail')->update(['value' => $config['manager_email'] ?? '']);

            $this->snapshotEnvIfNeeded();

            $envTemplatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'env.example';
            $strEnv = file_get_contents($envTemplatePath);
            $strEnv = str_replace('#DB_HOST#', $config['db_host'] ?? '127.0.0.1', $strEnv);
            $strEnv = str_replace('#DB_NAME#', $config['db_name'] ?? '', $strEnv);
            $strEnv = str_replace('#DB_USER#', $config['db_user'] ?? 'root', $strEnv);
            $strEnv = str_replace('#DB_PWD#', $config['db_pwd'] ?? '', $strEnv);
            $strEnv = str_replace('#DB_PORT#', $config['db_port'] ?? '3306', $strEnv);
            $strEnv = str_replace('#DB_PREFIX#', $config['db_prefix'] ?? 'ztb_', $strEnv);
            $strEnv = str_replace('#AUTHCODE#', StringHelper::genRandomString(18), $strEnv);
            $strEnv = str_replace('#COOKIE_PREFIX#', StringHelper::genRandomString(3) . '_', $strEnv);
            $strEnv = str_replace('#CACHE_PREFIX#', StringHelper::genRandomString(3) . '_', $strEnv);

            $envTarget = root_path() . '.env';
            if (file_exists($envTarget)) {
                $existingEnv = file_get_contents($envTarget);
                $pattern = '/### ZTBCMS_INSTALL_START ###.*?### ZTBCMS_INSTALL_END ###/s';
                if (preg_match($pattern, $existingEnv)) {
                    $newEnv = preg_replace($pattern, $strEnv, $existingEnv);
                } else {
                    $newEnv = rtrim($existingEnv) . PHP_EOL . PHP_EOL . $strEnv;
                }
                $envWriteResult = file_put_contents($envTarget, $newEnv);
            } else {
                $envWriteResult = file_put_contents($envTarget, $strEnv);
            }

            if ($envWriteResult === false) {
                throw new \RuntimeException('写入配置文件失败');
            }

            $verify = StringHelper::genRandomString(6);
            $time = time();
            $password = $config['manager_pwd'] ?? '';
            $passwordEnc = md5($password . md5($verify));
            $adminData = [
                'username'        => $config['manager'] ?? '',
                'nickname'        => '超级管理员',
                'password'        => $passwordEnc,
                'bind_account'    => '',
                'last_login_time' => $time,
                'last_login_ip'   => $config['ip'] ?? '127.0.0.1',
                'verify'          => $verify,
                'email'           => $config['manager_email'] ?? '',
                'remark'          => '',
                'create_time'     => $time,
                'update_time'     => $time,
                'status'          => '1',
                'role_id'         => '1',
                'info'            => '',
            ];

            if (!Db::name('user')->insert($adminData)) {
                throw new \RuntimeException('添加管理员失败');
            }

            $moduleService = new ModuleService();
            foreach (['admin', 'common'] as $module) {
                $moduleRes = $moduleService->install($module);
                if (!$moduleRes['status']) {
                    throw new \RuntimeException('安装模块 ' . $module . ' 失败: ' . ($moduleRes['msg'] ?? '未知错误'));
                }
                $this->appendInstalledModule($module);
            }

            $this->clearInstallState();

            $message = $this->prependMessage($prefixMessage, $this->buildLogItem(true, '安装完成'));
            return $this->createResponse(true, 999999, $message, '安装完成');
        } catch (\Throwable $e) {
            $this->markInstallFailed();
            $this->writeInstallLog('安装收尾失败', $e);

            $message = $this->prependMessage(
                $prefixMessage,
                $this->buildLogItem(false, '安装收尾失败: ' . $this->escapeMessage($e->getMessage()))
            );

            return $this->createResponse(false, null, $message, '安装失败: ' . $e->getMessage());
        }
    }

    /**
     * 准备新一轮安装
     *
     * @param array $config
     * @return array
     */
    protected function prepareInstallStart(array $config)
    {
        $state = $this->getInstallState();
        $message = '';

        if (!empty($state) && in_array($state['status'] ?? '', ['running', 'failed'], true)) {
            $currentSignature = $this->createDbSignature($config);
            $savedSignature = $state['db_signature'] ?? '';

            if ($savedSignature === $currentSignature) {
                $cleanupRes = $this->cleanupFailedInstall($state);
                $message .= $cleanupRes['msg'];
                if (!$cleanupRes['status']) {
                    return $this->createResponse(false, null, $message, '清理上一次未完成安装残留失败，请先手动处理后重试');
                }
            } else {
                $this->clearInstallState();
                $message .= $this->buildLogItem(true, '检测到旧的未完成安装记录，但数据库配置已变化，已按全新安装处理');
            }
        } elseif (!empty($state)) {
            $this->clearInstallState();
        }

        $this->saveInstallState($this->createEmptyInstallState($config));

        return [
            'status' => true,
            'data' => ['msg' => $message],
            'msg' => $this->plainMessage($message)
        ];
    }

    /**
     * 校验当前运行中的安装状态
     *
     * @param array $config
     * @return array
     */
    protected function validateRunningState(array $config)
    {
        $state = $this->getInstallState();
        if (empty($state)) {
            return $this->createResponse(false, null, $this->buildLogItem(false, '安装状态已失效，请返回上一步重新开始安装'), '安装状态已失效，请重新开始安装');
        }

        if (($state['db_signature'] ?? '') !== $this->createDbSignature($config)) {
            $this->clearInstallState();
            return $this->createResponse(false, null, $this->buildLogItem(false, '数据库配置已变化，请重新开始安装'), '数据库配置已变化，请重新开始安装');
        }

        if (($state['status'] ?? '') !== 'running') {
            return $this->createResponse(false, null, $this->buildLogItem(false, '安装已中断，请重新开始安装'), '安装已中断，请重新开始安装');
        }

        return ['status' => true, 'data' => null, 'msg' => ''];
    }

    /**
     * 重新开始安装前清理上一次残留
     *
     * @param array $state
     * @return array
     */
    protected function cleanupFailedInstall(array $state)
    {
        $messages = '';
        $hasError = false;
        $moduleService = new ModuleService();

        $messages .= $this->buildLogItem(true, '检测到上一次未完成安装，开始清理残留');

        $modules = array_reverse($state['installed_modules'] ?? []);
        foreach ($modules as $module) {
            try {
                $res = $moduleService->uninstall($module);
                if (!($res['status'] ?? false)) {
                    $hasError = true;
                    $messages .= $this->buildLogItem(false, '清理模块 ' . $module . ' 失败: ' . $this->escapeMessage($res['msg'] ?? '未知错误'));
                    continue;
                }
                $messages .= $this->buildLogItem(true, '清理模块 ' . $module . '，完成');
            } catch (\Throwable $e) {
                $hasError = true;
                $messages .= $this->buildLogItem(false, '清理模块 ' . $module . ' 失败: ' . $this->escapeMessage($e->getMessage()));
                $this->writeInstallLog('清理模块失败', $e);
            }
        }

        $envSnapshot = $state['env_snapshot'] ?? [];
        if (!empty($envSnapshot['captured'])) {
            $envTarget = root_path() . '.env';
            if (!empty($envSnapshot['exists'])) {
                $res = file_put_contents($envTarget, (string) ($envSnapshot['content'] ?? ''));
                if ($res === false) {
                    $hasError = true;
                    $messages .= $this->buildLogItem(false, '恢复配置文件失败');
                } else {
                    $messages .= $this->buildLogItem(true, '恢复配置文件，完成');
                }
            } elseif (file_exists($envTarget)) {
                if (!@unlink($envTarget)) {
                    $hasError = true;
                    $messages .= $this->buildLogItem(false, '删除上次生成的配置文件失败');
                } else {
                    $messages .= $this->buildLogItem(true, '删除上次生成的配置文件，完成');
                }
            }
        }

        $tables = array_reverse($state['created_tables'] ?? []);
        foreach ($tables as $tableName) {
            try {
                Db::execute('DROP TABLE IF EXISTS `' . str_replace('`', '', (string) $tableName) . '`');
                $messages .= $this->buildLogItem(true, '清理数据表 ' . $tableName . '，完成');
            } catch (\Throwable $e) {
                $hasError = true;
                $messages .= $this->buildLogItem(false, '清理数据表 ' . $tableName . ' 失败: ' . $this->escapeMessage($e->getMessage()));
                $this->writeInstallLog('清理数据表失败', $e);
            }
        }

        $this->clearInstallState();

        if ($hasError) {
            $messages .= $this->buildLogItem(false, '上一次安装残留清理未完全成功，请先手动处理后再重试');
            return ['status' => false, 'msg' => $messages];
        }

        $messages .= $this->buildLogItem(true, '上一次安装残留已清理完成，开始重新安装');
        return ['status' => true, 'msg' => $messages];
    }

    /**
     * 读取安装 SQL
     *
     * @param string $dbPrefix
     * @return array
     */
    protected function loadInstallSqlStatements($dbPrefix)
    {
        $sqldata = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'cms.sql');
        return SqlHelper::splitSQL($sqldata, $dbPrefix);
    }

    /**
     * 创建空安装状态
     *
     * @param array $config
     * @return array
     */
    protected function createEmptyInstallState(array $config)
    {
        return [
            'status' => 'running',
            'db_signature' => $this->createDbSignature($config),
            'created_tables' => [],
            'env_snapshot' => [
                'captured' => false,
                'exists' => false,
                'content' => '',
            ],
            'installed_modules' => [],
            'started_at' => time(),
        ];
    }

    /**
     * 记录创建成功的数据表
     *
     * @param string $tableName
     * @return void
     */
    protected function appendCreatedTable($tableName)
    {
        if ($tableName === '') {
            return;
        }

        $state = $this->getInstallState();
        if (empty($state)) {
            return;
        }

        $tables = $state['created_tables'] ?? [];
        if (!in_array($tableName, $tables, true)) {
            $tables[] = $tableName;
            $state['created_tables'] = $tables;
            $this->saveInstallState($state);
        }
    }

    /**
     * 记录已安装模块
     *
     * @param string $module
     * @return void
     */
    protected function appendInstalledModule($module)
    {
        if ($module === '') {
            return;
        }

        $state = $this->getInstallState();
        if (empty($state)) {
            return;
        }

        $modules = $state['installed_modules'] ?? [];
        if (!in_array($module, $modules, true)) {
            $modules[] = $module;
            $state['installed_modules'] = $modules;
            $this->saveInstallState($state);
        }
    }

    /**
     * 记录 .env 初始状态
     *
     * @return void
     */
    protected function snapshotEnvIfNeeded()
    {
        $state = $this->getInstallState();
        if (empty($state)) {
            return;
        }

        $snapshot = $state['env_snapshot'] ?? [];
        if (!empty($snapshot['captured'])) {
            return;
        }

        $envTarget = root_path() . '.env';
        $state['env_snapshot'] = [
            'captured' => true,
            'exists' => file_exists($envTarget),
            'content' => file_exists($envTarget) ? (string) file_get_contents($envTarget) : '',
        ];

        $this->saveInstallState($state);
    }

    /**
     * 标记安装失败
     *
     * @return void
     */
    protected function markInstallFailed()
    {
        $state = $this->getInstallState();
        if (empty($state)) {
            return;
        }

        $state['status'] = 'failed';
        $this->saveInstallState($state);
    }

    /**
     * 获取当前安装状态
     *
     * @return array
     */
    protected function getInstallState()
    {
        if (App::runningInConsole()) {
            if (is_array($this->runtimeInstallState)) {
                return $this->runtimeInstallState;
            }

            $stateFile = $this->getConsoleStateFile();
            if (!is_file($stateFile)) {
                $this->runtimeInstallState = [];
                return [];
            }

            $content = file_get_contents($stateFile);
            $state = json_decode((string) $content, true);
            $this->runtimeInstallState = is_array($state) ? $state : [];
            return $this->runtimeInstallState;
        }

        $state = session(self::INSTALL_STATE_KEY);
        return is_array($state) ? $state : [];
    }

    /**
     * 保存安装状态
     *
     * @param array $state
     * @return void
     */
    protected function saveInstallState(array $state)
    {
        if (App::runningInConsole()) {
            $this->runtimeInstallState = $state;
            $stateFile = $this->getConsoleStateFile();
            $stateDir = dirname($stateFile);
            if (!is_dir($stateDir)) {
                mkdir($stateDir, 0755, true);
            }
            file_put_contents($stateFile, json_encode($state, JSON_UNESCAPED_UNICODE));
            return;
        }

        session(self::INSTALL_STATE_KEY, $state);
    }

    /**
     * 清理安装状态
     *
     * @return void
     */
    protected function clearInstallState()
    {
        if (App::runningInConsole()) {
            $this->runtimeInstallState = [];
            $stateFile = $this->getConsoleStateFile();
            if (is_file($stateFile)) {
                @unlink($stateFile);
            }
            return;
        }

        session(self::INSTALL_STATE_KEY, null);
    }

    /**
     * 生成数据库签名
     *
     * @param array $config
     * @return string
     */
    protected function createDbSignature(array $config)
    {
        $parts = [
            trim((string) ($config['db_host'] ?? '')),
            trim((string) ($config['db_port'] ?? '')),
            trim((string) ($config['db_name'] ?? '')),
            trim((string) ($config['db_prefix'] ?? '')),
        ];

        return md5(implode('|', $parts));
    }

    /**
     * 从 SQL 中提取表名
     *
     * @param string $sql
     * @return string
     */
    protected function extractTableName($sql)
    {
        preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $sql, $matches);
        return $matches[1] ?? '未知表';
    }

    /**
     * 生成前端日志项
     *
     * @param bool $status
     * @param string $message
     * @return string
     */
    protected function buildLogItem($status, $message)
    {
        $class = $status ? 'correct_span' : 'correct_span error_span';
        $icon = $status ? '&radic;' : '&times;';
        return '<li><span class="' . $class . '">' . $icon . '</span>' . $message . '</li>';
    }

    /**
     * 合并前置日志
     *
     * @param string $prefix
     * @param string $message
     * @return string
     */
    protected function prependMessage($prefix, $message)
    {
        return (string) $prefix . (string) $message;
    }

    /**
     * 统一响应结构
     *
     * @param bool $status
     * @param int|null $n
     * @param string $htmlMessage
     * @param string $plainMessage
     * @return array
     */
    protected function createResponse($status, $n, $htmlMessage, $plainMessage = '')
    {
        $data = ['msg' => $htmlMessage];
        if ($n !== null) {
            $data['n'] = $n;
        }

        return [
            'status' => (bool) $status,
            'data' => $data,
            'msg' => $plainMessage !== '' ? $plainMessage : $this->plainMessage($htmlMessage),
        ];
    }

    /**
     * HTML 日志转纯文本
     *
     * @param string $message
     * @return string
     */
    protected function plainMessage($message)
    {
        $message = str_replace('&radic;', '√', (string) $message);
        $message = str_replace('&times;', '×', $message);
        $message = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return trim((string) $message);
    }

    /**
     * 转义日志内容
     *
     * @param string $message
     * @return string
     */
    protected function escapeMessage($message)
    {
        return htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 记录安装日志
     *
     * @param string $title
     * @param \Throwable $exception
     * @param string $sql
     * @return void
     */
    protected function writeInstallLog($title, \Throwable $exception, $sql = '')
    {
        try {
            $content = $title . ': ' . $exception->getMessage();
            if ($sql !== '') {
                $content .= "\nSQL: " . $sql;
            }
            Log::write($content, 'error');
        } catch (\Throwable $logException) {
        }
    }

    /**
     * 命令行安装状态文件
     *
     * @return string
     */
    protected function getConsoleStateFile()
    {
        return runtime_path() . 'install' . DIRECTORY_SEPARATOR . 'install_state.json';
    }
}
