<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 18:23.
 */

namespace app\common\libs\upload;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Result\Result;
use app\common\model\upload\AttachmentModel;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;
use OSS\OssClient;
use think\facade\Cache;

class AliyunDriver extends UploadDriver
{

    const PRIVILEGE_PUBLIC = "1";
    const PRIVILEGE_PRIVATE = "2";


    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;
    protected $domain;
    protected $privilege;
    protected $expireTime;
    protected $regionId;
    protected $roleArn;

    public function __construct($config)
    {
        $this->accessKeyId = $config['attachment_aliyun_key_id'] ?? "";
        $this->accessKeySecret = $config['attachment_aliyun_key_secret'] ?? "";

        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $this->endpoint = $config['attachment_aliyun_endpoint'] ?? "";
        // 设置存储空间名称。
        $this->bucket = $config['attachment_aliyun_bucket'] ?? "";
        $this->privilege = $config['attachment_aliyun_privilege'] ?? "";
        $this->expireTime = $config['attachment_aliyun_expire_time'] ?? "";
        $this->roleArn = $config['attachment_aliyun_sts_role_arn'] ?? "";
        $this->regionId = str_replace(['.aliyuncs.com'], '', $this->endpoint);
    }

    /**
     * @param AttachmentModel $attachmentModel
     * @return bool
     * @throws \Exception
     */
    function upload(AttachmentModel $attachmentModel): bool
    {
        try {
            $file = request()->file('file');
            $filePath = $file->getPath() . '/' . $file->getFilename();
            $object = $attachmentModel->module . '/' . date('Ymd') . '/' . md5(time() . rand(1000,
                        9999)) . '.' . $attachmentModel->fileext;
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $res = $ossClient->uploadFile($this->bucket, $object, $filePath);
            if (!empty($res['oss-request-url'])) {
                $attachmentModel->filepath = $object;
                $attachmentModel->fileurl = $res['oss-request-url'];
                if ($attachmentModel->module == AttachmentModel::MODULE_VIDEO) {
                    //如果是视频文件、获取视频缩略图
                    $attachmentModel->filethumb = $attachmentModel->getData('fileurl') . "?x-oss-process=video/snapshot,t_500,f_png";
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
     *
     * @param $object
     * @return bool|string
     */
    public function getPrivateUrl($object)
    {
        //如果读写权限是公开，不做私有处理
        if ($this->privilege == self::PRIVILEGE_PUBLIC) {
            return false;
        }
        $privateUrl = Cache::get('private_url_' . $object);
        if ($privateUrl) {
            // 查看
            return $privateUrl;
        }
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $privateUrl = $ossClient->signUrl($this->bucket, $object, $this->expireTime);
            //设置文件缓存，过期时间提前10分钟
            Cache::set('private_url_' . $object, $privateUrl, $this->expireTime - 600);

            return $privateUrl;
        } catch (\Exception $exception) {
            return "error";
        }
    }

    /**
     * 获取私有访问缩略图
     *
     * @param $object
     * @return bool|string
     */
    public function getPrivateThumbUrl($object)
    {
        //如果读写权限是公开，不做私有处理
        if ($this->privilege == self::PRIVILEGE_PUBLIC) {
            return false;
        }
        $privateThumbUrl = Cache::get('private_thumb_url_' . $object);
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
            Cache::set('private_thumb_url_' . $object, $privateThumbUrl, $this->expireTime - 600);

            return $privateThumbUrl;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 设置上传文件的跨域问题
     *
     * @param $origin
     * @throws OssException
     */
    private function setPutBucketCors($origin)
    {
        $cache_key = 'set_put_bucket_cors' . md5($origin);
        if (!Cache::get($cache_key)) {
            echo "cache";
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $corsConfig = new CorsConfig();
            $corsRule1 = new CorsRule();
            $corsRule1->addAllowedOrigin($origin);
            $corsRule1->addAllowedMethod("PUT");
            $corsRule1->addAllowedHeader("*");
            $corsRule1->setMaxAgeSeconds(600);

            $corsRule2 = new CorsRule();
            $corsRule2->addAllowedOrigin($origin);
            $corsRule2->addAllowedMethod("GET");
            $corsRule2->addAllowedHeader("*");
            $corsRule2->setMaxAgeSeconds(600);

            $corsConfig->addRule($corsRule1);
            $corsConfig->addRule($corsRule2);
            $ossClient->putBucketCors($this->bucket, $corsConfig);
            Cache::set($cache_key, true);
        }
    }

    function getStsToken($module = AttachmentModel::MODULE_IMAGE): array
    {
        try {
            $this->setPutBucketCors(request()->domain());
            AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret)
                ->regionId($this->regionId)
                ->asDefaultClient();
            $result = AlibabaCloud::rpc()
                ->product('Sts')
                ->scheme('https')
                ->version('2015-04-01')
                ->action('AssumeRole')
                ->method('POST')
                ->host('sts.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => $this->regionId,
                        'RoleArn' => $this->roleArn,
                        'RoleSessionName' => "full-oss",
                    ],
                ])
                ->request();
            $credentials = $result->toArray()['Credentials'] ?? [];
            if (!empty($credentials)) {
                return [
                    'file_dir' => $module . '/' . date('Ymd') . '/',
                    'region' => $this->regionId,
                    'accessKeyId' => $credentials['AccessKeyId'] ?? '',
                    'accessKeySecret' => $credentials['AccessKeySecret'] ?? '',
                    'stsToken' => $credentials['SecurityToken'] ?? '',
                    'bucket' => $this->bucket
                ];
            }

            return [];
        } catch (\Throwable $exception) {
            return [];
        }
    }
}