<?php
/**
 * User: jayinton
 * Date: 2020/10/10
 */

namespace app\admin\libs\module;

use app\common\service\BaseService;

/**
 * 模块安装器
 * Class ModuleInstaller
 *
 * @package app\admin\libs\module
 */
class ModuleInstaller extends BaseService
{
    private $module = '';

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run()
    {
        defined('INSTALL') or define("INSTALL", true);
        if (empty($this->module)) {
            return self::createReturn(false, null, '请选择需要安装的模块');
        }
        $moduleName = $this->module;
        //已安装模块列表
        $moduleList = cache('Module');
        //设置脚本最大执行时间
        set_time_limit(0);

        $res = $this->_checkPermission($moduleName);
        if (!$res['status']) {
            return $res;
        }
        //加载模块基本配置
        $config = $this->config($moduleName);

        //版本检查
        if ($config['adaptation']) {
            if (version_compare(CMS_VERSION, $config['adaptation'], '>=') == false) {
                $this->error = '该模块要求系统最低版本为：'.$config['adaptation'].'！';
                return false;
            }
        }
        //依赖模块检测
        if (!empty($config['depend']) && is_array($config['depend'])) {
            foreach ($config['depend'] as $mod) {
                if ('Content' == $mod) {
                    continue;
                }
                if (!isset($moduleList[$mod])) {
                    $this->error = "安装该模块，需要安装依赖模块 {$mod} !";
                    return false;
                }
            }
        }
        //检查模块是否已经安装
        if ($this->isInstall($moduleName)) {
            $this->error = '该模块已经安装，无法重复安装！';
            return false;
        }

        $model = D('Common/Module');
        C('TOKEN_ON', false);
        if (!$model->create($config, 1)) {
            $this->error = $model->getError() ?: '安装初始化失败！';
            return false;
        }
        if ($model->add() == false) {
            $this->error = '安装失败！';
            return false;
        }

        //执行数据库脚本安装
        $this->_runSQL($moduleName);
        //执行菜单项安装
        if ($this->installMenu($moduleName) !== true) {
            $this->installRollback($moduleName);
            return false;
        }

        if (!$this->isTp6) {
            //执行安装脚本【tp6模块不执行该脚本，默认不增加Install.class.php】
            if (!$this->runInstallScript($moduleName)) {
                $this->installRollback($moduleName);
                return false;
            }

            //缓存注册 【tp6模块不执行该脚本】
            if (!empty($config['cache'])) {
                if (D('Common/Cache')->installModuleCache($config['cache'], $config) !== true) {
                    $this->error = D('Common/Cache')->getError();
                    $this->installRollback($moduleName);
                    return false;
                }
            }
            $Dir = new \Dir();
            //前台模板 【tp6模块不执行该脚本，默认不支持前端模块】
            if (file_exists($this->appPath."{$moduleName}/Install/Template/")) {
                //拷贝模板到前台模板目录中去
                $Dir->copyDir($this->appPath."{$moduleName}/Install/Template/", $this->templatePath);
            }
            //静态资源文件
            if (file_exists($this->appPath."{$moduleName}/Install/Extres/")) {
                //拷贝模板到前台模板目录中去
                $Dir->copyDir($this->appPath."{$moduleName}/Install/Extres/", $this->extresPath.strtolower($moduleName).'/');
            }
            //安装行为
            if (!empty($config['tags'])) {
                D('Common/Behavior')->moduleBehaviorInstallation($moduleName, $config['tags']);
            }
        }

        //安装结束，最后调用安装脚本完成
        $this->runInstallScriptEnd($moduleName);
        //更新缓存
        cache('Module', null);
        return true;
    }

    /**
     * 目录权限检查
     *
     * @param  string  $moduleName  模块名称
     */
    function _checkPermission($moduleName = '')
    {
        //模板目录权限检测
//        if ($this->chechmod($this->templatePath) == false) {
//            $this->error = '目录 ' . $this->templatePath . ' 没有可写权限！';
//            return false;
//        }
//        if ($moduleName && file_exists($this->extresPath . $moduleName)) {
//            if ($this->chechmod($this->extresPath . $moduleName) == false) {
//                $this->error = '目录 ' . $this->extresPath . $moduleName . ' 没有可写权限！';
//                return false;
//            }
//        }
//        //静态资源目录权限检测
//        if (!file_exists($this->extresPath)) {
//            //创建目录
//            if (mkdir($this->extresPath, 0777, true) == false) {
//                $this->error = '目录 ' . $this->extresPath . ' 创建失败，请检查是否有可写权限！';
//                return false;
//            }
//        }
//        //权限检测
//        if ($this->chechmod($this->extresPath) == false) {
//            $this->error = '目录 ' . $this->extresPath . ' 没有可写权限！';
//            return false;
//        }
        return self::createReturn(true, null, '校验通过');
    }

    /**
     * 执行安装数据库脚本
     * @param string $moduleName
     * @param string $Dir
     * @return bool
     */
    private function _runSQL($moduleName = '', $Dir = 'install')
    {
        if (empty($moduleName)) {
            if ($this->moduleName) {
                $moduleName = $this->moduleName;
            } else {
                $this->error = '模块名称不能为空！';
                return false;
            }
        }
        if ($this->isTp6) {
            //如果是tp6模板，目录是全小写
            $path = $this->tp6AppPath . strtolower("{$moduleName}/{$Dir}/") . "{$moduleName}.sql";
        } else {
            $path = $this->appPath . "{$moduleName}/{$Dir}/{$moduleName}.sql";
        }
        if (!file_exists($path)) {
            return true;
        }
        $sql = file_get_contents($path);
        $sql = $this->resolveSQL($sql, C("DB_PREFIX"));
        if (!empty($sql) && is_array($sql)) {
            foreach ($sql as $sql_split) {
                M()->execute($sql_split);
            }
        }
        return true;
    }

}