<?php

namespace app\demo\controller\admin;

use think\facade\View;
use app\common\controller\AdminController;

/**
 * 上传示例
 * Class Upload
 * @package app\demo\controller\admin
 */
class Upload extends AdminController
{
    /**
     * 上传示例
     * @return string
     */
    public function demo()
    {
        return View::fetch('demo');
    }

    /**
     * 直传示例
     * @return string
     */
    public function directUploadDemo()
    {
        return View::fetch('direct_upload_demo');
    }
}
