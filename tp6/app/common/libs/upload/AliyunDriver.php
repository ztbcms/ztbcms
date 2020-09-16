<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 18:23.
 */

namespace app\common\libs\upload;


use app\common\model\upload\AttachmentModel;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Filesystem;

class AliyunDriver
{

    const DISKCONFIG = "ztbcms";

    protected $siteurl = "";
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;

    public function __construct($config)
    {
        $this->accessKeyId = $config['attachment_aliyun_key_id'];
        $this->accessKeySecret = $config['attachment_aliyun_key_secret'];

        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $this->endpoint = $config['attachment_aliyun_endpoint'];
        // 设置存储空间名称。
        $this->bucket = $config['attachment_aliyun_bucket'];
    }

    function upload(AttachmentModel $attachmentModel)
    {
        try {
            $file = request()->file('file');
            $filePath = $file->getPath() . '/' . $file->getFilename();
            $object = $attachmentModel->module . '/' . date('Ymd') . '/' . md5(time() . rand(1000, 9999)) . '.' . $attachmentModel->fileext;
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $res = $ossClient->uploadFile($this->bucket, $object, $filePath);
            if (!empty($res['oss-request-url'])) {
                $attachmentModel->filepath = $res['oss-request-url'];
                $attachmentModel->fileurl = $res['oss-request-url'];
                if ($attachmentModel->module == AttachmentModel::MODULE_VIDEO) {
                    //如果是视频文件、获取视频缩略图
                    $attachmentModel->filethumb = $attachmentModel->filepath . "?x-oss-process=video/snapshot,t_500,f_png";
                }
                return true;
            } else {
                $this->error = "上传oss失败";
                return false;
            }
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }


        $file = request()->file('file');
        $url = Filesystem::disk(self::DISKCONFIG)->getConfig()->get('url');
        //兼容原CMS
        $saveName = Filesystem::disk(self::DISKCONFIG)->putFile($attachmentModel->module, $file);
        $attachmentModel->filepath = $saveName;
        $attachmentModel->fileurl = ($this->siteurl != '/' ? $this->siteurl : '') . $url . $saveName;
    }
}