<?php
/**
 * Author: cycle_3
 */

namespace app\common\job\downloader;

use app\common\libs\queue\BaseQueueJob;
use app\common\service\downloader\DownloaderService;
use think\facade\Log;
use think\queue\Job;

class ImplementDownloaderTaskJop extends BaseQueueJob
{

    function fire(Job $job, $data): bool
    {
        try {
            $downloader_id = $data['downloader_id'] ?? '';
            if ($downloader_id) {
                DownloaderService::implementDownloaderTask($downloader_id);
            }
            $job->delete();
            return true;
        } catch (\Exception | \Error $exception) {
            $job->delete();
            return true;
        }
    }

    function failed($data)
    {
        $downloader_id = $data['downloader_id'] ?? '';
        Log::debug('ImplementDownloaderTaskJop::下载失败(downloader_id : ' . $downloader_id . ')');
    }

}