<?php

/**
 * 阿里云 OSS 存储驱动
 */

namespace app\common\libs\upload;

use AlibabaCloud\Client\AlibabaCloud;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Cache;

class AliyunDriver extends UploadDriver
{
    protected string $accessKeyId;
    protected string $accessKeySecret;
    protected string $endpoint;
    protected string $bucket;
    protected int $expireTime;
    protected string $regionId;
    protected string $roleArn;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->accessKeyId = $config['attachment_aliyun_key_id'] ?? "";
        $this->accessKeySecret = $config['attachment_aliyun_key_secret'] ?? "";
        $this->endpoint = $config['attachment_aliyun_endpoint'] ?? "";
        $this->bucket = $config['attachment_aliyun_bucket'] ?? "";
        $this->isPrivate = intval($config['attachment_aliyun_privilege'] ?? "") == 2;
        $this->expireTime = intval($config['attachment_aliyun_expire_time'] ?? 3600);
        $this->roleArn = $config['attachment_aliyun_sts_role_arn'] ?? "";
        $this->regionId = str_replace(['.aliyuncs.com'], '', $this->endpoint);
    }

    /**
     * 上传文件
     */
    public function upload(AttachmentModel $attachmentModel): bool
    {
        try {
            $file = request()->file('file');
            $filePath = $file->getPath() . '/' . $file->getFilename();
            $object = $attachmentModel->module . '/' . date('Ymd') . '/' . md5(time() . rand(1000, 9999)) . '.' . $attachmentModel->fileext;

            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $res = $ossClient->uploadFile($this->bucket, $object, $filePath);

            if (!empty($res['oss-request-url'])) {
                $attachmentModel->filepath = $object;
                $attachmentModel->fileurl = $res['oss-request-url'];

                if (!$this->getIsPrivate()) {
                    $ossClient->putObjectAcl($this->bucket, $object, OssClient::OSS_ACL_TYPE_PUBLIC_READ);
                }

                return true;
            }

            return false;
        } catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $filePath): bool
    {
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $ossClient->deleteObject($this->bucket, $filePath);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 获取公开访问 URL
     */
    public function getPublicUrl(string $filePath): string
    {
        $endpoint = str_replace('https://', '', $this->endpoint);
        return "https://{$this->bucket}.{$endpoint}/{$filePath}";
    }

    /**
     * 获取私有访问 URL
     */
    public function getPrivateUrl(string $filePath, int $expireSeconds = 3600): string
    {
        $cacheKey = 'aliyun_private_url_' . md5($filePath);
        $privateUrl = Cache::get($cacheKey);

        if ($privateUrl) {
            return $privateUrl;
        }

        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $privateUrl = $ossClient->signUrl($this->bucket, $filePath, $expireSeconds ?: $this->expireTime);
            Cache::set($cacheKey, $privateUrl, $expireSeconds - 600);
            return $privateUrl;
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * 检查文件是否存在
     */
    public function exists(string $filePath): bool
    {
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            return $ossClient->doesObjectExist($this->bucket, $filePath);
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 是否支持直传
     */
    public function supportsDirectUpload(): bool
    {
        return !empty($this->roleArn);
    }

    /**
     * 获取直传凭证（STS Token）
     */
    public function getUploadCredential(string $module = 'file', string $filename = '', string $fileext = ''): array
    {
        try {
            $objectKey = $this->buildObjectKey($module, $filename, $fileext);
            $fileDir = dirname($objectKey) . '/';

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
                        'RoleSessionName' => "upload-session",
                    ],
                ])
                ->request();

            $credentials = $result->toArray()['Credentials'] ?? [];

            if (!empty($credentials)) {
                return [
                    'driver' => 'Aliyun',
                    'module' => $module,
                    'fileext' => UploadService::normalizeFileExt($fileext, $filename),
                    'file_dir' => $fileDir,
                    'object_key' => $objectKey,
                    'region' => $this->regionId,
                    'endpoint' => $this->endpoint,
                    'bucket' => $this->bucket,
                    'accessKeyId' => $credentials['AccessKeyId'] ?? '',
                    'accessKeySecret' => $credentials['AccessKeySecret'] ?? '',
                    'stsToken' => $credentials['SecurityToken'] ?? '',
                    'expiration' => $credentials['Expiration'] ?? '',
                ];
            }

            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
