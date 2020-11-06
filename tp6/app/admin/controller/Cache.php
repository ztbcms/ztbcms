<?php
/**
 * User: cycle_3
 * Date: 2020/11/5
 * Time: 16:41
 */

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\App;
use think\facade\View;
use app\common\libs\helper\FileHelper;

class Cache extends AdminController
{

    /**
     * 清除缓存页面
     * @return string
     */
    function cache()
    {
        set_time_limit(0);
        $type = input('type', '', 'trim');
        if ($type == 'site') {
            //删除缓存的所有数据
            $path = App::getRootPath() . 'runtime\\';
            if (file_exists($path)) {
                //判断是否存在缓存的页面文件
                (new FileHelper)->deldir($path);
            }
            return json(self::createReturn(true, '', '清除成功'));
        } else if ($type == 'template') {
            //删除模板缓存
            $path = App::getRootPath() . 'runtime';
            $scandir = scandir($path);
            foreach ($scandir as $k => $v) {
                if ($v != '.' && $v != '..') {
                    if (file_exists($path . '\\' . $v . '\temp')) {
                        //判断是否存在缓存的页面文件
                        (new FileHelper)->deldir($path . '\\' . $v . '\temp\\');
                    }
                }
            }
            return json(self::createReturn(true, '', '清除成功'));
        } else if ($type == 'logs') {
            //网站运行日志
            $path = App::getRootPath() . 'runtime';
            $scandir = scandir($path);
            foreach ($scandir as $k => $v) {
                if ($v != '.' && $v != '..') {
                    if (file_exists($path . '\\' . $v . '\log')) {
                        //判断是否存在缓存的页面文件
                        (new FileHelper)->deldir($path . '\\' . $v . '\log\\');
                    }
                }
            }
            return json(self::createReturn(true, '', '清除成功'));
        } else {
            return View::fetch('cache');
        }
    }



}