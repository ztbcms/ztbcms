<?php

/**
 * 本地存储驱动
 */

namespace app\common\libs\upload;

use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\facade\Filesystem;
use think\facade\Request;

class LocalDriver extends UploadDriver
{
    const DISK_CONFIG = "ztbcms";

    protected string $fileDomain = "";

    public function __construct(array $config)
    {
        parent::__construct($config);
        $domain = $config['attachment_local_domain'] ?? '';
        $this->fileDomain = $domain ?: Request::domain();
    }

    /**
     * 上传文件
     */
    public function upload(AttachmentModel $attachmentModel): bool
    {
        $file = request()->file('file');
        if (!$file) {
            return false;
        }

        $url = Filesystem::getDiskConfig(self::DISK_CONFIG, 'url', '');
        $saveName = Filesystem::disk(self::DISK_CONFIG)
            ->putFile($attachmentModel->module, $file);

        $attachmentModel->filepath = $saveName;
        $attachmentModel->fileurl = $this->fileDomain . $url . $saveName;

        return true;
    }

    /**
     * 删除文件
     */
    public function delete(string $filePath): bool
    {
        $fullPath = public_path() . 'upload/' . $filePath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    /**
     * 获取公开访问 URL
     */
    public function getPublicUrl(string $filePath): string
    {
        $url = Filesystem::getDiskConfig(self::DISK_CONFIG, 'url', '');
        return $this->fileDomain . $url . $filePath;
    }

    /**
     * 获取私有访问 URL（本地存储无私有读，返回公开 URL）
     */
    public function getPrivateUrl(string $filePath, int $expireSeconds = 3600): string
    {
        return $this->getPublicUrl($filePath);
    }

    /**
     * 检查文件是否存在
     */
    public function exists(string $filePath): bool
    {
        $fullPath = public_path() . 'upload/' . $filePath;
        return file_exists($fullPath);
    }

    /**
     * 本地存储支持直传（上传到服务器 API）
     */
    public function supportsDirectUpload(): bool
    {
        return true;
    }

    /**
     * 获取本地上传凭证
     * 本地上传的"直传"是指前端直接调用服务器上传 API
     */
    public function getUploadCredential(string $module = 'file', string $filename = '', string $fileext = ''): array
    {
        $fileDir = $this->renderFileDir($module);
        $normalizedExt = UploadService::normalizeFileExt($fileext, $filename);

        return [
            'driver' => 'Local',
            'module' => $module,
            'fileext' => $normalizedExt,
            'file_dir' => $fileDir,
            'upload_url' => '/common/upload.api/uploadLocalDirect',
            'domain' => $this->fileDomain,
        ];
    }
}
