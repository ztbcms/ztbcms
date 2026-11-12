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
    const INSTALL_CONFIG_KEY = 'ztbcms_install_config';
    const MIN_PHP_VERSION = '7.2';
    const MIN_MANAGER_PWD_LENGTH = 6;
    const INSTALL_FINISHED_STEP = 999999;
    const ENV_INSTALL_MARKER = '### ZTBCMS_INSTALL_START ###';

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
        $mini_php_version = self::MIN_PHP_VERSION;
        $isPhpValid = version_compare($_php_version, $mini_php_version, '>=');
        $phpGroup = [
            'name' => 'PHP版本',
            'require' => '>=' . $mini_php_version,
            'current' => $_php_version,
            'status' => $isPhpValid,
        ];
        $items[] = $phpGroup;
        if (!$isPhpValid) {
            $err++;
        }

        $isUploadValid = (bool) ini_get('file_uploads');
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $uploadGroup = [
            'name' => '文件上传',
            'require' => '开启',
            'current' => $isUploadValid ? $uploadMaxFilesize : '禁止上传',
            'status' => $isUploadValid,
        ];
        $items[] = [
            'name' => '文件上传',
            'require' => '开启',
            'current' => $isUploadValid ? $uploadMaxFilesize : '禁止',
            'status' => $isUploadValid,
        ];
        if (!$isUploadValid) {
            $err++;
        }

        $isSessionValid = function_exists('session_start');
        $sessionGroup = [
            'name' => 'Session',
            'require' => '支持',
            'current' => $isSessionValid ? '支持' : '不支持',
            'status' => $isSessionValid,
        ];
        $items[] = $sessionGroup;
        if (!$isSessionValid) {
            $err++;
        }

        $envFile = root_path() . '.env';
        $hasEnvFile = file_exists($envFile);
        $folders = [
            $hasEnvFile ? '.env' : './',
            'runtime/',
            'app/install/',
        ];
        $folderGroups = [];
        foreach ($folders as $dir) {
            if ($dir === '.env') {
                $path = $envFile;
            } elseif ($dir === './') {
                $path = root_path();
            } else {
                $path = root_path() . $dir;
            }
            if ($dir === 'runtime/' && !is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $isReadable = is_readable($path);
            $isWritable = is_writable($path);
            $isValid = $isReadable && $isWritable;
            $folderGroups[] = [
                'dir' => $dir,
                'is_readable' => $isReadable,
                'is_writable' => $isWritable,
                'status' => $isValid,
            ];
            $items[] = [
                'name' => '目录权限: ' . $dir,
                'require' => '可读写',
                'current' => ($isReadable ? '可读 ' : '不可读 ') . ($isWritable ? '可写' : '不可写'),
                'status' => $isValid,
            ];
            if (!$isValid) {
                $err++;
            }
        }

        $extensions = ['pdo', 'pdo_mysql'];
        $extensionGroups = [];
        foreach ($extensions as $ext) {
            $isValid = extension_loaded($ext);
            $extensionGroups[] = [
                'name' => $ext,
                'status' => $isValid,
            ];
            $items[] = [
                'name' => '核心扩展: ' . $ext,
                'require' => '开启',
                'current' => $isValid ? '已开启' : '未开启',
                'status' => $isValid,
            ];
            if (!$isValid) {
                $err++;
            }
        }

        $functions = ['mb_strlen', 'curl_init'];
        $functionGroups = [];
        foreach ($functions as $func) {
            $isValid = function_exists($func);
            $functionGroups[] = [
                'name' => $func,
                'status' => $isValid,
            ];
            $items[] = [
                'name' => '核心函数: ' . $func,
                'require' => '支持',
                'current' => $isValid ? '支持' : '不支持',
                'status' => $isValid,
            ];
            if (!$isValid) {
                $err++;
            }
        }

        return [
            'status' => $err === 0,
            'err_count' => $err,
            'items' => $items,
            'groups' => [
                'php' => $phpGroup,
                'upload' => $uploadGroup,
                'session' => $sessionGroup,
                'folders' => $folderGroups,
                'extensions' => $extensionGroups,
                'functions' => $functionGroups,
            ],
        ];
    }

    /**
     * 校验数据库连接配置
     *
     * @param array $config
     * @param bool $requirePrefix
     * @return array
     */
    public function validateDbConfig(array $config, bool $requirePrefix = false): array
    {
        $dbHost = trim((string) ($config['db_host'] ?? ''));
        if ($dbHost === '') {
            return ['status' => false, 'msg' => '数据库服务器地址不能为空'];
        }

        $dbPort = trim((string) ($config['db_port'] ?? ''));
        if ($dbPort === '') {
            return ['status' => false, 'msg' => '数据库服务器端口不能为空'];
        }
        if (!ctype_digit($dbPort) || (int) $dbPort < 1 || (int) $dbPort > 65535) {
            return ['status' => false, 'msg' => '数据库服务器端口格式不正确'];
        }

        $dbUser = trim((string) ($config['db_user'] ?? ''));
        if ($dbUser === '') {
            return ['status' => false, 'msg' => '数据库用户名不能为空'];
        }

        $dbName = trim((string) ($config['db_name'] ?? ''));
        if ($dbName === '') {
            return ['status' => false, 'msg' => '数据库名不能为空'];
        }
        if (!preg_match('/^[a-z0-9_]+$/i', $dbName)) {
            return ['status' => false, 'msg' => '数据库名格式不正确，仅支持字母、数字和下划线'];
        }

        $dbPrefix = trim((string) ($config['db_prefix'] ?? ''));
        if ($requirePrefix && $dbPrefix === '') {
            return ['status' => false, 'msg' => '数据库表前缀不能为空'];
        }
        if ($dbPrefix !== '' && !preg_match('/^[a-z0-9_]+$/i', $dbPrefix)) {
            return ['status' => false, 'msg' => '数据库表前缀格式不正确，仅支持字母、数字和下划线'];
        }

        return ['status' => true, 'msg' => ''];
    }

    /**
     * 校验完整安装参数
     *
     * @param array $config
     * @return array
     */
    public function validateInstallConfig(array $config): array
    {
        $dbValidateRes = $this->validateDbConfig($config, true);
        if (!$dbValidateRes['status']) {
            return $dbValidateRes;
        }

        $manager = trim((string) ($config['manager'] ?? ''));
        if ($manager === '') {
            return ['status' => false, 'msg' => '管理员帐号不能为空'];
        }

        $managerPwd = (string) ($config['manager_pwd'] ?? '');
        if ($managerPwd === '') {
            return ['status' => false, 'msg' => '管理员密码不能为空'];
        }
        if (mb_strlen($managerPwd, 'UTF-8') < self::MIN_MANAGER_PWD_LENGTH) {
            return ['status' => false, 'msg' => '管理员密码长度不能少于' . self::MIN_MANAGER_PWD_LENGTH . '位'];
        }

        if (array_key_exists('manager_ckpwd', $config)) {
            $managerCkpwd = (string) ($config['manager_ckpwd'] ?? '');
            if ($managerCkpwd === '') {
                return ['status' => false, 'msg' => '重复密码不能为空'];
            }
            if ($managerCkpwd !== $managerPwd) {
                return ['status' => false, 'msg' => '两次输入的密码不一致'];
            }
        }

        $managerEmail = trim((string) ($config['manager_email'] ?? ''));
        if ($managerEmail === '') {
            return ['status' => false, 'msg' => '管理员邮箱不能为空'];
        }
        if (filter_var($managerEmail, FILTER_VALIDATE_EMAIL) === false) {
            return ['status' => false, 'msg' => '管理员邮箱格式不正确'];
        }

        return ['status' => true, 'msg' => ''];
    }

    /**
     * 测试链接数据库
     *
     * @param array $config
     * @return array
     */
    public function testDbConnection($config)
    {
        $validateRes = $this->validateDbConfig(is_array($config) ? $config : [], false);
        if (!$validateRes['status']) {
            return ['status' => false, 'data' => null, 'msg' => $validateRes['msg']];
        }

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
        config($db_config, 'database');

        try {
            Db::connect('install', true)->query('SELECT 1');

            return ['status' => true, 'data' => $db_config['connections']['install'], 'msg' => '数据库链接成功'];
        } catch (\Throwable $exception) {
            return ['status' => false, 'data' => null, 'msg' => '数据库链接配置失败:' . $exception->getMessage()];
        }
    }

    /**
     * 检查是否存在安装锁文件
     *
     * @return bool
     */
    public function hasInstallLock(): bool
    {
        return is_file(install_lock_path());
    }

    /**
     * 检查 .env 中是否已包含安装器生成的配置区块
     *
     * @return bool
     */
    public function hasInstallEnvBlock(): bool
    {
        $envPath = root_path() . '.env';
        if (!is_file($envPath) || !is_readable($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);
        return is_string($content) && strpos($content, self::ENV_INSTALL_MARKER) !== false;
    }

    /**
     * 判断系统是否已安装（锁文件或 .env 安装配置任一存在即视为已安装）
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->hasInstallLock() || $this->hasInstallEnvBlock();
    }

    /**
     * 检测目标数据库中是否已存在核心 user 表
     *
     * @param string $dbName
     * @param string $dbPrefix
     * @return bool
     */
    public function hasExistingUserTable(string $dbName, string $dbPrefix): bool
    {
        $rows = Db::connect('install')->query(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
            [$dbName, $dbPrefix . 'user']
        );

        return !empty($rows);
    }

    /**
     * 执行分步安装
     *
     * @param array $config
     * @param int $n
     * @param bool $allowOverwrite 是否允许覆盖已存在的安装配置与数据表（仅 CLI 显式指定时开启）
     * @return array
     */
    public function executeInstallStep($config, $n = 0, bool $allowOverwrite = false)
    {
        $n = (int) $n;
        $prefixMessage = '';

        if ($n < 0) {
            $errMsg = '非法安装步骤参数';
            return $this->createResponse(false, null, $this->buildLogItem(false, $errMsg), $errMsg);
        }

        if (!$allowOverwrite && $this->isInstalled()) {
            $errMsg = '系统已安装（检测到 install.lock 或 .env 安装配置），禁止在非覆盖模式下重新安装';
            return $this->createResponse(false, null, $this->buildLogItem(false, $this->escapeMessage($errMsg)), $errMsg);
        }

        $validateRes = $this->validateInstallConfig(is_array($config) ? $config : []);
        if (!$validateRes['status']) {
            if ($n > 0) {
                $this->markInstallFailed();
            }
            return $this->createResponse(false, null, $this->buildLogItem(false, $this->escapeMessage($validateRes['msg'])), $validateRes['msg']);
        }

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

            $dbName = (string) ($config['db_name'] ?? '');
            $dbPrefix = (string) ($config['db_prefix'] ?? '');
            if (!$allowOverwrite && $this->hasExistingUserTable($dbName, $dbPrefix)) {
                $this->clearInstallState();
                $errMsg = "目标数据库已存在 {$dbPrefix}user 表，为防止现有数据被清空，禁止覆盖安装；请更换空库/表前缀，或通过命令行使用 --overwrite 参数重装";
                return $this->createResponse(false, null, $this->buildLogItem(false, $this->escapeMessage($errMsg)), $errMsg);
            }
        }

        $sqlFormat = $this->loadInstallSqlStatements($config['db_prefix'] ?? '');
        $counts = count($sqlFormat);
        if ($n > $counts) {
            $errMsg = '安装步骤参数超出范围';
            return $this->createResponse(false, null, $this->buildLogItem(false, $errMsg), $errMsg);
        }

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
                    $message = $prefixMessage . $this->buildLogItem(true, '创建数据表 ' . $tableName . '，完成');

                    return $this->createResponse(true, $i + 1, $message, '创建数据表 ' . $tableName . '，完成');
                } catch (\Throwable $e) {
                    $this->markInstallFailed();
                    $this->writeInstallLog('创建数据表失败', $e, $sql);

                    $message = $prefixMessage . $this->buildLogItem(false, '创建数据表 ' . $tableName . '，失败: ' . $this->escapeMessage($e->getMessage()));

                    return $this->createResponse(false, null, $message, '创建数据表 ' . $tableName . ' 失败: ' . $e->getMessage());
                }
            }

            try {
                Db::execute($sql);
            } catch (\Throwable $e) {
                $this->markInstallFailed();
                $this->writeInstallLog('SQL执行失败', $e, $sql);

                $shortSql = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($sql))), 0, 60);
                $message = $prefixMessage . $this->buildLogItem(false, '执行异常: ' . $this->escapeMessage($shortSql) . ' (' . $this->escapeMessage($e->getMessage()) . ')');

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

            $envTemplatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'env.example';
            $strEnv = file_get_contents($envTemplatePath);
            $strEnv = strtr($strEnv, [
                '#DB_HOST#'       => $this->formatEnvValue($config['db_host'] ?? '127.0.0.1', '数据库地址'),
                '#DB_NAME#'       => $this->formatEnvValue($config['db_name'] ?? '', '数据库名称'),
                '#DB_USER#'       => $this->formatEnvValue($config['db_user'] ?? 'root', '数据库用户名'),
                '#DB_PWD#'        => $this->formatEnvValue($config['db_pwd'] ?? '', '数据库密码'),
                '#DB_PORT#'       => $this->formatEnvValue($config['db_port'] ?? '3306', '数据库端口'),
                '#DB_PREFIX#'     => $this->formatEnvValue($config['db_prefix'] ?? 'ztb_', '数据表前缀'),
                '#AUTHCODE#'      => $this->formatEnvValue(bin2hex(random_bytes(16)), 'AUTHCODE'),
                '#COOKIE_PREFIX#' => $this->formatEnvValue(bin2hex(random_bytes(3)) . '_', 'COOKIE_PREFIX'),
                '#CACHE_PREFIX#'  => $this->formatEnvValue(bin2hex(random_bytes(3)) . '_', 'CACHE_PREFIX'),
            ]);

            $envTarget = root_path() . '.env';
            $envExistedBeforeWrite = file_exists($envTarget);
            $previousEnvContent = $envExistedBeforeWrite ? file_get_contents($envTarget) : false;
            if ($envExistedBeforeWrite && $previousEnvContent === false) {
                throw new \RuntimeException('读取现有配置文件失败');
            }

            $this->snapshotEnvIfNeeded($envExistedBeforeWrite, is_string($previousEnvContent) ? $previousEnvContent : '');

            if ($envExistedBeforeWrite) {
                $existingEnv = (string) $previousEnvContent;
                $pattern = '/### ZTBCMS_INSTALL_START ###.*?### ZTBCMS_INSTALL_END ###/s';
                if (preg_match($pattern, $existingEnv)) {
                    $newEnv = preg_replace_callback($pattern, function () use ($strEnv) {
                        return $strEnv;
                    }, $existingEnv);
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

            if (!touch(install_lock_path())) {
                $this->rollbackEnvOnImmediateFailure($envTarget, $envExistedBeforeWrite, $previousEnvContent);
                throw new \RuntimeException('写入安装锁文件失败');
            }

            $this->clearInstallState();

            $message = $prefixMessage . $this->buildLogItem(true, '安装完成');
            return $this->createResponse(true, self::INSTALL_FINISHED_STEP, $message, '安装完成');
        } catch (\Throwable $e) {
            $this->markInstallFailed();
            $this->writeInstallLog('安装收尾失败', $e);

            $message = $prefixMessage . $this->buildLogItem(false, '安装收尾失败: ' . $this->escapeMessage($e->getMessage()));

            return $this->createResponse(false, null, $message, '安装失败: ' . $e->getMessage());
        }
    }

    /**
     * 格式化写入 .env 的配置值（拒绝双引号与换行符，并使用双引号包裹）
     *
     * @param mixed $value
     * @param string $fieldName
     * @return string
     */
    protected function formatEnvValue($value, string $fieldName = '配置项'): string
    {
        $str = (string) $value;
        if (preg_match('/["\r\n\0]/', $str)) {
            throw new \RuntimeException($fieldName . '不能包含双引号或换行符');
        }

        return '"' . $str . '"';
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
                if (!empty($envSnapshot['had_install_block'])) {
                    $hasError = true;
                    $messages .= $this->buildLogItem(false, '配置文件 .env 包含已有安装区块且被上次中断的安装覆盖，无法自动还原，请先手动恢复 .env 后重试');
                } elseif (file_exists($envTarget)) {
                    $existingEnv = (string) file_get_contents($envTarget);
                    $cleanedEnv = preg_replace('/(?:\r?\n)*### ZTBCMS_INSTALL_START ###.*?### ZTBCMS_INSTALL_END ###\r?\n?/s', '', $existingEnv);
                    $res = file_put_contents($envTarget, (string) $cleanedEnv);
                    if ($res === false) {
                        $hasError = true;
                        $messages .= $this->buildLogItem(false, '恢复配置文件失败');
                    } else {
                        $messages .= $this->buildLogItem(true, '恢复配置文件，完成');
                    }
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
                'had_install_block' => false,
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
     * 记录 .env 初始状态（不保存文件内容，避免残留明文密码）
     *
     * @param bool|null $exists
     * @param string $existingContent
     * @return void
     */
    protected function snapshotEnvIfNeeded(?bool $exists = null, string $existingContent = '')
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
        if ($exists === null) {
            $exists = file_exists($envTarget);
            $existingContent = $exists ? (string) file_get_contents($envTarget) : '';
        }
        $hadInstallBlock = $exists && (bool) preg_match('/### ZTBCMS_INSTALL_START ###.*?### ZTBCMS_INSTALL_END ###/s', $existingContent);

        $state['env_snapshot'] = [
            'captured' => true,
            'exists' => $exists,
            'had_install_block' => $hadInstallBlock,
        ];

        $this->saveInstallState($state);
    }

    /**
     * 当 finishInstall 写入 .env 后紧邻步骤（如写锁）失败时，使用内存中的原始内容立即回滚
     *
     * @param string $envTarget
     * @param bool $envExistedBeforeWrite
     * @param string|false $previousEnvContent
     * @return void
     */
    protected function rollbackEnvOnImmediateFailure(string $envTarget, bool $envExistedBeforeWrite, $previousEnvContent)
    {
        $restored = false;
        if ($envExistedBeforeWrite && is_string($previousEnvContent)) {
            $restored = file_put_contents($envTarget, $previousEnvContent) !== false;
        } elseif (!$envExistedBeforeWrite) {
            $restored = !file_exists($envTarget) || @unlink($envTarget);
        }

        if ($restored) {
            $state = $this->getInstallState();
            if (!empty($state) && isset($state['env_snapshot'])) {
                $state['env_snapshot']['captured'] = false;
                $this->saveInstallState($state);
            }
        }
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
        session(self::INSTALL_CONFIG_KEY, null);
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
