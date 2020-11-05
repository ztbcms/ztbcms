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
                $this->deldir($path);
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
                        $this->deldir($path . '\\' . $v . '\temp\\');
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
                        $this->deldir($path . '\\' . $v . '\log\\');
                    }
                }
            }
            return json(self::createReturn(true, '', '清除成功'));
        } else {
            return View::fetch('cache');
        }
    }


    /**
     * 删除文件
     * @param $path
     */
    private function deldir($path)
    {
        //如果是目录则继续
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . $val)) {
                        //子目录中操作删除文件夹和文件
                        $this->deldir($path . $val . '/');
                        //目录清空后删除空文件夹
                        @rmdir($path . $val . '/');
                    } else {
                        //如果是文件直接删除
                        unlink($path . $val);
                    }
                }
            }
        }
    }


}