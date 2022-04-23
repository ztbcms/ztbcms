<?php
/**
 * Created by FHYI.
 * Date 2020/10/28
 * Time 10:02
 */

namespace app\admin\controller;

use app\common\libs\helper\MysqlHelper;
use app\admin\model\AdminUserModel;
use app\admin\model\LoginlogModel;
use app\admin\model\OperationlogModel;
use app\common\controller\AdminController;
use think\facade\Config;
use think\facade\View;

/**
 * 后台框架首页
 * Class Main
 * @package app\admin\controller
 */
class Main extends AdminController
{
    /**
     * 概览页
     * @return string
     */
    public function index()
    {
        $_action = input('_action');
        if($_action == 'getMainInfo') {
            //服务器信息
            $returnData = [
                'system_info'           => $this->_getSystemInfo(),
                'admin_statistics_info' => $this->_getAdminStatisticsInfo(),
                'alert_message'         => $this->_getAlertMessage(),
            ];
            return self::makeJsonReturn(true, $returnData, '');
        }
        return View::fetch('index');
    }

    /**
     * 系统信息
     * @return array
     */
    private function _getSystemInfo()
    {
        return [
            [
                'name'  => '操作系统',
                'value' => PHP_OS,
            ],
            [
                'name'  => '运行环境',
                'value' => $_SERVER["SERVER_SOFTWARE"],
            ],
            [
                'name'  => 'PHP运行方式',
                'value' => php_sapi_name(),
            ],
            [
                'name'  => 'MySQL版本',
                'value' => MysqlHelper::getVersion(),
            ],
            [
                'name'  => '数据库名字',
                'value' => Config::get('database.connections.mysql.database'),
            ],
            [
                'name'  => '数据库地址',
                'value' => Config::get('database.connections.mysql.hostname'),
            ],
            [
                'name'  => '产品名称',
                'value' => Config::get('admin.cms_name'),
            ],
            [
                'name'  => '内核版本',
                'value' => Config::get('admin.cms_version'),
            ],
            [
                'name'  => '内核流水号',
                'value' => Config::get('admin.cms_build'),
            ],
            [
                'name'  => '产品版本',
                'value' => Config::get('admin.application_version'),
            ],
            [
                'name'  => '上传文件上限',
                'value' => ini_get('upload_max_filesize'),
            ],
            [
                'name'  => 'POST容量上限',
                'value' => ini_get('post_max_size'),
            ],
            [
                'name'  => '脚本内存上限',
                'value' => ini_get('memory_limit'),
            ],
            [
                'name'  => '执行时间限制',
                'value' => ini_get('max_execution_time') . "秒",
            ],
            [
                'name'  => '剩余空间',
                'value' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            ],
        ];
    }

    /**
     * 后台统计信息
     * @return array
     */
    private function _getAdminStatisticsInfo()
    {
        // 7日内
        $limitTime = time() - 7 * 24 * 60 * 60;

        // 后台用户数
        $adminUserAmount = AdminUserModel::count();

        // 登录成功率
        $loginSuccessAmount = LoginlogModel::where([
            ['logintime', '>=', $limitTime],
            ['status', '=', 1]
        ])->count();

        // 登录数量
        $loginTotal = LoginlogModel::where('logintime', '>=', $limitTime)->count();

        if ($loginTotal == 0) {
            $loginSuccessPercent = 100 . '%';
        } else {
            $loginSuccessPercent = round($loginSuccessAmount / $loginTotal * 100, 1) . '%';
        }

        // 操作成功率
        $operationSuccessAmount = OperationlogModel::where([
            ['time', '>=', $limitTime],
            ['status', '=', 1]
        ])->count();

        // 操作数
        $operationTotal = OperationlogModel::where('time', '>=', $limitTime)->count();
        if ($operationTotal == 0) {
            $operateSuccessPercent = 100 . '%';
        } else {
            $operateSuccessPercent = round($operationSuccessAmount / $operationTotal * 100, 1) . '%';
        }

        return [
            'admin_user_amount'       => $adminUserAmount ?? 0,
            'login_success_percent'   => $loginSuccessPercent ?? 0,
            'operate_success_percent' => $operateSuccessPercent ?? 0,
        ];
    }

    /**
     * 获取警报信息
     * @return array
     */
    protected function _getAlertMessage()
    {
        $msg = [];
        if (file_exists(base_path() . '/install')) {
            $msg [] = [
                'type' => 'warning', //success,info,warning,error
                'msg' => '您还没有删除 Install 模块，出于安全的考虑，我们建议您删除 Install 模块(/app/install)'
            ];
        }
        return $msg;
    }
}
