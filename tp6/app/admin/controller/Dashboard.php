<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;

class Dashboard extends AdminController
{
    /**
     * 框架页
     * @return string
     */
    function index(){
        return View::fetch('index');
    }

    /**
     * 清理缓存
     */
    function doCleanCache(){

    }
}