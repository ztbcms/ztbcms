<?php

namespace app\common\model\cron;


use think\Model;

class CronConfigModel extends Model
{
    protected $name = 'tp6_cron_config';

    //是否启用计划任务
    const KEY_ENABLE_CRON = 'enable_cron';
    //密钥
    const KEY_ENABLE_SECRET_KEY = 'secret_key';
    //是否启用
    const ENABLE_YES = 1;
    const ENABLE_NO = 0;

    /**
     * 获取定时任务的状态
     * @return array
     */
    public function getCronStatus(){
        $cronConfig = $this->column('value', 'key');
        $cronStatus = $this->_getCronExecuteStatus();
        $cronEntryUrl = api_url('/common/cron.index/index/cron_secret_key/' . $cronConfig[CronConfigModel::KEY_ENABLE_SECRET_KEY]);
        return [
            'cron_config' => $cronConfig,
            'cron_status' => $cronStatus,
            'cron_entry_url' => $cronEntryUrl,
        ];
    }

    /**
     * 获取任务进行的状态
     * @return array
     */
    public function _getCronExecuteStatus(){
        $cron_execute_status = [
            'current_exec_amount' => 0, //正在执行任务数量
            'current_exec_cron' => [],//正在执行任务列表
        ];

        $CronLogModel = new CronLogModel();
        $CronModel = new CronModel();

        //正在执行任务数量
        $cronlog_list = $CronLogModel->where('result', CronLogModel::RESULT_PROCESSING)->select();
        $cron_execute_status['current_exec_amount'] = count($cronlog_list);

        //，正在执行任务列表
        $exec_cron_list = [];
        $exec_cron_map = [];
        foreach ($cronlog_list as $i => $log) {
            $cron = $CronModel->where('crod_id', $log['cron_id'])->findOrEmpty();
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