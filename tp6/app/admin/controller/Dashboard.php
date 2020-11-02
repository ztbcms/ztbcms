<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 仪表盘
 * Class Dashboard
 *
 * @package app\admin\controller
 */
class Dashboard extends AdminController
{
    protected $noNeedPermission = ['index'];

    /**
     * 框架页
     * @return string
     */
    function index(){
        return View::fetch('index');
    }
}