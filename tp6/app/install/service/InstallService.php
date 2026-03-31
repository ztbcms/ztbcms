<?php

namespace app\install\service;

use app\common\libs\helper\SqlHelper;
use app\common\libs\helper\StringHelper;
use app\admin\service\ModuleService;
use think\facade\Db;

class InstallService
{
    /**
     * 检查系统环境
     *
     * @return array
     */
    public function checkEnvironment()
    {
        $err = 0;
        $items = [];

        // php版本
        $_php_version = phpversion();
        $mini_php_version = '7.2';
        $isPhpValid = version_compare($_php_version, $mini_php_version, '>=');
        $items[] = [
            'name' => 'PHP版本',
            'require' => '>=' . $mini_php_version,
            'current' => $_php_version,
            'status' => $isPhpValid
        ];
        if (!$isPhpValid) $err++;

        // 上传检测
        $isUploadValid = ini_get('file_uploads');
        $items[] = [
            'name' => '文件上传',
            'require' => '开启',
            'current' => $isUploadValid ? ini_get('upload_max_filesize') : '禁止',
            'status' => (bool)$isUploadValid
        ];
        if (!$isUploadValid) $err++;

        // session检测
        $isSessionValid = function_exists('session_start');
        $items[] = [
            'name' => 'Session',
            'require' => '支持',
            'current' => $isSessionValid ? '支持' : '不支持',
            'status' => $isSessionValid
        ];
        if (!$isSessionValid) $err++;

        // 目录权限检测
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
            if (!$isValid) $err++;
        }

