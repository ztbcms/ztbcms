<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 10:01
 */

namespace app\admin\controller;

use app\admin\model\MenuModel;
use app\admin\service\MenuService;
use app\common\controller\AdminController;
use think\facade\Request;

/**
 * 菜单
 *
 * @package app\admin\controller
 */
class Menu extends AdminController
{

    /**
     * 菜单列表
     *
     * @return string
     */
    function index()
    {
        return view();
    }

    /**
     * 获取菜单列表
     */
    function getMenuList()
    {
        return json(MenuService::getMenuList());
    }

    /**
     * 公共编辑
     */
    public function updateTable()
    {
        $field = Request::param('field', '', 'trim'); //字段
        $value = Request::param('value', '', 'trim');  //值
        $where_name = Request::param('where_name', 'trim'); //条件名称
        $where_value = Request::param('where_value', '', 'trim'); //添加的内容

        $save[$field] = $value;
        $where[$where_name] = $where_value;
        $MenuModel = new MenuModel();
        $MenuModel->where($where)->save($save);

        return json(self::createReturn(true, '', '保存成功'));
    }

    /**
     * 删除菜单
     */
    public function doDelete()
    {
        $id = Request::param('id', '', 'trim'); //字段
        return json(MenuService::doDelete($id));
    }

    /**
     * 菜单视图
     */
    public function details()
    {
        $id = Request::param('id', '', 'trim');
        $parentid = Request::param('parentid', '', 'trim');
        return view('details', ['id' => $id, 'parentid' => (int) $parentid]);
    }

    /**
     * 菜单数据
     */
    public function getDetails()
    {
        $id = Request::param('id', '', 'trim');
        $res = MenuService::getDetails($id);
        return json($res);
    }

    /**
     * 获取模块列表
     */
    public function getModuleList()
    {
        return json(MenuService::getModuleList());
    }

    /**
     * 获取控制器列表
     */
    public function getControllerList()
    {
        $module = Request::param('module');
        $is_tp6 = Request::param('is_tp6');
        return json(MenuService::getControllerList($module, $is_tp6));
    }

    /**
     * 获取方法列表
     */
    public function getActionList()
    {
        $controller = Request::param('controller');
        $is_tp6 = Request::param('is_tp6');
        $app = Request::param('app');
        return json(MenuService::getActionList($controller, $app, $is_tp6));
    }

    /**
     * 添加或者编辑详情
     */
    public function addEditDetails()
    {
        $posts = input('post.');
        return json(MenuService::addEditDetails($posts));
    }
}
