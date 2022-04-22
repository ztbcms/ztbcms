<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

use app\admin\service\AdminConfigService;
use app\common\model\upload\AttachmentModel;
use Error;
use Exception;

/**
 * 文件下载工具
 */
class DownloaderTool
{

    static function getFileExtensionByUrl($url)
    {
        $path_info = pathinfo($url);
        if (isset($path_info['extension'])) {
            return $path_info['extension'];
        }
        return '';
    }

    /**
     * 根据文件名称获取扩展名
     * @param $file_name
     * @return mixed|string|void
     */
    static function getFileExtensionByFileName($file_name)
    {
        $path_info = pathinfo($file_name);
        if (isset($path_info['extension'])) {
            return $path_info['extension'];
        }
        return '';
    }

    /**
     * 在线文件下载
     * @param string $url
     * @param string $file_name
     * @return array
     */
    static function downloaderFile(string $url, string $file_name = ''): array
    {
        // 文件类型获取 文件名称 > URL 中获取
        $file_extension = self::getFileExtensionByFileName($file_name);
        if (empty($file_extension)) {
            $file_extension = self::getFileExtensionByUrl($url);
        }
        if (empty($file_name)) {
            $file_name = md5($url) . '.' . $file_extension;
        }
        if (in_array($file_extension, ['jpg', 'gif', 'png', 'jpeg', 'bmp'])) {
            //图片
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_IMAGE);
        }
        if (in_array($file_extension, ['mp4'])) {
            //视频
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_VIDEO);
        }
        if (in_array($file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'])) {
            //文件
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_FILE);
        }
        return createReturn(false, null, '文件类型暂不支持');
    }


    /**
     * 文件下载实现
     * @param string $url
     * @param string $filename
     * @param string $module
     * @return array
     */
    static function doDownloadFile(string $url = '', string $filename = '', $file_extension = '', string $module = '')
    {
        try {
            // 保存路径
            $save_path_base = '/downloader/' . substr(md5($url), 0, 2) .'/';
            $save_path = app()->getRootPath() . 'public' . $save_path_base;
            $file_path = $save_path . $filename;
            $file_path_base = $save_path_base . $filename;
            if (!file_exists($save_path)) {
                mkdir($save_path, 0777, true); //创建目录
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
                //w+ 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。
                $file = fopen($save_path . $filename, 'w+'); //打开一个文件或 URL
                fwrite($file, $resource); //将内容$resource写入打开的文件$file中
                fclose($file);
            }
            // 访问地址
            $host = AdminConfigService::getInstance()->getConfig('downloader_domain')['data'];
            $file_url = rtrim($host, '/') . $file_path_base;
            $file_thumb = '';
            if (in_array($file_extension, ['jpg', 'gif', 'png', 'jpeg'])) {
                //图片
                $file_thumb = rtrim($host, '/') . $file_path_base;
            }
            if (in_array($file_extension, ['mp4'])) {
                //视频
                $file_thumb = rtrim($host, '/') . '/statics/admin/upload/video.png';
            }
            if (in_array($file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'])) {
                //文件
                $file_thumb = rtrim($host, '/') . '/statics/admin/upload/file.png';
            }
            return createReturn(true, [
                'module' => $module,
                'file_name' => $filename,
                'file_path' => $file_path_base,
                'file_url' => $file_url,
                'file_thumb' => $file_thumb,
                'file_size' => filesize($file_path),
                'file_ext' => $file_extension,
                'file_hash' => hash_file('md5', $file_path)
            ]);
        } catch (Exception | Error $e) {
            return createReturn(false, [], $e->getMessage());
        }
    }
}