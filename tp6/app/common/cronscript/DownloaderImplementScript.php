<?php
/**
 * Author: cycle_3
 */

namespace app\common\cronscript;

use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;

/**
 * 下载中心-执行待开始的下载任务
 * 建议采用队列方式启动，大文件下载比较耗时，以计划任务形式启动会影响其他任务
 */
class DownloaderImplementScript extends CronScript
{

    public function run($cronId): array
    {
        $downloader_id = $this->getNextTask();

        $total = 0;
        $success = 0;
        $error = 0;

        while ($downloader_id) {
            try {
                $billingRes = DownloaderService::implementDownloaderTask($downloader_id);
            } catch (\Exception $e) {
                $billingRes['status'] = false;
                $billingRes['code'] = 400;
                $billingRes['msg'] = $e->getMessage();
            }

            $total++;
            if ($billingRes['status'])  {
                $success++;
            } else {
                $error ++;
            }
            $downloader_id = $this->getNextTask($downloader_id);
        }

        return [
            'total' => $total,
            'success' => $success,
            'error' => $error
        ];
    }


    function getNextTask($downloader_id = 0)
    {
        return DownloaderModel::where([
            ['downloader_state', '=', DownloaderModel::STATE_WAIT],
            ['downloader_id', '>', $downloader_id],
        ])->value('downloader_id');
    }

}