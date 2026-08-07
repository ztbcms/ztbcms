<?php
/**
 * Created by FHYI.
 * Date 2020/10/28
 * Time 11:49
 */

namespace app\demo\controller\admin;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 图标
 * Class Iconfont
 * @package app\demo\controller\admin
 */
class Iconfont extends AdminController
{
    /**
     * 阿里图标库
     * @return string
     */
    public function aliIconFont()
    {
        return View::fetch('ali_iconfont');
    }

    /**
     * Element图标
     * @return string
     */
    public function elementIconfont()
    {
        return View::fetch('element_iconfont');
    }
}
