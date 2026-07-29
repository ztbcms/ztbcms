<?php
/**
 * Created by FHYI.
 * Date 2020/10/28
 * Time 11:25
 */

namespace app\demo\controller\admin;

use app\common\controller\AdminController;
use think\facade\View;

/**
 * 图片处理
 * Class ImageProcess
 * @package app\demo\controller\admin
 */
class ImageProcess extends AdminController
{
    /**
     * 图片处理
     * @return string
     */
    public function index()
    {
        return View::fetch('admin/image_process/index');
    }

    /**
     * 生成分享海报（已移除 intervention/image 依赖）
     * @return \think\response\Json
     */
    public function createSharePoster()
    {
        return json(self::createReturn(false, null, '图片合成功能已移除（intervention/image 依赖已卸载）'));
    }
}
