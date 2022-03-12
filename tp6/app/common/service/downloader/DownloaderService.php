<?php
/**
 * Author: cycle_3
 */

namespace app\common\service\downloader;

use app\common\libs\downloader\ImgTool;
use app\common\libs\downloader\VideoTool;
use app\common\model\downloader\DownloaderModel;
use app\common\service\BaseService;

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

        $downloader_id = $DownloaderModel->insertGetId([
            'downloader_url' => $url,
            'downloader_state' => DownloaderModel::WAIT_DOWNLOADER,
            'create_time' => time(),
            'update_time' => time()
        ]);

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
        $DownloaderModel = new DownloaderModel();
        $downloader = $DownloaderModel
            ->where('downloader_id','=',$downloader_id)
            ->where('downloader_state','<>',DownloaderModel::SUCCESS_DOWNLOADER)
            ->findOrEmpty();
        if($downloader->isEmpty()) {
            return self::createReturn(false,'','抱歉，不存在需要执行的任务');
        }

        $DownloaderModel
            ->where('downloader_id','=',$downloader_id)
            ->update([
                'downloader_state' => DownloaderModel::START_DOWNLOADER
            ]);

        $downloader_url = $downloader['downloader_url'];
        $downloaderRes['status'] = false;
        $downloaderRes['msg'] = '抱歉,上传的类型暂不支持';

        $is_img = ImgTool::isImg($downloader_url);
        if($is_img['status']) {
            //判断是否为图片
            $downloaderRes = ImgTool::getOnLineImg($downloader_url,time().'.'.$is_img['data']['file_type']);
        }


        $is_video = VideoTool::isVideo($downloader_url);
        if($is_video['status']) {
            //判断是否为图片
            $downloaderRes = VideoTool::getOnLineVideo($downloader_url,time().'.'.$is_video['data']['file_type']);
        }


        if($downloaderRes['status']) {
            $updateData['downloader_state'] = DownloaderModel::SUCCESS_DOWNLOADER;
            $updateData['file_name'] = $downloaderRes['data']['file_name'];
            $updateData['file_path'] = $downloaderRes['data']['file_path'];
            $updateData['file_url'] = $downloaderRes['data']['file_url'];
        } else {
            $updateData['downloader_state'] = DownloaderModel::FAIL_DOWNLOADER;
        }
        $updateData['update_time'] = time();
        $updateData['downloader_result'] = $downloaderRes['msg'];

        $DownloaderModel
            ->where('downloader_id','=',$downloader_id)
            ->update($updateData);
        return self::createReturn(true,[],'执行成功');
    }


}