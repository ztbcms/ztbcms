<?php
/**
 * User: jayinton
 * Date: 2020/10/9
 */

namespace app\admin\controller;


use app\common\controller\AdminController;
use think\facade\Db;
use think\Request;

class Module  extends AdminController
{
    //系统模块，隐藏
    const SystemModuleList = ['admin', 'common', 'install', 'attachment', 'template'];

    /**
     * 模块列表
     */
    function index(){
//        return view('index');
//        var_dump($dirs = glob(base_path() . '*'));
        $this->getModuleList();
    }

    function getModuleList(){
        $page = input('page', 1);
        $limit = input('limit', 15);
        //取得模块目录名称
        $dirs = glob(base_path() . '*');
        $dirs_arr = [];

        //tp6下的模块
//        $tpDisrs = glob($this->tpAppPath . "*");
//        foreach ($tpDisrs as $path) {
//            if (is_dir($path) && file_exists($path . '/Config.inc.php')) {
//                $pathName = basename($path);
//                $dirs_arr[ucwords($pathName)] = $path . '/Config.inc.php';
//            }
//        }

        foreach ($dirs as $path) {
            if (is_dir($path)) {
                //目录名称
                $pathName = basename($path);
                //系统模块隐藏
                if (in_array($pathName, self::SystemModuleList)) {
                    continue;
                }
                $dirs_arr[$pathName] = $path . '/Config.inc.php';
            }
        }

        //取得已安装模块列表
//        $moduleList = Db::name('module')->select() ?: [];
//        foreach ($this->moduleList as $v) {
//            $moduleList[$v['module']] = $v;
//            //检查是否系统模块，如果是，直接不显示
//            if ($v['iscore']) {
//                $key = array_keys($dirs_arr, $v['module']);
//                unset($dirs_arr[$key[0]]);
//            }
//        }

        //数量
        $total_items = count($dirs_arr);
        //把一个数组分割为新的数组块
        $dirs_arr = array_chunk($dirs_arr, $limit, true);
        //当前分页
        $page = max($page, 1);
        //根据分页取到对应的模块列表数据
        $directory = $dirs_arr[intval($page - 1)];
//var_dump($directory);
//exit;
        $moduleList = [];
        foreach ($directory as $moduleName => $moduleFilePath) {
            $moduleName = ucwords($moduleName);
            $config = array(
                //模块目录
                'module' => $moduleName,
                //模块名称
                'modulename' => $moduleName,
                //图标地址，远程地址
                'icon' => '',
                //模块介绍地址
                'address' => '',
                //模块简介
                'introduce' => '',
                //模块作者
                'author' => '',
                //作者地址
                'authorsite' => '',
                //作者邮箱
                'authoremail' => '',
                //版本号，请不要带除数字外的其他字符
                'version' => '',
                //适配最低CMS版本，
                'adaptation' => '',
                //签名
                'sign' => '',
                //依赖模块
                'depend' => array(),
                //行为
                'tags' => array(),
                //缓存
                'cache' => array(),
            );
            var_dump($moduleFilePath);
            // Config.inc.php 存在才认为是模块
            if(is_file($moduleFilePath)){
                $moduleConfig = include $moduleFilePath;
                $moduleList[$moduleName] = array_merge($config, $moduleConfig);
            }
        }
        //进行分页
//        $Page = $this->page($count, 10, I('get.page', 1));

//        $this->assign("Page", $Page->show());
//        $this->assign("data", $moduleList);
//        $this->assign("modules", $this->moduleList);
//        $this->display();

        return self::makeJsonReturn(true, [
            'page' => $page,
            'limit' => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items/$limit),
            'items' => $moduleList,
        ]);
    }
}