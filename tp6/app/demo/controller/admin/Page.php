<?php
/**
 * User: jayinton
 */

namespace app\demo\controller\admin;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 页面
 * Class Page
 * @package app\demo\controller\admin
 */
class Page extends AdminController
{
    /**
     * 在线表单
     * @return string
     */
    public function diyForm(){
        return View::fetch('diyForm');
    }

    /**
     * 列表页
     * @return string
     */
    public function list(){
        return View::fetch('list');
    }

    /**
     * 表单页
     * @return string
     */
    public function form(){
        return View::fetch('form');
    }

    /**
     * 图片预览
     * @return string
     */
    public function image(){
        return View::fetch('image');
    }
}
