<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\service;


use app\common\service\BaseService;
use think\facade\Db;

/**
 * 模块管理
 * Class ModuleService
 *
 * @package app\admin\service
 */
class ModuleService extends BaseService
{
    /**
     * 是否已安装
     * @param $module
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function isInstall($module)
    {
        $map = $this->getInstallModuleMap()['data'];
        return isset($map[strtolower($module)]);
    }

    /**
     * 安装模块
     * @param $moduleName
     *
     * @return bool
     */
    function install($moduleName)
    {
        return true;
    }

    /**
     * 卸载模块
     * @param $moduleMame
     */
    function uninstall($moduleMame)
    {
        return true;
    }

    /**
     * 获取已安装模块列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getInstallModuleList()
    {
        $moduleList = Db::name('module')->select() ?:[];
        return self::createReturn(true, $moduleList);
    }

    /**
     * 获取已安装的模块对照表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getInstallModuleMap(){
        $list = $this->getInstallModuleList()['data'];
        $map = [];
        foreach ($list as $item){
            $map[strtolower($item['module'])] = $item;
        }

        return self::createReturn(true, $map);
    }


    /**
     * 获取模块信息
     * @param $module
     * @return array
     */
    function getModuleInfo($module)
    {
        $config_file = base_path() . strtolower($module) . '/Config.inc.php';
        if (!file_exists($config_file)) {
            return self::createReturn(false, null, '找不到模块配置文件');
        }
        $moduleConfig = include $config_file;
        $moduleConfig['module'] = strtolower($module);
        $moduleConfig['install_time'] = '';
        $moduleInfo = Db::name('module')->where('module', '=', ucwords($module))->findOrEmpty();
        if ($moduleInfo && isset($moduleInfo['installtime'])) {
            $moduleConfig['install_time'] = date('Y-m-d H:i', $moduleInfo['installtime']);
        }
        return self::createReturn(true, $moduleConfig);
    }

}