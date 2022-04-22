<?php
/**
 * User: jayinton
 * Date: 2020/10/10
 */

namespace app\admin\libs\module;

use app\common\libs\helper\SqlHelper;
use app\admin\model\MenuModel;
use app\admin\model\ModuleModel;
use app\admin\service\ModuleService;
use app\common\service\BaseService;
use think\facade\Config;
use think\facade\Db;
use think\File;

/**
 * 模块安装器
 * Class ModuleInstaller
 *
 * @package app\admin\libs\module
 */
class ModuleInstaller extends BaseService
{
    protected $module = '';

    public function __construct($module)
    {
        $this->module = $module;
        if (empty($this->module)) {
            throw new \Exception('请指定模块');
        }
    }

    /**
     * 执行
     *
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function run()
    {
        $moduleName = $this->module;
        //设置脚本最大执行时间
        set_time_limit(0);
        $moduleService = new ModuleService();
        // 检测目录权限
        $res = $this->_checkPermission($moduleName);
        if (!$res['status']) {
            return $res;
        }
        //加载模块基本配置
        $res = $moduleService->getModuleInfo($moduleName);
        if (!$res['status']) {
            return $res;
        }
        $config = $res['data'];
        //版本检查
        if ($config['adaptation']) {
            if (version_compare(Config::get('admin.cms_version'), $config['adaptation'], '>=') == false) {
                return self::createReturn(false, null, '该模块要求系统最低版本为：'.$config['adaptation']);
            }
        }
        //依赖模块检测
        if (!empty($config['depend']) && is_array($config['depend'])) {
            $installedModuleMap = $moduleService->getInstallModuleMap()['data'];
            foreach ($config['depend'] as $key => $value) {
                if(is_int($key)){
                    // 没有指定版本的模块
                    $mod = $value;
                    $version = '^1.0.0';
                } else {
                    $mod = $key;
                    $version = $value;
                }
                if (!isset($installedModuleMap[$mod])) {
                    return self::createReturn(false, null, "缺少依赖模块 {$mod}");
                }
                if (!empty($version)) {
                    $modInfo = $installedModuleMap[$mod];
                    if (version_compare($modInfo['version'], $version) == -1) {
                        return self::createReturn(false, null, "依赖模块 {$mod} 版本不兼容");
                    }
                }
            }
        }
        //检查模块是否已经安装
        if ($moduleService->isInstall($moduleName)) {
            return self::createReturn(false, null, "该模块已经安装，无法重复安装");
        }

        $moduleModel = new ModuleModel();
        $data = [
            'module'      => ucwords($config['module']),
            'modulename'  => $config['modulename'],
            'sign'        => $config['sign'] ?? '',
            'iscore'      => isset($config['iscore']) ? $config['iscore'] : 0,
            'disabled'    => 0,
            'version'     => $config['version'],
            'setting'     => isset($config['setting']) ? json_encode($config['setting']) : '',
            'installtime' => time(),
            'updatetime'  => time(),
            'listorder'   => 0,
        ];
        $res = $moduleModel->insert($data);
        if (!$res) {
            return self::createReturn(false, null, "安装初始化失败");
        }

        //执行数据库脚本安装
        $res = $this->_runSQL($moduleName, 'install');
        if (!$res['status']) {
            $this->_installRollback($moduleName);
            return $res;
        }

        //执行菜单项安装
        $res = $this->_installMenu($moduleName, $config);
        if (!$res['status']) {
            $this->_installRollback($moduleName);
            return $res;
        }

        // TODO 执行安装脚本
        $this->_runInstallScript($moduleName, 'install');

        //静态资源文件
        $this->_copyResource($moduleName);

        //安装结束，最后调用安装脚本完成
        $this->_runInstallScriptEnd($moduleName, 'install');
        return self::createReturn(true, null, '安装完成');
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
     *
     * @param  string  $moduleName
     * @param  string  $dir
     *
     * @return array
     */
    function _runSQL($moduleName = '', $dir = 'install')
    {
        if (empty($moduleName)) {
            return self::createReturn(false, null, '模块名称不能为空');
        }
        $fileName=ucfirst("{$moduleName}.sql"); //文件需要首字母大写
        //sql文件
        $path = base_path().strtolower("{$moduleName}/{$dir}/")."{$fileName}";

        if (!file_exists($path)) {
            return self::createReturn(true, null, 'sql文件不存在，无需安装');
        }
        $sql = file_get_contents($path);
        $sql = $this->_resolveSQL($sql);
        if (!empty($sql) && is_array($sql)) {
            foreach ($sql as $sql_split) {
                Db::execute($sql_split);
            }
        }
        return self::createReturn(true, null, 'sql安装完成');
    }

