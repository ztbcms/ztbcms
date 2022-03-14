<?php
/**
 * Author: cycle_3
 */

namespace app\common\service\downloader;

use app\common\libs\downloader\ImgTool;
use app\common\libs\downloader\VideoTool;
use app\common\model\downloader\DownloaderModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use think\facade\Queue;

/**
 * 下载服务
 *
 * @package app\common\service\email
 */
class DownloaderService extends BaseService
{

    /**
     * 校验下载的任务
     * @param string $url
     * @return array
     */
    static function checkCreateDownloaderTask(string $url = ''): array
    {
        if (empty($url)) {
            return self::createReturn(false, '', '抱歉，下载的链接不能为空');
        }
        return self::createReturn(true);
    }

    /**
     * 创建下载任务
     * @param string $url
     * @return array
     */
    static function createDownloaderTask(string $url = ''): array
    {
        $checkRes = self::checkCreateDownloaderTask($url);
        if (!$checkRes['status']) {
            return $checkRes;
        }

        $DownloaderModel = new DownloaderModel();
        $downloader_id = $DownloaderModel
            ->where('downloader_url','=',$url)
            ->where('downloader_state','<>',DownloaderModel::FAIL_DOWNLOADER)
            ->value('downloader_id');
        if(empty($downloader_id)) {
            //已经下载过的文件不进行二次下载
            $downloader_id = $DownloaderModel->insertGetId([
                'downloader_url' => $url,
                'downloader_state' => DownloaderModel::WAIT_DOWNLOADER,
                'create_time' => time(),
                'update_time' => time()
            ]);

            Queue::push('app\common\job\downloader\ImplementDownloaderTaskJop', [
                'downloader_id' => $downloader_id
            ], 'downloader');
        }

        return self::createReturn(true, [
            'downloader_id' => $downloader_id
        ], '创建成功');
    }

    /**
     * 执行下载任务
     * @param int $downloader_id
     * @return array
     */
    static function implementDownloaderTask(int $downloader_id = 0): array
    {
        $start_duration = microtime(true);
        $DownloaderModel = new DownloaderModel();
        $downloader = $DownloaderModel
            ->where('downloader_id', '=', $downloader_id)
            ->where('downloader_state', '<>', DownloaderModel::SUCCESS_DOWNLOADER)
            ->findOrEmpty();
        if ($downloader->isEmpty()) {
            return self::createReturn(false, '', '抱歉，不存在需要执行的任务');
        }

        $DownloaderModel
            ->where('downloader_id', '=', $downloader_id)
            ->update([
                'downloader_state' => DownloaderModel::START_DOWNLOADER
            ]);

        $downloader_url = $downloader['downloader_url'];
        $downloaderRes['status'] = false;
        $downloaderRes['msg'] = '抱歉,上传的类型暂不支持';

        try {

            //todo 上传逻辑可抽象出来，暂时未处理
            $is_img = ImgTool::isImg($downloader_url);
            if ($is_img['status']) {
                //判断是否为图片
                $file_type = $is_img['data']['file_type'];
                $downloaderRes = ImgTool::getOnLineImg($downloader_url, time() . '.' . $file_type);
            }

            $is_video = VideoTool::isVideo($downloader_url);
            if ($is_video['status']) {
                //判断是否为视频
                $file_type = $is_video['data']['file_type'];
                $downloaderRes = VideoTool::getOnLineVideo($downloader_url, time().'.'.$file_type);
            }

            $downloaderData = $downloaderRes['data'];
            if ($downloaderRes['status']) {
                $updateData['downloader_state'] = DownloaderModel::SUCCESS_DOWNLOADER;
                $updateData['file_name'] = $downloaderData['file_name'] ?? '';
                $updateData['file_path'] = $downloaderData['file_path'] ?? '';
                $updateData['file_url'] = $downloaderData['file_url'] ?? '';
                $updateData['file_thumb'] = $downloaderData['file_thumb'] ?? '';
            } else {
                $updateData['downloader_state'] = DownloaderModel::FAIL_DOWNLOADER;
            }
            $updateData['downloader_result'] = $downloaderRes['msg'];
        } catch (\Exception|\Error $exception) {
            $updateData['downloader_state'] = DownloaderModel::FAIL_DOWNLOADER;
            $updateData['downloader_result'] = $exception->getMessage();
        }

        $updateData['downloader_duration'] = (microtime(true) - $start_duration);
        $updateData['update_time'] = time();
        $DownloaderModel
            ->where('downloader_id', '=', $downloader_id)
            ->update($updateData);

        if ($updateData['downloader_state'] == DownloaderModel::SUCCESS_DOWNLOADER) {
            //文件下载成功的情况 ，将文件同步进入附件表, 默认为本地上传
            $attachmentModel = new AttachmentModel();
            $attachmentModel->driver = 'Local';
            $attachmentModel->module = $downloaderData['module'] ?? '';
            $attachmentModel->filename = $downloaderData['file_name'] ?? '';
            $attachmentModel->filepath = $downloaderData['file_path'] ?? '';
            $attachmentModel->fileurl = $downloaderData['file_url'] ?? '';
            $attachmentModel->filethumb = $downloaderData['file_thumb'] ?? '';
            $attachmentModel->filesize = $downloaderData['filesize'] ?? 0;
            $attachmentModel->fileext = $downloaderData['fileext'] ?? '';
            $attachmentModel->upload_ip = $_SERVER['REMOTE_ADDR'];
            $attachmentModel->create_time = time();
            $attachmentModel->update_time = time();
            $attachmentModel->user_type = 'admin';
            $attachmentModel->user_id = 0;
            $attachmentModel->hash = $downloaderData['hash'] ?? '';
            $attachmentModel->save();
        }

        return self::createReturn(true, [], '执行成功');
    }

    /**
     * 重启失败任务
     * @param int $downloader_id
     * @return array
     */
    static function retryDownloaderTask(int $downloader_id = 0): array
    {
        $DownloaderModel = new DownloaderModel();
        $downloader = $DownloaderModel
            ->where('downloader_id', '=', $downloader_id)
            ->where('downloader_state', '=', DownloaderModel::FAIL_DOWNLOADER)
            ->findOrEmpty();
        if ($downloader->isEmpty()) {
            return self::createReturn(false, '', '抱歉，该任务不需要重启');
        }

        $downloader->downloader_state = DownloaderModel::WAIT_DOWNLOADER;
        $downloader->downloader_implement_num += 1;
        $downloader->downloader_next_implement_time = time();
        $downloader->downloader_result = '';
        $downloader->save();

        Queue::push('app\common\job\downloader\ImplementDownloaderTaskJop', [
            'downloader_id' => $downloader_id
        ], 'downloader');

        return self::createReturn(true, [], '重启下载任务');
    }
}