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
        return true;
    }

    //基本安装结束后的回调
    public function end() {
        return true;
    }

}