    /**
     * 处理sql语句，执行替换前缀都功能
     *
     * @param  string  $sql  原始的sql
     *
     * @return array
     */
    function _resolveSQL($sql)
    {
        // 前缀
        $tablepre = Config::get('database.connections.'.Config::get('database.default').'.prefix');
        return SqlHelper::splitSQL($sql, $tablepre);
    }

    /**
     * 安装菜单
     * @param $moduleName
     * @param $moduleConfig
     *
     * @return array
     */
    function _installMenu($moduleName, $moduleConfig)
    {
        if (empty($moduleName)) {
            return self::createReturn(false, null, '模块名称不能为空');
        }
        $path = base_path().strtolower("{$moduleName}/install/")."Menu.php";
        //检查是否有安装脚本
        if (!file_exists($path)) {
            return self::createReturn(true, null, '不存在Menu.php,无需安装');
        }
        $menu = include $path.'';

        if (empty($menu)) {
            return self::createReturn(true, null, '菜单为空，安装完成');
        }
        $menuModel = new MenuModel();
        return $menuModel->installModuleMenu($menu, $moduleConfig);
    }

    /**
     * 安装回滚
     *
     * @param $moduleName
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function _installRollback($moduleName)
    {
        if (empty($moduleName)) {
            return self::createReturn(false, null, '模块名称不能为空');
        }
        //删除安装状态
        Db::name('module')->where('module', $moduleName)->delete();
        return self::createReturn(true, null, '安装回滚完成');
    }

    // TODO 执行 install.php 前置方法
    function _runInstallScript($moduleName, $type = '')
    {
        return true;
    }

    // TODO 执行 install.php的后置方法
    function _runInstallScriptEnd($moduleName, $type = '')
    {
        return true;
    }

    /**
     * 资源迁移
     *
     * @param $moduleName
     *
     * @return array
     */
    function _copyResource($moduleName)
    {
        if (empty($moduleName)) {
            return self::createReturn(false, null, '模块名称不能为空');
        }
        $resource_dir = base_path()."{$moduleName}/install/extra/";
        if (file_exists($resource_dir)) {
            $dir = new File($resource_dir, false);
            if ($dir->isDir()) {
                $des_dir = public_path().'statics/extra/'.strtolower($moduleName).'/';
                $this->_copyDir($resource_dir, $des_dir);
            }
        }
        return self::createReturn(true, null, '资源安装完成');
    }

    /**
     * 复制目录
     *
     * @param $source
     * @param $destination
     *
     * @return array
     */
    function _copyDir($source, $destination)
    {
        if (is_dir($source) == false) {
            return BaseService::createReturn(false, null, '源目录不存在');
        }
        if (is_dir($destination) == false) {
            mkdir($destination, 0700, true);
        }
        $handle = opendir($source);
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                is_dir("$source/$file")
                    ?
                    $this->_copyDir("$source/$file", "$destination/$file")
                    :
                    copy("$source/$file", "$destination/$file");
            }
        }
        closedir($handle);

        return BaseService::createReturn(true, null, '操作完成');
    }

    /**
     * 删除目录（包括下面的文件）
     *
     * @param $directory
     *
     * @return array
     */
    function _delDir($directory)
    {
        if (is_dir($directory) == false) {
            return BaseService::createReturn(false, null, '目录不存在');
        }
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                is_dir("$directory/$file")
                    ?
                    $this->_delDir("$directory/$file")
                    :
                    unlink("$directory/$file");
            }
        }
        if (readdir($handle) == false) {
            closedir($handle);
            rmdir($directory);
        }
        return BaseService::createReturn(true, null, '操作完成');
    }


}
