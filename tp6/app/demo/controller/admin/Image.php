<?php
/**
 * Created by FHYI.
 * Date 2020/10/31
 * Time 15:33
 */

namespace app\demo\controller\admin;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 图片裁剪
 * Class Image
 * @package app\demo\controller\admin
 */
class Image extends AdminController
{
    /**
     * 图片裁剪示例
     * @return string
     */
    public function index(){
        return View::fetch();
    }

    /**
     * 图片裁剪面板（iframe 弹窗内打开）
     * @return string
     */
    public function cropImage(){
        return View::fetch();
    }
}
