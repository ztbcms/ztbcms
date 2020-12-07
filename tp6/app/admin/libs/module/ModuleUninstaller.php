<?php
/**
 * User: jayinton
 * Date: 2020/10/10
 */

namespace app\admin\libs\module;


use app\admin\service\ModuleService;
use think\exception\ErrorException;
use think\facade\Db;

/**
 * 模块卸载器
 * Class ModuleUninstaller
 *
 * @package app\admin\libs\module
 */
class ModuleUninstaller extends ModuleInstaller
{
    /**
     * 执行
     *
     * @return array|int|string
     * @throws \think\db\exception\DbException
     */
    function run()
    {
        $moduleName = $this->module;
        //设置脚本最大执行时间
        set_time_limit(0);
        // 检测目录权限
        $res = $this->_checkPermission($moduleName);
        if (!$res['status']) {
            return $res;
        }
        $moduleService = new ModuleService();
        //取得该模块数据库中记录的安装信息
        $moduleInfo = $moduleService->getModuleInfo($moduleName)['data'];
        if (empty($moduleInfo['install_time'])) {
            return self::createReturn(true, null, '该模块未安装，无需卸载');
        }
        //删除安装记录
        Db::name('module')->where('module', $moduleName)->delete();
        //执行卸载脚本
        $this->_runInstallScript($moduleName, 'uninstall');
        //删除菜单项
        $this->_uninstallMenu($moduleName);
        //执行数据库脚本安装
        $this->_runSQL($moduleName, 'uninstall');
        //静态资源移除
        $this->_removeResource($moduleName);
        //卸载结束，最后调用卸载脚本完成
        $this->_runInstallScriptEnd($moduleName, 'uninstall');

        return self::createReturn(true, null, '卸载完成');
    }

    /**
     * 卸载菜单
     *
     * @param $moduleName
     *
     * @throws \think\db\exception\DbException
     */
    function _uninstallMenu($moduleName)
    {
        //删除权限
        Db::name('access')->where('app', $moduleName)->delete();
        //移除菜单项和权限项
        Db::name('menu')->where([
            ['app', '=', $moduleName],
        ])->delete();
    }

    /**
     * 删除资源
     *
     * @param $moduleName
     */
    function _removeResource($moduleName)
    {
        $des_dir = public_path().'statics/extra/'.strtolower($moduleName).'/';
        $this->_delDir($des_dir);
    }
}