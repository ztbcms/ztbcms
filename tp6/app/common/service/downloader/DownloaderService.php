<?php
/**
 * Author: cycle_3
 */

namespace app\common\service\downloader;

use app\common\libs\downloader\DownloaderTool;
use app\common\libs\downloader\FileTool;
use app\common\libs\downloader\ImgTool;
use app\common\libs\downloader\VideoTool;
use app\common\model\downloader\DownloaderModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use think\Exception;
use think\facade\Queue;

/**
 * 下载服务
 *
 * @package app\common\service\email
 */
class DownloaderService extends BaseService
{

    /**
     * 创建下载任务
     * @param string $url
     * @return array
     */
    static function createDownloaderTask(string $url = '')
    {
        $url = trim($url);
        if (empty($url) || strpos($url, 'http') !== 0) {
            return self::createReturn(false, '', '参数异常：url格式错误');
        }
        $url_hash = md5($url);
        $DownloaderModel = new DownloaderModel();
        $downloader_record = $DownloaderModel->where('downloader_url_hash', '=', $url_hash)->find();
        if (empty($downloader_record)) {
            // 未下载
            $downloader_id = $DownloaderModel->insertGetId([
                'downloader_url' => $url,
                'downloader_url_hash' => $url_hash,
                'downloader_state' => DownloaderModel::STATE_WAIT,
                'create_time' => time(),
                'update_time' => time()
            ]);
            Queue::push('app\common\job\downloader\ImplementDownloaderTaskJop', [
                'downloader_id' => $downloader_id
            ], 'downloader');
            return self::createReturn(true, [
                'downloader_id' => $downloader_id
            ], '下载任务创建成功');
        } else {
            // 已有下载记录
            $downloader_id = $downloader_record->downloader_id;
            $downloader_state = $downloader_record->downloader_state;
            if ($downloader_state == DownloaderModel::STATE_SUCCESS) {
                return self::createReturn(true, [
                    'downloader_id' => $downloader_id
                ], '已下载');
            }
            // 下载失败,失败重试
            if ($downloader_state == DownloaderModel::STATE_FAIL) {
                $downloader_record->save([
                    'downloader_state' => DownloaderModel::STATE_WAIT,
                    'process_start_time' => 0,
                    'process_end_time' => 0,
                ]);
                Queue::push('app\common\job\downloader\ImplementDownloaderTaskJop', [
                    'downloader_id' => $downloader_id
                ], 'downloader');
                return self::createReturn(true, [
                    'downloader_id' => $downloader_id
                ], '下载任务创建成功');
            }
            // 下载中、待下载
            if ($downloader_state == DownloaderModel::STATE_FAIL || $downloader_state == DownloaderModel::STATE_WAIT) {
                return self::createReturn(true, [
                    'downloader_id' => $downloader_id
                ], '下载中,请稍候');
            }
            return self::createReturn(false, [
                'downloader_id' => $downloader_id
            ], '状态异常，请开发人员检查');
        }
    }

    /**
     * 执行下载任务
     * @param int $downloader_id
     * @return array
     */
    static function implementDownloaderTask(int $downloader_id = 0)
    {
        $start_time = time();
        $DownloaderModel = new DownloaderModel();
        $downloader = $DownloaderModel->where([
            ['downloader_id', '=', $downloader_id],
            ['downloader_state', '<>', DownloaderModel::STATE_SUCCESS],
        ])->findOrEmpty();
        if ($downloader->isEmpty()) {
            return self::createReturn(false, null, '找不到下载任务');
        }
        if ($downloader->downloader_state !== DownloaderModel::STATE_WAIT) {
            return self::createReturn(false, $downloader, '下载状态异常，无法执行');
        }
        $res = $DownloaderModel->where([
            ['downloader_id', '=', $downloader_id],
            ['downloader_state', '=', DownloaderModel::STATE_WAIT],
        ])->update([
            'downloader_state' => DownloaderModel::STATE_PROCESSING,
            'process_start_time' => $start_time,
            'process_end_time' => 0,
        ]);
        // 避免重复操作
        if(!$res){
            return self::createReturn(false, $downloader, '下载状态异常，请稍候再试');
        }
        $downloader_url = $downloader->downloader_url;
        try {
            $downloaderRes = DownloaderTool::downloaderFile($downloader_url, $downloader->file_name);
            $downloaderData = $downloaderRes['data'];
            if ($downloaderRes['status']) {
                $updateData['downloader_state'] = DownloaderModel::STATE_SUCCESS;
                $updateData['file_name'] = $downloaderData['file_name'] ?? '';
                $updateData['file_name'] = $downloaderData['file_name'] ?? '';
                $updateData['file_path'] = $downloaderData['file_path'] ?? '';
                $updateData['file_url'] = $downloaderData['file_url'] ?? '';
                $updateData['file_thumb'] = $downloaderData['file_thumb'] ?? '';
                $updateData['file_hash'] = $downloaderData['file_hash'] ?? '';
            } else {
                $updateData['downloader_state'] = DownloaderModel::STATE_FAIL;
            }
            $updateData['downloader_result'] = $downloaderRes['msg'];
        } catch (\Exception | \Error $exception) {
            $updateData['downloader_state'] = DownloaderModel::STATE_FAIL;
            $updateData['downloader_result'] = $exception->getMessage();
        }

        $updateData['process_end_time'] = time();
        $updateData['downloader_duration'] = ($updateData['process_end_time'] - $start_time);
        if ($updateData['downloader_duration'] <= 0) {
            $updateData['downloader_duration'] = 1;
        }
        $DownloaderModel->where('downloader_id', '=', $downloader_id)
            ->update($updateData);

        if ($updateData['downloader_state'] == DownloaderModel::STATE_SUCCESS) {
            //文件下载成功的情况 ，将文件同步进入附件表, 默认为本地上传
            $attachmentModel = new AttachmentModel();
            $attachmentModel->driver = 'Local';
            $attachmentModel->module = $downloaderData['module'] ?? '';
            $attachmentModel->filename = $downloaderData['file_name'] ?? '';
            $attachmentModel->filepath = $downloaderData['file_path'] ?? '';
            $attachmentModel->fileurl = $downloaderData['file_url'] ?? '';
            $attachmentModel->filethumb = $downloaderData['file_thumb'] ?? '';
            $attachmentModel->filesize = $downloaderData['file_size'] ?? 0;
            $attachmentModel->fileext = $downloaderData['file_ext'] ?? '';
            $attachmentModel->upload_ip = $_SERVER['REMOTE_ADDR'];
            $attachmentModel->create_time = time();
            $attachmentModel->update_time = time();
            $attachmentModel->user_type = 'admin';
            $attachmentModel->user_id = 0;
            $attachmentModel->hash = $downloaderData['file_hash'] ?? '';
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
            ->where('downloader_state', '=', DownloaderModel::STATE_FAIL)
            ->findOrEmpty();
        if ($downloader->isEmpty()) {
            return self::createReturn(false, '', '抱歉，该任务不需要重启');
        }

        $downloader->downloader_state = DownloaderModel::STATE_WAIT;
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