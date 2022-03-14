<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;

/**
 * 视频下载工具
 */
class VideoTool
{

    /**
     * 判断是否为视频
     * @param string $file_name
     * @return array
     */
    static function isVideo(string $file_name = ''): array
    {
        $file_type = pathinfo($file_name)['extension'];
        if ($file_type == 'mp4') {
            return createReturn(true, [
                'file_type' => $file_type
            ]);
        } else {
            return createReturn(false, [], '抱歉，仅允许上传mp4格式的视频');
        }
    }

    /**
     * 将文件下载到本地
     * @param string $url
     * @param string $filename
     * @return array
     */
    static function getOnLineVideo(string $url = '', string $filename = ''): array
    {
        //本地保存地址
        $date = date("Ymd");

        $save_path = app()->getRootPath() . 'public/downloader/video/' . $date . '/';
        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true); //创建目录
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //对于https的不验证ssl证书
        $resource = curl_exec($ch);
        if ($resource === FALSE) {
            return createReturn(false, [], "CURL Error:" . curl_error($ch));
        }
        curl_close($ch);
        if (empty($filename)) {
            $filename = date("YmdHis") . rand(1, 99999) . '.jpg';
        }

        //w+ 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。
        $file = fopen($save_path . $filename, 'w+'); //打开一个文件或 URL
        fwrite($file, $resource); //将内容$resource写入打开的文件$file中
        fclose($file);

        $file_path = $save_path . $filename;

        $host = ConfigModel::getConfigs()['siteurl'];
        return createReturn(true, [
            'module' => AttachmentModel::MODULE_VIDEO,
            'file_name' => $filename,
            'file_path' => $file_path,
            'file_url' => $host . '/downloader/video/' . $date . '/' . $filename,
            'filesize' => filesize($file_path),
            //todo : 视频缩略图暂时使用固定的缩略图
            'file_thumb' => $host . '/statics/admin/upload/video.png',
            'fileext' => pathinfo($file_path)['extension'],
            'hash' => hash_file('md5',$file_path)
        ]);
    }

}