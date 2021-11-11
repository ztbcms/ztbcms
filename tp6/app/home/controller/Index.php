<?php

namespace app\home\controller;

use app\BaseController;

class Index extends BaseController
{
    /**
     * 首页
     *
     * @return \think\response\View
     */
    public function index()
    {
        $install_path = root_path('app/install');
        $install_lock_path = $install_path.DIRECTORY_SEPARATOR.'install.lock';
        $enable_install = file_exists($install_path) && !file_exists($install_lock_path);
        try {
            $config = \app\common\model\ConfigModel::getConfigs();
        } catch (\Exception $e) {
        }
        $site_config = [
            'sitename' => $config['sitename'] ?? 'ZTBCMS',
            'siteinfo' => $config['siteinfo'] ?? '高性能、模块化应用开发框架',
        ];
        return view('index', [
            'enable_install' => $enable_install, // 是否允许安装框架
            'site_config'    => $site_config,
        ]);
    }
}
