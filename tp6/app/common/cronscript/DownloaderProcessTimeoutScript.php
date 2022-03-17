<?php
/**
 * Author: cycle_3
 */

namespace app\common\cronscript;

use app\admin\service\AdminConfigService;
use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;

/**
 * 下载中心-处理下载任务任务超时
 * 下载中+执行超时 => 下载失败
 */
class DownloaderProcessTimeoutScript extends CronScript
{

    public function run($cronId)
    {
        $downloader_timeout = intval(AdminConfigService::getInstance()->getConfig('downloader_timeout')['data']) ?: 300;
        $total = 0;
        $success = 0;
        $error = 0;
        $limit_time = time() - $downloader_timeout;
        $downloader_id = $this->getNextTask(0, $limit_time);
        while ($downloader_id) {
            try {
                $retryRes = DownloaderService::faildDownloadTask($downloader_id, '执行超时');
            } catch (\Exception $e) {
                $retryRes['status'] = false;
                $retryRes['code'] = 400;
                $retryRes['msg'] = $e->getMessage();
            }
            $total++;
            if ($retryRes['status']) {
                $success++;
            } else {
                $error++;
            }
            $downloader_id = $this->getNextTask($downloader_id, $limit_time);
        }
        return [
            'total' => $total,
            'success' => $success,
            'error' => $error
        ];
    }

    function getNextTask($downloader_id, $limit_time)
    {
        return DownloaderModel::where([
            ['downloader_state', '=', DownloaderModel::STATE_PROCESSING],
            ['downloader_id', '>', $downloader_id],
            ['process_start_time', '<', $limit_time],
        ])->value('downloader_id');
    }
}