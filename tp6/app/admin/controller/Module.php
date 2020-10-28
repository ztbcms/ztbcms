<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\controller;


use app\admin\libs\module\ModuleInstaller;
use app\admin\libs\module\ModuleUninstaller;
use app\admin\service\ModuleService;
use app\common\controller\AdminController;
use think\facade\Db;

/**
 * 模块
 *
 * @package app\admin\controller
 */
class Module extends AdminController
{
    /**
     * 模块列表
     */
    function index()
    {
        return view('index');
    }

    /**
     * 模块安装页面
     */
    function install()
    {
        $module = input('module', '');
        if (empty($module)) {
            $this->showError('请指定模块');
            return;
        }
        $moduleService = new ModuleService();
        $res = $moduleService->getModuleInfo($module);
        if (!$res) {
            return $res;
        }
        $moduleConfig = $res['data'];
        return view('install', [
            'config' => $moduleConfig
        ]);
    }

    /**
     * 获取模块列表
     *
     * @return \think\response\Json
     */
    function getModuleList()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 15, 'intval');

        $service = new ModuleService();
        $moduleList = $service->getLocalModuleList()['data'];
        //数量
        $total_items = count($moduleList);
        //把一个数组分割为新的数组块
        $dirs_arr = array_chunk($moduleList, $limit, true);
        //当前分页
        $page = max($page, 1);
        //根据分页取到对应的模块列表数据
        $list = $dirs_arr[intval($page - 1)];

        return self::makeJsonReturn(true, [
            'page'        => $page,
            'limit'       => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit),
            'items'       => $list,
        ]);
    }

    // 安装
    function doInstallModule()
    {
        $moduleName = input('module', '', 'strtolower');
        if (empty($moduleName)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $installer = new ModuleInstaller($moduleName);
        $res = $installer->run();
        return json($res);
    }

    // 卸载
    function doUninstallModule()
    {
        $moduleName = input('module', '', 'strtolower');
        if (empty($moduleName)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $uninstaller = new ModuleUninstaller($moduleName);
        $res = $uninstaller->run();
        return json($res);
    }

}