<?php
/**
 * Author: jayinton
 */

namespace app\admin\controller;


use app\common\controller\AdminController;

/**
 * iconfont 字体
 *
 * @package app\admin\controller
 */
class Iconfont extends AdminController
{
    public $noNeedPermission = ['index'];

    // 字体列表页
    function index()
    {
        return view('index');
    }
}