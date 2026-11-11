<?php

namespace app\home\controller;

use app\BaseController;
use app\common\model\ConfigModel;
use think\facade\Log;

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
        $installed = file_exists(install_lock_path());
        $enable_install = file_exists($install_path) && !$installed;

        $config = [];
        try {
            $config = ConfigModel::getConfigs();
        } catch (\Throwable $e) {
            // 未安装时数据库不可用属于预期情况，已安装时才记录
            if ($installed) {
                Log::warning('home 首页读取站点配置失败：' . $e->getMessage());
            }
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
