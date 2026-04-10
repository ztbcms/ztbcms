<?php

/**
 * User: jayinton
 */

namespace app\install\controller;


use app\BaseController;

class Index extends BaseController
{
    protected $middleware = [
        \think\middleware\SessionInit::class,
    ];

    protected function initialize()
    {
        parent::initialize();

        //检查是否已经安装过
        if (is_file(install_lock_path())) {
            response('你已经安装过该系统，如果想重新安装，请先删除站点 install.lock 文件，然后再安装。')->send();
            exit;
        }
    }

    // 安装向导
    function index()
    {
        return view('index');
    }

    function step2()
    {
        $installService = new \app\install\service\InstallService();
        $envRes = $installService->checkEnvironment();

        $err = $envRes['err_count'];
        $items = $envRes['items'];

        // mysql检测
        $mysql = '请自行检查';
        $mini_php_version = '7.2';

        $php_version_info = '';
        $uploadSize = '';
        $session = '';
        $folderInfo = [];
        $function = [];

        foreach ($items as $item) {
            $spanClass = $item['status'] ? '<span class="correct_span">&radic;</span> ' : '<span class="correct_span error_span">&radic;</span> ';

            if ($item['name'] === 'PHP版本') {
                $php_version_info = $spanClass . $item['current'];
            } elseif ($item['name'] === '文件上传') {
                $uploadSize = $spanClass . ($item['current'] === '禁止' ? '禁止上传' : $item['current']);
            } elseif ($item['name'] === 'Session') {
                $session = $spanClass . $item['current'];
            } elseif (strpos($item['name'], '目录权限: ') === 0) {
                $dir = str_replace('目录权限: ', '', $item['name']);
                $folderInfo[] = [
                    'dir'         => $dir,
                    'is_readable' => strpos($item['current'], '不可读') === false ? '<span class="correct_span">&radic;</span>可读' : '<span class="correct_span error_span">&radic;</span>不可读',
                    'is_writable' => strpos($item['current'], '不可写') === false ? '<span class="correct_span">&radic;</span>可写' : '<span class="correct_span error_span">&radic;</span>不可写',
                ];
            } elseif (strpos($item['name'], '核心函数: ') === 0) {
                $func = str_replace('核心函数: ', '', $item['name']);
                $function[] = [
                    'name'  => $func,
                    'value' => $item['status']
                ];
            }
        }

        return view('step2', [
            'os'               => PHP_OS,
            'function'         => $function,
            'err'              => $err,
            'phpv'             => $php_version_info,
            'mini_php_version' => $mini_php_version,
            'mysql'            => $mysql,
            'uploadSize'       => $uploadSize,
            'session'          => $session,
            'folderInfo'       => $folderInfo
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

    function step5()
    {
        touch(install_lock_path());
        return view('step5');
    }

    /**
     * 测试链接数据库（添加测试数据库链接）
     *
     * @return \think\response\Json
     */
    public function testdbpwd()
    {
        $config = [
            'db_host'   => input('db_host', '', 'trim'),
            'db_name'   => input('db_name', '', 'trim'),
            'db_user'   => input('db_user', '', 'trim'),
            'db_pwd'    => input('db_pwd', '', 'trim'),
            'db_port'   => input('db_port', '', 'trim'),
            'db_prefix' => input('db_prefix', '', 'trim'),
        ];
        $installService = new \app\install\service\InstallService();
        $res = $installService->testDbConnection($config);
        return self::makeJsonReturn($res['status'], null, $res['msg']);
    }

    //数据库安装
    function doInstall()
    {
        $n = input('get.n', 0, 'intval');

        $config = [
            'db_host'   => input('db_host', '', 'trim'),
            'db_port'   => input('db_port', '', 'trim'),
            'db_name'   => input('db_name', '', 'trim'),
            'db_user'   => input('db_user', '', 'trim'),
            'db_pwd'    => input('db_pwd', '', 'trim'),
            'db_prefix' => input('db_prefix', '', 'trim'),

            'manager'       => input('manager', '', 'trim'),
            'manager_pwd'   => input('manager_pwd', '', 'trim'),
            'manager_email' => input('manager_email', '', 'trim'),

            'sitename'     => input('sitename'),
            'siteurl'      => input('siteurl'),
            'siteinfo'     => input('siteinfo', '', 'trim'),
            'sitekeywords' => input('sitekeywords', '', 'trim'),

            'ip' => request()->ip()
        ];

        $installService = new \app\install\service\InstallService();
        $res = $installService->executeInstallStep($config, $n);

        return self::makeJsonReturn($res['status'], $res['data'], $res['msg']);
    }
}
