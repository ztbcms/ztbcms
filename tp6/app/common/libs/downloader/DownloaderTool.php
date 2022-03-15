<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use Error;
use Exception;

/**
 * 文件下载工具
 */
class DownloaderTool
{

    /**
     * 线上文件下载
     * @param string $url
     * @param string $file_name
     * @return array
     */
    public static function downloaderOnLine(string $url = '', string $file_name = ''): array
    {
        $path_info = pathinfo($url);
        if(isset($path_info['extension'])) {
            $file_type = $path_info['extension'];
            if($file_type == 'jpg' || $file_type == 'gif' || $file_type == 'png') {
                //图片
                return self::getOnLine($url,$file_name.'.'.$file_type,AttachmentModel::MODULE_IMAGE);
            }
            if($file_type == 'mp4') {
                //视频
                return self::getOnLine($url,$file_name.'.'.$file_type,AttachmentModel::MODULE_VIDEO);
            }
            if($file_type == 'pdf' || $file_type == 'docx' || $file_type == 'txt') {
                //文件
                return self::getOnLine($url,$file_name.'.'.$file_type,AttachmentModel::MODULE_FILE);
            }
        }
        return createReturn(false,[],'抱歉,上传的类型暂不支持');
    }


    /**
     * 文件下载
     * @param string $url
     * @param string $filename
     * @param string $module
     * @return array
     */
    static function getOnLine(string $url = '', string $filename = '',string $module = ''): array
    {

        try {
            $date = date("Ymd");
            $save_path = app()->getRootPath().'public/downloader/'.$module.'/'.$date.'/';
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
                return createReturn(false,[],"CURL Error:" . curl_error($ch));
            }
            curl_close($ch);

            $host = ConfigModel::getConfigs()['siteurl'];

            $file_type = '';
            $file_thumb = '';
            if($module == AttachmentModel::MODULE_IMAGE) {
                //图片
                $file_type = '.jpg';
                $file_thumb = $host.'/downloader/'.$module.'/'.$date.'/'.$filename;
            }
            if($module == AttachmentModel::MODULE_VIDEO) {
                //视频
                $file_type = '.mp4';
                $file_thumb = $host . '/statics/admin/upload/video.png';
            }
            if($module == AttachmentModel::MODULE_FILE) {
                //文件
                $file_type = '.docx';
                $file_thumb = $host.'/statics/admin/upload/pdf.png';
            }

            if(empty($filename)) {
                $filename = date("YmdHis") . rand(1, 99999).$file_type;
            }
            //w+ 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。
            $file = fopen($save_path . $filename, 'w+'); //打开一个文件或 URL
            fwrite($file, $resource); //将内容$resource写入打开的文件$file中
            fclose($file);
            $file_path = $save_path . $filename;

            return createReturn(true,[
                'module' => $module,
                'file_name' => $filename,
                'file_path' => $file_path,
                'file_url'  => $host.'/downloader/'.$module.'/'.$date.'/'.$filename,
                'filesize' => filesize($file_path),
                'file_thumb' => $file_thumb,
                'fileext' => pathinfo($file_path)['extension'],
                'hash' => hash_file('md5',$file_path)
            ]);
        } catch (Exception|Error $e) {
            return createReturn(false,[], $e->getMessage());
        }
    }
}