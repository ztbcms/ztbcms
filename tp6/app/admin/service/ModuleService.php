<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\service;


use app\admin\libs\module\ModuleInstaller;
use app\admin\libs\module\ModuleUninstaller;
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
    //系统模块，隐藏
    const SystemModuleList = ['admin', 'common', 'install', 'attachment', 'template'];

    /**
     * 是否已安装
     *
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
     *
     * @param $module
     *
     */
    function install($module)
    {
        $moduleName = strtolower($module);
        if (empty($moduleName)) {
            return self::createReturn(false, null, '参数异常');
        }
        $installer = new ModuleInstaller($moduleName);
        return $installer->run();
    }

    /**
     * 卸载模块
     *
     * @param $moduleMame
     */
    function uninstall($module)
    {
        $moduleName = strtolower($module);
        if (empty($moduleName)) {
            return self::createReturn(false, null, '参数异常');
        }
        $installer = new ModuleUninstaller($moduleName);
        return $installer->run();
    }

    /**
     * 获取已安装模块列表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getInstallModuleList()
    {
        $moduleList = Db::name('module')->select() ?: [];
        return self::createReturn(true, $moduleList);
    }

    /**
     * 获取已安装的模块对照表
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getInstallModuleMap()
    {
        $list = $this->getInstallModuleList()['data'];
        $map = [];
        foreach ($list as $item) {
            $map[strtolower($item['module'])] = $item;
        }

        return self::createReturn(true, $map);
    }


    /**
     * 获取模块信息
     *
     * @param $module
     *
     * @return array
     */
    function getModuleInfo($module)
    {
        $config_file = base_path().strtolower($module).'/Config.inc.php';
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

    function getLocalModuleList($page = 1, $limit = 1000)
    {
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

        $installedModuleMap = $this->getInstallModuleMap()['data'];
        $moduleList = [];
        foreach ($dirs_arr as $moduleName => $moduleFilePath) {
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

        return self::createReturn(true, $moduleList);
    }

}