<?php

// +----------------------------------------------------------------------
// |  后台框架首页
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;
use Libs\Helper\MysqlHelper;
use Think\Model;

class MainController extends AdminBase {

    public function index() {
        //服务器信息
        $info = array(
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            'MySQL版本' => MysqlHelper::getVersion(),
            '产品名称' => CMS_APPNAME,
            '内核版本' => CMS_VERSION,
            '内核流水号' => CMS_BUILD,
            '产品版本' => C('APPLIATION_VERSION'),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        );

        $this->assign('server_info', $info);
        $this->display();
    }
}
