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

class Module extends AdminController
{
    //系统模块，隐藏
    const SystemModuleList = ['admin', 'common', 'install', 'attachment', 'template'];

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
    function install(){
        $module = input('module', '');
        if(empty($module)){
            $this->showError('请指定模块');
            return;
        }
        $moduleService = new ModuleService();
        $res = $moduleService->getModuleInfo($module);
        if(!$res){
            return $res;
        }
        $moduleConfig = $res['data'];
        return view('install', [
            'config' => $moduleConfig
        ]);
    }

    /**
     * 获取模块列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getModuleList()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 15, 'intval');
        //取得模块目录名称
        $dirs = glob(base_path().'*');
        $dirs_arr = [];

        foreach ($dirs as $path) {
            if (is_dir($path)) {
                //目录名称
                $pathName = basename($path);
                //系统模块隐藏
                if (in_array($pathName, self::SystemModuleList)) {
                    continue;
                }
                $dirs_arr[$pathName] = $path.'/Config.inc.php';
            }
        }

        //取得已安装模块列表
        $installedModuleList = Db::name('module')->select() ?: [];
        $installedModuleMap = [];
        foreach ($installedModuleList as $module) {
            $installedModuleMap[strtolower($module['module'])] = $module;
        }

        //数量
        $total_items = count($dirs_arr);
        //把一个数组分割为新的数组块
        $dirs_arr = array_chunk($dirs_arr, $limit, true);
        //当前分页
        $page = max($page, 1);
        //根据分页取到对应的模块列表数据
        $directory = $dirs_arr[intval($page - 1)];

        $moduleList = [];
        foreach ($directory as $moduleName => $moduleFilePath) {
            $moduleName = ucwords($moduleName);
            $moduleInfo = isset($installedModuleMap[strtolower($moduleName)]) ? $installedModuleMap[strtolower($moduleName)] : null;
            $config = array(
                //模块目录
                'module'       => $moduleName,
                //模块名称
                'modulename'   => $moduleName,
                //图标地址，远程地址
                'icon'         => '',
                //模块介绍地址
                'address'      => '',
                //模块简介
                'introduce'    => '',
                //模块作者
                'author'       => '',
                //作者地址
                'authorsite'   => '',
                //作者邮箱
                'authoremail'  => '',
                //版本号，请不要带除数字外的其他字符
                'version'      => '',
                //适配最低CMS版本，
                'adaptation'   => '',
                //签名
                'sign'         => '',
                //依赖模块
                'depend'       => array(),
                //行为
                'tags'         => array(),
                //缓存
                'cache'        => array(),
                // 安装时间
                'install_time' => $moduleInfo && isset($moduleInfo['installtime']) ? date('Y-m-d H:i', $moduleInfo['installtime']) : ''
            );
            // Config.inc.php 存在才认为是模块
            if (is_file($moduleFilePath)) {
                $moduleConfig = include $moduleFilePath;
                $moduleList[] = array_merge($config, $moduleConfig);
            }
        }
        //进行分页

        return self::makeJsonReturn(true, [
            'page'        => $page,
            'limit'       => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit),
            'items'       => $moduleList,
        ]);
    }

    // 安装
    function doInstallModule(){
        $moduleName = input('module', '', 'strtolower');
        if(empty($moduleName)){
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $installer = new ModuleInstaller($moduleName);
        $res = $installer->run();
        return json($res);
    }

    // 卸载
    function doUninstallModule(){
        $moduleName = input('module', '', 'strtolower');
        if(empty($moduleName)){
            return self::makeJsonReturn(false, null, '参数异常');
        }
        $uninstaller = new ModuleUninstaller($moduleName);
        $res = $uninstaller->run();
        return json($res);
    }

}