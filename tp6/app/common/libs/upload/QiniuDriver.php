<?php

/**
 * 七牛云存储驱动
 */

namespace app\common\libs\upload;

use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use think\facade\Cache;

class QiniuDriver extends UploadDriver
{
    protected string $accessKey;
    protected string $secretKey;
    protected string $bucket;
    protected string $domain;
    protected int $expireTime;
    protected ?Auth $auth = null;

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->accessKey = $config['attachment_qiniu_access_key'] ?? "";
        $this->secretKey = $config['attachment_qiniu_secret_key'] ?? "";
        $this->bucket = $config['attachment_qiniu_bucket'] ?? "";
        $this->domain = rtrim($config['attachment_qiniu_domain'] ?? "", '/');
        $this->expireTime = intval($config['attachment_qiniu_expire_time'] ?? 3600);
        $this->isPrivate = intval($config['attachment_qiniu_privilege'] ?? "") == 2;
    }

    /**
     * 获取七牛认证对象
     */
    protected function getAuth(): Auth
    {
        if ($this->auth === null) {
            $this->auth = new Auth($this->accessKey, $this->secretKey);
        }
        return $this->auth;
    }

    /**
     * 上传文件
     */
    public function upload(AttachmentModel $attachmentModel): bool
    {
        try {
            $file = request()->file('file');
            $filePath = $file->getPath() . '/' . $file->getFilename();
            $key = $attachmentModel->module . '/' . date('Ymd') . '/' . md5(time() . rand(1000, 9999)) . '.' . $attachmentModel->fileext;

            $token = $this->getAuth()->uploadToken($this->bucket);
            $uploadMgr = new UploadManager();

            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

            if ($err !== null) {
                throw new \Exception($err->message());
            }

            $attachmentModel->filepath = $key;
            $attachmentModel->fileurl = $this->domain . '/' . $key;

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $filePath): bool
    {
        try {
            $bucketMgr = new BucketManager($this->getAuth());
            list($ret, $err) = $bucketMgr->delete($this->bucket, $filePath);
            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取公开访问 URL
     */
    public function getPublicUrl(string $filePath): string
    {
        return $this->domain . '/' . $filePath;
    }

    /**
     * 获取私有访问 URL
     */
    public function getPrivateUrl(string $filePath, int $expireSeconds = 3600): string
    {
        $cacheKey = 'qiniu_private_url_' . md5($filePath);
        $privateUrl = Cache::get($cacheKey);

        if ($privateUrl) {
            return $privateUrl;
        }

        try {
            $expireTime = $expireSeconds ?: $this->expireTime;
            $baseUrl = $this->domain . '/' . $filePath;
            $privateUrl = $this->getAuth()->privateDownloadUrl($baseUrl, $expireTime);
            Cache::set($cacheKey, $privateUrl, $expireTime - 600);
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
            $bucketMgr = new BucketManager($this->getAuth());
            list($fileInfo, $err) = $bucketMgr->stat($this->bucket, $filePath);
            return $err === null && !empty($fileInfo);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 是否支持直传
     */
    public function supportsDirectUpload(): bool
    {
        return true;
    }

    /**
     * 获取直传凭证（上传 Token）
     */
    public function getUploadCredential(string $module = 'file', string $filename = '', string $fileext = ''): array
    {
        try {
            $objectKey = $this->buildObjectKey($module, $filename, $fileext);
            $fileDir = dirname($objectKey) . '/';
            $policy = [
                'scope' => $this->bucket . ':' . $objectKey,
                'deadline' => time() + $this->expireTime,
            ];

            $token = $this->getAuth()->uploadToken($this->bucket, $objectKey, $this->expireTime, $policy);

            // 通过 SDK 查询 bucket 对应区域的上传域名
            $config = new \Qiniu\Config();
            $config->useHTTPS = true;
            $config->useCdnDomains = true;
            $uploadUrl = $config->getUpHost($this->accessKey, $this->bucket);

            return [
                'driver' => 'Qiniu',
                'module' => $module,
                'fileext' => UploadService::normalizeFileExt($fileext, $filename),
                'file_dir' => $fileDir,
                'object_key' => $objectKey,
                'upload_url' => $uploadUrl,
                'domain' => $this->domain,
                'bucket' => $this->bucket,
                'token' => $token,
                'expireTime' => time() + $this->expireTime,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
