<?php
/**
 * Created by FHYI.
 * Date 2020/10/28
 * Time 11:10
 */

namespace app\demo\controller\admin;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 新建路由标签
 * Class Router
 * @package app\demo\controller\admin
 */
class Router extends AdminController
{
    /**
     * 打开新的iframe 示例
     * @return string
     */
    public function openNewIframe(){
        return View::fetch();
    }
}
