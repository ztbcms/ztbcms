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
        $_action = input('_action');
        if (Request::isGet() && $_action == 'getMenuList') {
            return json(MenuService::getMenuList());
        }
        if (Request::isPost() && $_action == 'updateSort') {
            $id = Request::post('id');
            $listorder = Request::post('listorder', 0);
            if(empty($id) || $listorder < 0){
                return json(self::createReturn(false, null, '参数异常'));
            }
            $MenuModel = new MenuModel();
            $MenuModel->where('id', $id)->save(['listorder' => $listorder]);
            return json(self::createReturn(true, null, '更新成功'));
        }
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
     * 添加菜单
     *
     * @return \think\response\Json|\think\response\View
     */
    function menuAdd()
    {
        $_action = input('_action');
        if (Request::isGet() && $_action == 'getMenuList') {
            return json(MenuService::getMenuList());
        }
        if (Request::isPost()) {
            $data = input('post.');
            return json(MenuService::addEditDetails($data));
        }
        return view('menuAddOrEdit');
    }

    /**
     * 编辑菜单
     *
     * @return \think\response\Json|\think\response\View
     */
    function menuEdit()
    {
        $_action = input('_action');
        if (Request::isGet() && $_action == 'getMenuList') {
            return json(MenuService::getMenuList());
        }
        if (Request::isGet() && $_action == 'getDetail') {
            $id = Request::param('id', '', 'trim');
            $res = MenuService::getDetails($id);
            return json($res);
        }
        if (Request::isPost()) {
            $data = input('post.');
            return json(MenuService::addEditDetails($data));
        }
        return view('menuAddOrEdit');
    }

    /**
     * 菜单删除
     *
     * @return \think\response\Json
     */
    function menuDelete()
    {
        $id = Request::param('id', '', 'trim'); //字段
        return json(MenuService::doDelete($id));
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

}