        // PHP内置函数检测
        $functions = ['mb_strlen', 'curl_init'];
        foreach ($functions as $func) {
            $isValid = function_exists($func);
            $items[] = [
                'name' => '核心函数: ' . $func,
                'require' => '支持',
                'current' => $isValid ? '支持' : '不支持',
                'status' => $isValid
            ];
            if (!$isValid) $err++;
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
     * @return array ['status' => bool, 'data' => mixed, 'msg' => string]
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
        } catch (\Exception $exception) {
            return ['status' => false, 'data' => null, 'msg' => '数据库链接配置失败:' . $exception->getMessage()];
        }
    }

    /**
     * 执行分布安装
     * @param array $config 安装参数
     * @param int $n 当前执行进度步骤数
     * @return array
     */
    public function executeInstallStep($config, $n = 0)
    {
        $res = $this->testDbConnection($config);
        if (!$res['status']) {
            return ['status' => false, 'data' => null, 'msg' => $res['msg']];
        }

        // 切换 install 链接来安装
        $db_config = config('database');
        $db_config['default'] = 'install';
        config($db_config, 'database');

        $dbPrefix = $config['db_prefix'] ?? '';

        // 读取数据文件
        $sqldata = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'cms.sql');
        $sqlFormat = SqlHelper::splitSQL($sqldata, $dbPrefix);

        $counts = count($sqlFormat);
        for ($i = $n; $i < $counts; $i++) {
            $sql = trim($sqlFormat[$i]);
            if (stripos($sql, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $sql, $matches);
                $tableName = $matches[1] ?? '未知表';
                
                try {
                    Db::execute($sql);
                    $message = '<li><span class="correct_span">&radic;</span>创建数据表 ' . $tableName . '，完成</li> ';
                } catch (\Throwable $e) {
                    $message = '<li><span class="correct_span error_span">&times;</span>创建数据表 ' . $tableName . '，失败: ' . $e->getMessage() . '</li>';
                    try {
                        \think\facade\Log::write('创建数据表失败: ' . $e->getMessage() . "\nSQL: " . $sql, 'error');
                    } catch (\Throwable $logE) {
                        // 防止日志写入自身报错诱发深渊
                    }
                }
                
                $arr = array('n' => $i + 1, 'msg' => $message);
                return ['status' => true, 'data' => $arr, 'msg' => $message];
            } else {
                // 非创建表的，直接执行
                if (!empty($sql)) {
                    try {
                        Db::execute($sql);
                    } catch (\Throwable $e) {
                        // 组装前台报错
                        $shortSql = mb_substr(strip_tags($sql), 0, 40) . '...';
                        $errorMsg = mb_substr($e->getMessage(), 0, 60);
                        $message = '<li><span class="correct_span error_span">&times;</span>执行异常: <span style="color:red" title="'.htmlspecialchars($e->getMessage()).'">' . $shortSql . ' (' . $errorMsg . ')</span></li>';
                        
                        try {
                            \think\facade\Log::write('SQL执行失败: ' . $e->getMessage() . "\nSQL: " . $sql, 'error');
                        } catch (\Throwable $logE) { }
                        
                        // 往外阻断当前批次直接抛出可视化的进度提示 (但不中断整体大循环，继续推进一步)
                        $arr = array('n' => $i + 1, 'msg' => $message);
                        return ['status' => true, 'data' => $arr, 'msg' => $message];
                    }
                }
            }
        }

        // 到达最后一步，更新后续配置和账号
        return $this->finishInstall($config, $dbPrefix);
    }

    /**
     * 完成安装剩余操作
     */
    protected function finishInstall($config, $dbPrefix)
    {
        $site_name = $config['sitename'] ?? '';
        $site_url = $config['siteurl'] ?? '';
        $seo_description = $config['siteinfo'] ?? '';
        $seo_keywords = $config['sitekeywords'] ?? '';
        $manager_email = $config['manager_email'] ?? '';

        // 更新配置信息
        Db::execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='sitename'");
        Db::execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='siteurl' ");
        Db::execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='siteinfo'");
        Db::execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='sitekeywords'");
        Db::execute("UPDATE `{$dbPrefix}config` SET  `value` = '$manager_email' WHERE varname='siteemail'");

        // 构建并落盘 .env 环境变量文件
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
            $res = file_put_contents($envTarget, $newEnv);
        } else {
            $res = file_put_contents($envTarget, $strEnv);
        }

        if (!$res) {
            $message = '<li><span class="correct_span">&radic;</span>写入配置文件，失败</li> ';
            return ['status' => false, 'data' => ['msg' => $message], 'msg' => $message];
        }

        // 添加超级管理员
        $username = $config['manager'] ?? '';
        $password = $config['manager_pwd'] ?? '';
        $verify = StringHelper::genRandomString(6); // 生成随机认证码
        $time = time();
        $ip = $config['ip'] ?? '127.0.0.1';
        $passwordEnc = md5($password . md5($verify));
        $admin_data = [
            'username'        => $username,
            'nickname'        => '超级管理员',
            'password'        => $passwordEnc,
            'bind_account'    => '',
            'last_login_time' => $time,
            'last_login_ip'   => $ip,
            'verify'          => $verify,
            'email'           => $manager_email,
            'remark'          => '',
            'create_time'     => $time,
            'update_time'     => $time,
            'status'          => '1',
            'role_id'         => '1',
            'info'            => '',
        ];
        $query = "INSERT INTO `{$dbPrefix}user` (username, nickname,password,verify,email,remark,create_time,update_time,status,role_id,info) 
          VALUES ('{$admin_data['username']}','{$admin_data['nickname']}','{$admin_data['password']}','{$admin_data['verify']}','{$admin_data['email']}','{$admin_data['remark']}','{$admin_data['create_time']}','{$admin_data['update_time']}','{$admin_data['status']}','{$admin_data['role_id']}','{$admin_data['info']}');";
        $res = Db::execute($query);
        if (!$res) {
            $message = '<li><span class="correct_span">&radic;</span>添加管理员，失败</li> ';
            return ['status' => false, 'data' => ['msg' => $message], 'msg' => $message];
        }

        // 安装默认模块
        $moduleService = new ModuleService();
        $install_modules = ['admin', 'common'];
        foreach ($install_modules as $module) {
            $res = $moduleService->install($module);
            if (!$res['status']) {
                $message = '<li><span class="correct_span">&radic;</span>安装模块' . $module . '，失败:' . $res['msg'] . '</li> ';
                return ['status' => false, 'data' => ['msg' => $message], 'msg' => $message];
            }
        }

        return ['status' => true, 'data' => ['n' => 999999, 'msg' => '安装完成'], 'msg' => '安装完成'];
    }
}
