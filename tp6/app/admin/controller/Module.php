<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\controller;


use app\admin\libs\module\ModuleInstaller;
use app\admin\libs\module\ModuleUninstaller;
use app\admin\service\ModuleService;
use app\admin\service\UserOperateLogService;
use app\common\controller\AdminController;
use think\facade\Db;
use think\facade\Request;

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
        $action = input('_action');
        if ($this->request->isGet() && $action == 'getModuleList') {
            return $this->_getModuleList();
        }
        return view('index');
    }

    /**
     * 获取模块列表
     *
     * @return \think\response\Json
     */
    private function _getModuleList()
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

        if($dirs_arr) {
            $list = $dirs_arr[intval($page - 1)];
        } else {
            $list = [];
        }
        return self::makeJsonReturn(true, [
            'page'        => $page,
            'limit'       => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit),
            'items'       => $list,
        ]);
    }

    /**
     * 模块安装页面
     */
    function install()
    {
        $module = input('module', '');
        $action = input('_action', '');
        if (Request::isGet() && $action == 'getDetail') {
            if (empty($module)) {
                return self::makeJsonReturn(false, null, '请指定模块');
            }
            $moduleService = new ModuleService();
            $res = $moduleService->getModuleInfo($module);
            return json($res);
        }

        return view('install');
    }

    // 安装
    function doInstallModule()
    {
        $moduleName = input('module', '', 'strtolower');
        if (empty($moduleName)) {
            return self::makeJsonReturn(false, null, '参数异常');
        }
        UserOperateLogService::addUserOperateLog([
            'source_type' => 'admin_module',
            'source'      => $moduleName,
            'content'     => '安装模块 '.$moduleName
        ]);
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
        UserOperateLogService::addUserOperateLog([
            'source_type' => 'admin_module',
            'source'      => $moduleName,
            'content'     => '卸载模块 '.$moduleName
        ]);
        $uninstaller = new ModuleUninstaller($moduleName);
        $res = $uninstaller->run();
        return json($res);
    }

}