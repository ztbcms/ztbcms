<?php

/**
 * 计划任务模块安装
 */

namespace Cron\Install;

use Libs\System\InstallBase;

class Install extends InstallBase {

    //模块地址
    private $path = NULL;

    public function __construct() {
        $this->path = APP_PATH . 'Cron/';
    }

    //安装前进行处理
    public function run() {
//        //检查是否有Cron目录
//        if (file_exists(PROJECT_PATH . 'Cron/')) {
//            if ($this->chechmod(PROJECT_PATH . 'Cron/') == false) {
//                $this->error = '目录 ' . PROJECT_PATH . 'Cron/' . ' 没有可写权限！';
//                return false;
//            }
//        } else {
//            //创建Cron目录
//            if (mkdir(PROJECT_PATH . 'Cron/', 0777, true) == false) {
//                $this->error = '目录 ' . PROJECT_PATH . 'Cron/' . ' 创建失败！';
//                return false;
//            }
//        }
        return true;
    }

    //基本安装结束后的回调
    public function end() {
        //移动默认脚本到Cron目录
//        CMS()->Dir->copyDir(APP_PATH . 'Cron/Install/Script/', PROJECT_PATH . 'Cron/');
        return true;
    }

//    /**
//     * 检查对应目录是否有相应的权限
//     * @param type $path 目录地址
//     * @return boolean
//     */
//    protected function chechmod($path) {
//        //检查模板文件夹是否有可写权限 TEMPLATE_PATH
//        $tfile = "_test.txt";
//        $fp = @fopen($path . $tfile, "w");
//        if (!$fp) {
//            return false;
//        }
//        fclose($fp);
//        $rs = @unlink($path . $tfile);
//        if (!$rs) {
//            return false;
//        }
//        return true;
//    }

}
