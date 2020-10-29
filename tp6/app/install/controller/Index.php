<?php
/**
 * User: jayinton
 */

namespace app\install\controller;


use app\admin\libs\helper\SqlHelper;
use app\admin\libs\helper\StringHelper;
use app\BaseController;
use think\facade\Db;
use Think\Model;

class Index extends BaseController
{
    function index()
    {
        return view('index');
    }


    function step2()
    {
        //错误
        $err = 0;
        //mysql检测
        $mysql = '——';

        //上传检测
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> '.ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
            $err++;
        }
        //session检测
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err++;
        }
        //目录权限检测
        $folder = array(
            'public/',
            'app/install/',
            'config/',
        );
        $folderInfo = array();
        foreach ($folder as $dir) {
            $result = array(
                'dir' => $dir,
            );
            $path = root_path().$dir;
            //是否可读
            if (is_readable($path)) {
                $result['is_readable'] = '<span class="correct_span">&radic;</span>可读';
            } else {
                $result['is_readable'] = '<span class="correct_span error_span">&radic;</span>不可读';
                $err++;
            }
            //是否可写
            if (is_writable($path)) {
                $result['is_writable'] = '<span class="correct_span">&radic;</span>可写';
            } else {
                $result['is_writable'] = '<span class="correct_span error_span">&radic;</span>不可写';
                $err++;
            }
            $folderInfo[] = $result;
        }
        //PHP内置函数检测
        $function = array(
            [
                'name'  => 'mb_strlen',
                'value' => function_exists('mb_strlen')
            ],
            [
                'name'  => 'curl_init',
                'value' => function_exists('curl_init')
            ],
        );
        foreach ($function as $rs) {
            if ($rs == false) {
                $err++;
            }
        }


        return view('step2', [
            'os'         => PHP_OS,
            'function'   => $function,
            'err'        => $err,
            'phpv'       => @phpversion(),
            'mysql'      => $mysql,
            'uploadSize' => $uploadSize,
            'session'    => $session,
            'folderInfo' => $folderInfo
        ]);
    }

    function step3()
    {
        return view('step3');
    }


    function step4()
    {
        //检测数据
        $data = input('post.');

        return view('step4', [
            'data' => json_encode($data)
        ]);
    }

    /**
     * 测试链接数据库
     *
     * @return \think\response\Json
     */
    public function testdbpwd()
    {
        $res = $this->_doTestDbConnection();
        return self::makeJsonReturn($res['status'], null, $res['msg']);
    }

    function _doTestDbConnection()
    {
        $db_config = config('database');
        $db_config['connections']['install'] = [
            // 数据库连接配置信息
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'hostname' => input('db_host', '', 'trim'),
            // 数据库名
            'database' => input('db_name', '', 'trim'),
            // 用户名
            'username' => input('db_user', '', 'trim'),
            // 密码
            'password' => input('db_pwd', '', 'trim'),
            // 端口
            'hostport' => input('db_port', '', 'trim'),
            // 前缀
            'prefix'   => input('db_prefix', '', 'trim'),
            // 数据库连接参数
            'params'   => [],
        ];
        var_dump($db_config['connections']['install']);
        var_dump(input('post.'));
        var_dump(input('dbName'));
        config($db_config, 'database');
        try {
            $res = Db::connect('install', true)->execute('show databases like \''.input('dbName').'\'');
            if ($res == 0) {
                return self::createReturn(false, null, '请先创建数据库'.input('dbName'));
            }
            return self::createReturn(true, $db_config['connections']['install'], '数据库链接成功');
        } catch (\Exception $exception) {
            return self::createReturn(false, null, '数据库链接配置失败');
        }
    }

    //数据库安装
    public function doInstall()
    {
//        $n = intval($_GET['n']);
        $n = input('get.n', 0, 'intval');

        $dbHost = input('dbHost', '', 'trim');
        $dbPort = input('dbPort', '', 'trim');
        $dbName = input('dbName', '', 'trim');
        $dbHost = input('dbHost', '', 'trim');
        $dbUser = input('dbUser', '', 'trim');
        $dbPwd = input('dbPwd', '', 'trim');
        $dbPrefix = input('dbPrefix', '', 'trim');

        $username = input('manager', '', 'trim');
        $password = input('manager_pwd', '', 'trim');
        $manager_email = input('manager_email', '', 'trim');
        //网站名称
        $site_name = input('sitename');
        //网站域名
        $site_url = input('siteurl');
        $_site_url = parse_url($site_url);
        //附件地址
        $sitefileurl = $_site_url['path']."d/file/";
        //描述
        $seo_description = input('siteinfo', '', 'trim');
        //关键词
        $seo_keywords = input('sitekeywords', '', 'trim');
        //测试数据
        $testdata = input('testdata', '', 'intval');

        $res = $this->_doTestDbConnection();
        if (!$res['status']) {
            return self::makeJsonReturn(true, [
                'msg' => $res['msg']
            ]);
        }

        $conn = Db::connect('install');

        //读取数据文件
        $sqldata = file_get_contents(base_path().'data/cms.sql');
        //读取测试数据
        if ($testdata) {
            $sqldataDemo = file_get_contents(base_path().'data/cms_demo.sql');
            $sqldata = $sqldata."\r\n".$sqldataDemo;
        } else {
            //不加测试数据的时候，删除d目录的文件
//            try {
//                $Dir = new \Dir();
//                $Dir->delDir(SITE_PATH . 'd/file/contents/');
//            } catch (\Exception $exc) {
//
//            }
        }
        $sqlFormat = SqlHelper::splitSQL($sqldata, $dbPrefix);

        /**
         * 执行SQL语句
         */
        $counts = count($sqlFormat);

        for ($i = $n; $i < $counts; $i++) {
            $sql = trim($sqlFormat[$i]);

            if (strstr($sql, 'CREATE TABLE')) {
                preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                $conn->execute("DROP TABLE IF EXISTS `$matches[1]`");
                $ret = $conn->execute($sql);

                if ($ret === 0) {
                    $message = '<li><span class="correct_span">&radic;</span>创建数据表'.$matches[1].'，完成</li> ';
                } else {
                    $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表'.$matches[1].'，失败</li>';
                }
                $i++;
                $arr = array('n' => $i, 'msg' => $message);
                return self::makeJsonReturn(true, $arr);
            } else {
                $ret = $conn->execute($sql);
                $message = '';
                $arr = array('n' => $i, 'msg' => $message);
                return self::makeJsonReturn(true, $arr);
            }
        }

//        if ($i == 999999) {
//            exit;
//        }

        //更新配置信息
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='sitename'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='siteurl' ");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$sitefileurl' WHERE varname='sitefileurl' ");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='siteinfo'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='sitekeywords'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$manager_email' WHERE varname='siteemail'");

        //读取配置文件，并替换真实配置数据
        $strConfig = file_get_contents(base_path().'data/config.php');
        $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
        $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
        $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
        $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
        $strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
        $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
        $strConfig = str_replace('#AUTHCODE#', genRandomString(18), $strConfig);
        $strConfig = str_replace('#COOKIE_PREFIX#', genRandomString(3)."_", $strConfig);
        $strConfig = str_replace('#DATA_CACHE_PREFIX#', genRandomString(3)."_", $strConfig);
        @file_put_contents(config().'dataconfig.php', $strConfig);

        //插入管理员
        //生成随机认证码
        $verify = StringHelper::genRandomString(6);
        $time = time();
        $ip = request()->ip();
        $password = md5($password.md5($verify));
        $admin_data = [
            'username'        => $username,
            'nickname'        => '超级管理员',
            'password'        => $password,
            'bind_account'    => '',
            'last_login_time' => $time,
            'last_login_ip'   => $ip,
            'verify'          => $verify,
            'email'           => $manager_email,
            'remark'          => '备注信息',
            'create_time'     => $time,
            'update_time'     => $time,
            'status'          => '1',
            'role_id'         => '1',
            'info'            => '',
        ];

        $query = "INSERT INTO `{$dbPrefix}user` (username, nickname,password,verify,email,remark,create_time,update_time,status,role_id,info) 
          VALUES ('{$admin_data['username']}','{$admin_data['nickname']}','{$admin_data['password']}','{$admin_data['verify']}','{$admin_data['email']}','{$admin_data['remark']}','{$admin_data['create_time']}','{$admin_data['update_time']}','{$admin_data['status']}','{$admin_data['role_id']}','{$admin_data['info']}');";
        $res = $conn->execute($query);

        if ($res) {
            $message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
            $arr = array('n' => 999999, 'msg' => $message);
        } else {
            $message = '<strong style="color: red;">添加管理员失败</strong><br/>';
            $arr = array('n' => 999998, 'msg' => $message);
        }
        return self::makeJsonReturn(true, $arr);
    }


}