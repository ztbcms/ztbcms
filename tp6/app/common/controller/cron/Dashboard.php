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
use app\common\util\Dir;
use think\facade\View;
use think\Request;

class Dashboard extends AdminController
{
    function deleteCron(Request $request)
    {
        $cronId = $request->post('cron_id');
        $cron = CronModel::where('cron_id', $cronId)->findOrEmpty();
        if (!$cron->isEmpty() && $cron->delete()) {
            return self::createReturn(true, [''], 'ok');
        } else {
            return self::createReturn(false, [], '删除失败');
        }
    }

    function getCronDetail(Request $request)
    {
        $cronId = $request->get('cron_id');
        $cron = CronModel::where('cron_id', $cronId)->findOrEmpty();
        if (!$cron->isEmpty()) {
            //整理loopData
            $loopDaytime = $cron->loop_daytime;
            list($day, $hour, $minute) = explode('-', $loopDaytime);
            if ($cron->loop_type == 'now') {
                $loopData = [
                    'now_time' => (int)$day + (int)$hour + (int)$minute,
                    'now_type' => $day ? 'day' : ($hour ? 'hour' : 'minute'),
                ];
            } else {
                $loopData = [
                    $cron->loop_type . '_day' => (int)$day,
                    $cron->loop_type . '_hour' => (int)$hour,
                    $cron->loop_type . '_minute' => (int)$minute,
                ];
            }

            return self::createReturn(true, ['cron' => $cron, 'loop_data' => $loopData], 'ok');
        } else {
            return self::createReturn(false, [], '获取失败');
        }
    }

    function createCron(Request $request)
    {
        if ($request->isPost()) {
//            TODO 数据校验
//            $formData = $request->post('form');
            $cronId = $request->post('cron_id', '');
            $loopType = $request->post('form.loop_type');
            $loopData = $request->post('loop_data');
            //通过循环的类型和参数，获取下次执行时间和统一循环格式。
            list($nextTime, $loopDaytime) = CronModel::getLoopData($loopType, $loopData);

            $cronModel = CronModel::where('cron_id', $cronId)->findOrEmpty();
            $cronModel->type = $request->post('form.type');
            $cronModel->subject = $request->post('form.subject');
            $cronModel->loop_type = $request->post('form.loop_type');
            $cronModel->cron_file = $request->post('form.cron_file');
            $cronModel->isopen = $request->post('form.isopen');
            $cronModel->loop_daytime = $loopDaytime;
            $cronModel->next_time = $nextTime;
            $cronModel->created_time = time();
            $cronModel->modified_time = time();
            if ($cronModel->save()) {
                return self::createReturn(true, [], 'ok');
            } else {
                return self::createReturn(false, [], '创建有误');
            }
        }
        return View::fetch('createCron', [
            'cronFileList' => $this->_getCronFileList()
        ]);
    }

    private function _getCronFileList()
    {
        $dir = APP_PATH;
        $Dirs = new Dir($dir);
        $moduleList = $Dirs->toArray();
        $cronFileList = [];
        //遍历模块
        foreach ($moduleList as $k => $module) {
            $cronscript_filename = APP_PATH . $module['filename'] . DIRECTORY_SEPARATOR . 'CronScript';
            if (file_exists($cronscript_filename)) {
                $CronDirs = new Dir($cronscript_filename);
                $cronFiles = $CronDirs->toArray();
                //遍历 Module/CronScript 模块下的脚本
                foreach ($cronFiles as $index => $cronFile) {
                    $cron_classname = str_replace('.php', '', $cronFile['filename']);
                    if ($cron_classname != 'CronScript') {
                        $cronFileList[] = 'app\\' . $module['filename'] . '\\' . 'cronscript' . '\\' . $cron_classname;
                    }
                }
            }
        }
        return $cronFileList;
    }

    function getCronList()
    {
        $lists = CronModel::order('cron_id', 'DESC')->paginate(15);
        foreach ($lists as $key => $value) {
            $value->loop_time_text = CronModel::getLoopText($value->loop_type, $value->loop_daytime);
        }
        return self::createReturn(true, $lists, 'ok');
    }

    function cron()
    {
        return View::fetch('cron');
    }

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