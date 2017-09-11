<?php

namespace Install\Controller;

use Libs\Helper\MysqlHelper;
use Think\Controller;
use Think\Model;

class IndexController extends Controller {

	//初始化
	public function _initialize() {
		header('Content-Type:text/html;charset=utf-8;');
		if (!defined('INSTALL')) {
			exit('请不要直接访问本模块。');
		}
		//检查是否已经安装过
		if (is_file(MODULE_PATH . 'install.lock')) {
			exit('你已经安装过该系统，如果想重新安装，请先删除站点' . MODULE_PATH . '目录下的 install.lock 文件，然后再安装。');
		}
		$this->assign('Title', C('CMS_APPNAME'))
			->assign('Powered', 'Powered by ZTBCMS');
	}

	//安装首页
	public function index() {
		$this->display();
	}

	//第二步
	public function step_2() {
		//错误
		$err = 0;
		//mysql检测
//        $db_version = MysqlHelper::getVersion();
//        if(!empty($db_version)){
//            $mysql = '<span class="correct_span">&radic;</span> ' . $db_version;
//        }else{
//            $mysql = '<span class="correct_span error_span">&radic;</span> 链接错误' ;
//        }
        $mysql = '';

		//上传检测
		if (ini_get('file_uploads')) {
			$uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
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
			'/',
			'/d/',
			'/app/Application/Install/',
			'/app/Common/Conf/',
			'/app/Common/Conf/addition.php',
		);
		$dir = new \Dir();
		$folderInfo = array();
		foreach ($folder as $dir) {
			$result = array(
				'dir' => $dir,
			);
			$path = SITE_PATH . $dir;
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
			'mb_strlen' => function_exists('mb_strlen'),
			'curl_init' => function_exists('curl_init'),
		);
		foreach ($function as $rs) {
			if ($rs == false) {
				$err++;
			}
		}

		$this->assign('os', PHP_OS)
			->assign('function', $function)
			->assign('err', $err)
			->assign('phpv', @phpversion())
			->assign('mysql', $mysql)
			->assign('uploadSize', $uploadSize)
			->assign('session', $session)
			->assign('folderInfo', $folderInfo);
		$this->display();
	}

