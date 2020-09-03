<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-03
 * Time: 14:07.
 */

namespace app\common\controller\cron;

use app\common\controller\AdminController;
use app\common\model\cron\CronConfigModel;
use app\common\model\cron\CronLogModel;
use app\common\model\cron\CronModel;
use think\facade\View;
use think\Request;

class Dashboard extends AdminController
{
    function index()
    {
        return View::fetch('index');
    }

    function setCronEnable(Request $request)
    {
        $enable = $request->param('enable');
        $res = CronConfigModel::where('key', CronConfigModel::KEY_ENABLE_CRON)->update([
            'value' => $enable
        ]);
        return self::createReturn(true, $res, '更新成功');
    }

    function setCronSecretKey(Request $request)
    {
        $secretKey = $request->param('secret_key');
        $res = CronConfigModel::where('key', CronConfigModel::KEY_ENABLE_SECRET_KEY)->update([
            'value' => $secretKey
        ]);
        return self::createReturn(true, $res, '更新成功');
    }

    function getCronStatus(Request $request)
    {
        $cronConfig = CronConfigModel::column('value', 'key');

        $cronStatus = $this->_getCronExecuteStatus();
        $cronEntryUrl = $request->domain() . '/home/common/cron.index/index/cron_secret_key/' . $cronConfig[CronConfigModel::KEY_ENABLE_SECRET_KEY];
        return self::createReturn(true, [
            'cron_config' => $cronConfig,
            'cron_status' => $cronStatus,
            'cron_entry_url' => $cronEntryUrl,
        ]);
    }

    private function _getCronExecuteStatus()
    {
        $cron_execute_status = [
            'current_exec_amount' => 0, //正在执行任务数量
            'current_exec_cron' => [],//正在执行任务列表
        ];

        //正在执行任务数量
        $cronlog_list = CronLogModel::where('result', CronLogModel::RESULT_PROCESSING)->select();
        $cron_execute_status['current_exec_amount'] = count($cronlog_list);

        //，正在执行任务列表
        $exec_cron_list = [];
        $exec_cron_map = [];
        foreach ($cronlog_list as $i => $log) {
            $cron = CronModel::where('crod_id', $log['cron_id'])->findOrEmpty();
            if (!$cron->isEmpty() && empty($exec_cron_map[$cron->cron_id])) {
                $exec_cron_list [] = [
                    'cron_id' => $cron->cron_id,
                    'subject' => $cron->subject,
                    'cron_file' => $cron->cron_file,
                ];
            }
        }
        $cron_execute_status['current_exec_cron'] = $exec_cron_list;

        return $cron_execute_status;
    }
}