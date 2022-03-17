<?php
/**
 * Author: cycle_3
 */

namespace app\common\cronscript;

use app\common\model\ConfigModel;
use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;

/**
 * 下载中心-将执行失败的下载任务重试下载
 * 已失败的任务 => 重新进入下载队列，待下载
 */
class DownloaderRetryScript extends CronScript
{

    public function run($cronId)
    {
        $config = ConfigModel::getConfigs();
        $downloader_retry_switch = $config['downloader_retry_switch'] ?? 0;
        $downloader_retry_num = $config['downloader_retry_num'] ?? 0;
        $total = 0;
        $success = 0;
        $error = 0;
        if (!$downloader_retry_switch) {
            return [
                'total' => $total,
                'success' => $success,
                'error' => $error
            ];
        }
        $downloader_id = $this->getNextTask(0, $downloader_retry_num);
        while ($downloader_id) {
            try {
                $retryRes = DownloaderService::retryDownloaderTask($downloader_id);
            } catch (\Exception $e) {
                $retryRes['status'] = false;
                $retryRes['code'] = 500;
                $retryRes['msg'] = $e->getMessage();
            }
            $total++;
            if ($retryRes['status']) {
                $success++;
            } else {
                $error++;
            }
            $downloader_id = $this->getNextTask($downloader_id, $downloader_retry_num);
        }

        return [
            'total' => $total,
            'success' => $success,
            'error' => $error
        ];
    }

    function getNextTask($downloader_id = 0, $downloader_retry_num = 0)
    {
        return DownloaderModel::where([
            ['downloader_state', '=', DownloaderModel::STATE_FAIL],
            ['downloader_id', '>', $downloader_id],
            ['downloader_implement_num', '<', $downloader_retry_num],
            ['downloader_next_implement_time', '<', time()]
        ])->value('downloader_id');
    }
}