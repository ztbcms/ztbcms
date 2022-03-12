<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

use app\common\model\ConfigModel;

/**
 * 图片下载工具
 */
class ImgTool
{


    /**
     * 判断是否为图片
     * @param string $file_name
     * @return array
     */
    static function isImg(string $file_name = ''): array
    {
        $file = fopen($file_name, "rb");
        $bin = fread($file, 2); // 只读2字节

        fclose($file);
        $str_info = @unpack("C2chars", $bin);

        $type_code = intval($str_info['chars1'] . $str_info['chars2']);
        $file_type = '';
        if ($type_code == 255216 || $type_code == 7173 || $type_code == 13780) {
            if($type_code == 255216) {
                $file_type = 'jpg';
            }
            if($type_code == 7173) {
                $file_type = 'gif';
            }
            if($type_code == 13780) {
                $file_type = 'png';
            }
            return createReturn(true,[
                'file_type' => $file_type
            ]);
        } else {
            return createReturn(false,[],'抱歉，仅允许上传jpg/jpeg/gif/png格式的图片');
        }
    }

    /**
     * 将文件下载到本地
     * @param string $url
     * @param string $filename
     * @return array
     */
    static function getOnLineImg(string $url = '', string $filename = ''): array
    {
        //本地保存地址
        $date = date("Ymd");

        $save_path = app()->getRootPath().'public/downloader/img/'.$date.'/';
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
        if(empty($filename)) {
            $filename = date("YmdHis") . rand(1, 99999) . '.jpg';
        }

        //w+ 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。
        $file = fopen($save_path . $filename, 'w+'); //打开一个文件或 URL
        fwrite($file, $resource); //将内容$resource写入打开的文件$file中
        fclose($file);

        $host = ConfigModel::getConfigs()['siteurl'];

        return createReturn(true,[
            'file_name' => $filename,
            'file_path' => $save_path . $filename,
            'file_url'  => $host.'/downloader/img/'.$date.'/'.$filename
        ]);
    }


}