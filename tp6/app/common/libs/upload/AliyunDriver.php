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
use think\facade\Cache;

class AliyunDriver extends UploadDriver
{

    const PRIVILEGE_PUBLIC = "1";
    const PRIVILEGE_PRIVICE = "2";

    protected $siteurl = "";
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;
    protected $domain;
    protected $privilege;
    protected $expireTime;

    public function __construct($config)
    {
        $this->accessKeyId = $config['attachment_aliyun_key_id'];
        $this->accessKeySecret = $config['attachment_aliyun_key_secret'];

        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $this->endpoint = $config['attachment_aliyun_endpoint'];
        // 设置存储空间名称。
        $this->bucket = $config['attachment_aliyun_bucket'];
        $this->domain = $config['attachment_aliyun_domain'];
        $this->privilege = $config['attachment_aliyun_privilege'];
        $this->expireTime = $config['attachment_aliyun_expire_time'];
    }

    /**
     * @param  AttachmentModel  $attachmentModel
     * @return bool
     * @throws \Exception
     */
    function upload(AttachmentModel $attachmentModel)
    {
        try {
            $file = request()->file('file');
            $filePath = $file->getPath().'/'.$file->getFilename();
            $object = $attachmentModel->module.'/'.date('Ymd').'/'.md5(time().rand(1000,
                        9999)).'.'.$attachmentModel->fileext;
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $res = $ossClient->uploadFile($this->bucket, $object, $filePath);
            if (!empty($res['oss-request-url'])) {
                $attachmentModel->filepath = $object;
                $attachmentModel->fileurl = $res['oss-request-url'];
                if ($attachmentModel->module == AttachmentModel::MODULE_VIDEO) {
                    //如果是视频文件、获取视频缩略图
                    $attachmentModel->filethumb = $attachmentModel->getData('fileurl')."?x-oss-process=video/snapshot,t_500,f_png";
                }
                if (!$this->getIsPrivate()) {
                    //设置公共读
                    $ossClient->putObjectAcl($this->bucket, $object, OssClient::OSS_ACL_TYPE_PUBLIC_READ);
                }
                return true;
            } else {
                throw new \Exception("OSS 上传失败");
            }
        } catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取私有访问链接
     * @param $object
     * @return bool|string
     */
    public function getPrivateUrl($object)
    {
        //如果读写权限是公开，不做私有处理
        if ($this->privilege == self::PRIVILEGE_PUBLIC) {
            return false;
        }
        $privateUrl = Cache::get('private_url_'.$object);
        if ($privateUrl) {
            // 查看
            return $privateUrl;
        }
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $privateUrl = $ossClient->signUrl($this->bucket, $object, $this->expireTime);
            //设置文件缓存，过期时间提前10分钟
            Cache::set('private_url_'.$object, $privateUrl, $this->expireTime - 600);
            return $privateUrl;
        } catch (\Exception $exception) {
            return "error";
        }
    }

    /**
     * 获取私有访问缩略图
     * @param $object
     * @return bool|string
     */
    public function getPrivateThumbUrl($object)
    {
        //如果读写权限是公开，不做私有处理
        if ($this->privilege == self::PRIVILEGE_PUBLIC) {
            return false;
        }
        $privateThumbUrl = Cache::get('private_thumb_url_'.$object);
        if ($privateThumbUrl) {
            // 查看
            return $privateThumbUrl;
        }
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $options = [
                OssClient::OSS_PROCESS => "video/snapshot,t_500,f_png"
            ];
            $privateThumbUrl = $ossClient->signUrl($this->bucket, $object, $this->expireTime, OssClient::OSS_HTTP_GET,
                $options);
            //设置文件缓存，过期时间提前10分钟
            Cache::set('private_thumb_url_'.$object, $privateThumbUrl, $this->expireTime - 600);
            return $privateThumbUrl;
        } catch (\Exception $exception) {
            return false;
        }
    }
}