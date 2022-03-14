<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

/**
 * 文件下载工具
 */
class DownloaderTool
{

    /**
     * 线上文件下载
     * @param $url
     * @return array
     */
    public static function downloaderOnLine($url = '',$file_name = ''){
        //todo 上传逻辑可抽象出来，暂时未处理
        $pathinfo = pathinfo($url);
        if(isset($pathinfo['extension'])) {
            $file_type = $pathinfo['extension'];

            if($file_type == 'jpg' || $file_type == 'gif' || $file_type == 'png') {
                //图片
                return ImgTool::getOnLineImg($url,$file_name.'.'.$file_type);
            }

            if($file_type == 'mp4') {
                //视频
                return VideoTool::getOnLineVideo($url,$file_name.'.'.$file_type);
            }

            if($file_type == 'pdf' || $file_type == 'docx' || $file_type == 'txt') {
                //文件
                return FileTool::getOnLineFile($url,$file_name.'.'.$file_type);
            }
        }
        return createReturn(false,[],'抱歉,上传的类型暂不支持');
    }

}