	//第三步
	public function step_3() {
		//地址
		$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
		$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "/", $scriptName);
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		if ((int) $_SERVER['SERVER_PORT'] != 80) {
			$domain .= ":" . $_SERVER['SERVER_PORT'];
		}
		$domain = $sys_protocal . $domain . $rootpath;
		$parse_url = parse_url($domain);
		$parse_url['path'] = str_replace('install.php', '', $parse_url['path']);
		$this->assign('parse_url', $parse_url);
		$this->display();
	}

	//数据库安装
	public function step_4() {
		$this->assign('data', json_encode($_POST));
		$this->display();
	}

	//安装完成
	public function step_5() {
		@unlink(RUNTIME_PATH . APP_MODE . '~runtime.php');
		@touch(MODULE_PATH . 'install.lock');
		$this->display();
	}

	//数据库安装
	public function mysql() {
		$n = intval($_GET['n']);

		$arr = array();

		$dbHost = trim($_POST['dbhost']);
		$dbPort = trim($_POST['dbport']);
		$dbName = trim($_POST['dbname']);
		$dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost . ':' . $dbPort;
		$dbUser = trim($_POST['dbuser']);
		$dbPwd = trim($_POST['dbpw']);
		$dbPrefix = empty($_POST['dbprefix']) ? 'think_' : trim($_POST['dbprefix']);

		$username = trim($_POST['manager']);
		$password = trim($_POST['manager_pwd']);
		//网站名称
		$site_name = addslashes(trim($_POST['sitename']));
		//网站域名
		$site_url = trim($_POST['siteurl']);
		$_site_url = parse_url($site_url);
		//附件地址
		$sitefileurl = $_site_url['path'] . "d/file/";
		//描述
		$seo_description = trim($_POST['siteinfo']);
		//关键词
		$seo_keywords = trim($_POST['sitekeywords']);
		//测试数据
		$testdata = (int) $_POST['testdata'];
		//邮箱地址
		$siteemail = trim($_POST['manager_email']);

        $conn = new Model('', '', [
            'DB_TYPE' => 'mysql', // 数据库类型
            'DB_HOST' => $dbHost, // 服务器地址
            'DB_NAME' => $dbName, // 数据库名
            'DB_USER' => $dbUser, // 用户名
            'DB_PWD' => $dbPwd, // 密码
            'DB_PORT' => $dbPort, // 端口
            'DB_PREFIX' => $dbPrefix, // 数据库表前缀
        ]);

        try{
            $conn->execute('show databases');
        }catch (\Exception $exception){
            $arr['msg'] = "连接数据库失败!";
            echo json_encode($arr);
            exit;
        }

		//读取数据文件
		$sqldata = file_get_contents(MODULE_PATH . 'Data/cms.sql');
		//读取测试数据
		if ($testdata) {
			$sqldataDemo = file_get_contents(MODULE_PATH . 'Data/cms_demo.sql');
			$sqldata = $sqldata . "\r\n" . $sqldataDemo;
		} else {
			//不加测试数据的时候，删除d目录的文件
			try {
				$Dir = new \Dir();
				$Dir->delDir(SITE_PATH . 'd/file/contents/');
			} catch (Exception $exc) {

			}
		}
		$sqlFormat = sql_split($sqldata, $dbPrefix);

		/**
		执行SQL语句
		 */
		$counts = count($sqlFormat);

		for ($i = $n; $i < $counts; $i++) {
			$sql = trim($sqlFormat[$i]);

			if (strstr($sql, 'CREATE TABLE')) {
				preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                $conn->execute("DROP TABLE IF EXISTS `$matches[1]`");
				$ret = $conn->execute($sql);

				if ($ret === 0) {
					$message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成</li> ';
				} else {
					$message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败</li>';
				}
				$i++;
				$arr = array('n' => $i, 'msg' => $message);
				echo json_encode($arr);
				exit;
			} else {
				$ret = $conn->execute($sql);
				$message = '';
				$arr = array('n' => $i, 'msg' => $message);
				//echo json_encode($arr); exit;
			}
		}

		if ($i == 999999) {
			exit;
		}

		//更新配置信息
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='sitename'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='siteurl' ");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$sitefileurl' WHERE varname='sitefileurl' ");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='siteinfo'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='sitekeywords'");
        $conn->execute("UPDATE `{$dbPrefix}config` SET  `value` = '$siteemail' WHERE varname='siteemail'");

		//读取配置文件，并替换真实配置数据
		$strConfig = file_get_contents(MODULE_PATH . 'Data/config.php');
		$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
		$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
		$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
		$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
		$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
		$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
		$strConfig = str_replace('#AUTHCODE#', genRandomString(18), $strConfig);
		$strConfig = str_replace('#COOKIE_PREFIX#', genRandomString(3) . "_", $strConfig);
		$strConfig = str_replace('#DATA_CACHE_PREFIX#', genRandomString(3) . "_", $strConfig);
		@file_put_contents(CONF_PATH . 'dataconfig.php', $strConfig);

		//插入管理员
		//生成随机认证码
		$verify = genRandomString(6);
		$time = time();
		$ip = get_client_ip();
		$password = md5($password . md5($verify));
        $admin_data = [
            'username' => $username,
            'nickname' => '超级管理员',
            'password' => $password,
            'pwdconfirm' => $password,
            'bind_account' => '',
            'last_login_time' => $time,
            'last_login_ip' => $ip,
            'verify' => $verify,
            'email' => I('manager_email'),
            'remark' => '备注信息',
            'create_time' => $time,
            'update_time' => $time,
            'status' => '1',
            'role_id' => '1',
            'info' => '',
        ];
        $UserModel = D('Admin/User');
        $UserModel->delete();
        if($UserModel->create($admin_data)){
            $UserModel->add();
        }else{
            $message = '<strong style="color: red;">添加管理员失败</strong><br/>';
            echo $message;exit();
        }

		$message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
		$arr = array('n' => 999999, 'msg' => $message);
		echo json_encode($arr);
		exit;
	}

	//测试数据库
	public function testdbpwd() {
        $db = new Model('', '', [
            'DB_TYPE' => 'Mysql', // 数据库类型
            'DB_HOST' => $_POST['dbHost'], // 服务器地址
            'DB_NAME' => $_POST['dbName'], // 数据库名
            'DB_USER' => $_POST['dbUser'], // 用户名
            'DB_PWD' => $_POST['dbPwd'], // 密码
            'DB_PORT' => $_POST['dbPort'], // 端口
//            'DB_PREFIX' => '', // 数据库表前缀
        ]);

		$return = '1';
        try{
			$res = $db->execute('show databases');
			$return = $res ? '1' : '0';
        }catch (\Exception $exception){
            $return = '0';
		}
		echo $return;
        exit();
    }

}
