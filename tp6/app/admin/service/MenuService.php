<?php
/**
 * Created by PhpStorm.
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 10:35
 */

namespace app\admin\service;

use app\admin\model\MenuModel;
use app\admin\validate\Menu;
use app\common\service\BaseService;
use think\exception\ValidateException;

/**
 * 菜单管理
 * Class MenuService
 * @package app\admin\service
 */
class MenuService extends BaseService
{

    /**
     * 获取菜单列表
     * @return array
     */
    static function getMenuList()
    {
        $MenuModel = new MenuModel();
        $menu = $MenuModel->order(array("listorder" => "ASC"))->select();
        $menu = self::getTree($menu);
        foreach ($menu as $k => $v) {
            $menu[$k]['name'] = str_repeat("ㄧㄧ", $v['level']).' '.$v['name'];
        }
        return self::createReturn(true, $menu, '获取成功');
    }

    /**
     * 获取详情
     * @param $id
     * @return array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getDetails($id = 0){
        $MenuModel = new MenuModel();
        $info = $MenuModel->where('id', $id)->find();
        $info['parentid'] = (string)$info['parentid'];
        $info['status'] = (string)$info['status'];
        $info['type'] = (string)$info['type'];
        return self::createReturn(true,$info);
    }

    /**
     * 删除菜单详情
     * @param $id
     * @return array
     */
    static function doDelete($id)
    {
        $MenuModel = new MenuModel();
        $count = $MenuModel->where(array("parentid" => $id))->count();
        if ($count > 0) {
            return self::createReturn(false, '', '该菜单下还有子菜单，无法删除！');
        }
        if ($MenuModel->where(['id' => $id])->delete() !== false) {
            return self::createReturn(true, '', '删除菜单成功！');
        } else {
            return self::createReturn(false, '', '删除失败！');
        }
    }

    /**
     * 获取所有模块
     * todo 兼容tp3模块
     * @return array
     */
    static function getModuleList()
    {

        $appPath = getcwd().'/app/Application/';
        $tpAppPath = getcwd().'/tp6/app/';

        //取得模块目录名称
        $dirs = glob($appPath.'*');
        $all_module = [];

        //tp6下的模块
        $tpDisrs = glob($tpAppPath."*");
        foreach ($tpDisrs as $path) {
            if (is_dir($path) && file_exists($path.'/Config.inc.php')) {
                $path = basename($path);
                $all_module[] = [
                    'name'   => ucwords($path),
                    'is_tp6' => 1
                ];
            }
        }

        foreach ($dirs as $path) {
            if (is_dir($path)) {
                //目录名称
                $path = basename($path);
                $all_module[] = [
                    'name'   => $path,
                    'is_tp6' => 0
                ];
            }
        }
        return self::createReturn(true, $all_module);
    }

    /**
     * 获取模型下的控制器
     * todo 兼容tp3模块
     * @param $module
     * @param  int  $is_tp6
     * @return array
     */
    static function getControllerList($module, $is_tp6 = 0)
    {

        if (empty($module)) {
            return self::createReturn(true, []);
        }

        $appPath = getcwd().'/app/Application/';
        $tpAppPath = getcwd().'/tp6/app/';

        if ($is_tp6 == 1) {
            $module_path = $tpAppPath.strtolower($module.'/Controller').'/';  //控制器路径
            $module_path .= '/*.php';
        } else {
            $module_path = $appPath.'/'.$module.'/Controller/';  //控制器路径
            $module_path .= '/*.class.php';
        }

        $ary_files = glob($module_path);
        $data[] = '%';
        foreach ($ary_files as $file) {
            if (is_dir($file)) {
                continue;
            } else {
                if ($is_tp6 == 0) {
                    $data[] = basename($file, 'Controller.class.php');
                } else {
                    $data[] = basename($file, '.php');
                }
            }
        }
        return self::createReturn(true, $data);
    }

    /**
     * 获取控制器下的方法
     * todo 兼容tp3模块
     * @param  string  $controller
     * @return array
     */
    static function getActionList($controller = '', $app = '', $is_tp6 = 1)
    {
        $data[] = '%';
        if (empty($controller)) {
            return self::createReturn(true, $data);
        }

        if (empty($app)) {
            return self::createReturn(true, $data);
        }

        if ($is_tp6) {
            //tp6 的模块
            $content = file_get_contents('tp6/app/'.$app.'/controller/'.$controller.'.php');
        } else {
            //tp3 的模块
            $content = file_get_contents('app/Application/'.$app.'/Controller/'.$controller.'Controller.class.php');
        }

        preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
        $functions = $matches[1];

        //排除部分方法
        $inherents_functions = array(
            '_initialize', '__construct', 'getActionName', 'isAjax', 'display', 'show', 'fetch', 'buildHtml', 'assign',
            '__set', 'get', '__get', '__isset', '__call', 'error', 'success', 'ajaxReturn', 'redirect', '__destruct',
            '_empty', 'logo', 'page', 'createReturn', 'app', 'initSite', 'getModelObject', 'basePage', 'baseAdd',
            'baseEdit', 'baseDelete', 'verify', 'theme'
        );

        foreach ($functions as $func) {
            $func = trim($func);
            if (!in_array($func, $inherents_functions)) {
                $data[] = $func;
            }
        }
        return self::createReturn(true, $data);
    }

    /**
     * 递归实现无限极分类
     * @param $array
     * @param  int  $pid  父ID
     * @param  int  $level  分类级别
     * @return array 分好类的数组 直接遍历即可 $level可以用来遍历缩进
     */
    static function getTree($array, $pid = 0, $level = 0)
    {
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];
        foreach ($array as $key => $value) {
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parentid'] == $pid) {
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                self::getTree($array, $value['id'], $level + 1);
            }
        }
        return $list;
    }

    /**
     * 添加或者编辑菜单
     * @param  array  $posts
     * @return array
     */
    static function addEditDetails($posts = []){
        $MenuModel = new MenuModel();

        try {
            validate(Menu::class)->check($posts);
            if($posts['id']) {
                //编辑菜单
                if ($MenuModel->where('id',$posts['id'])->save($posts) !== false) {
                    return self::createReturn(true,'','操作成功');
                } else {
                    return self::createReturn(false,'',$MenuModel->getError());
                }
            } else {
                //添加菜单
                if($MenuModel->create($posts)) {
                    return self::createReturn(true,'','操作成功');
                } else {
                    return self::createReturn(false,'',$MenuModel->getError());
                }
            }
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return self::createReturn(false,'',$e->getError());
        }
    }
}
