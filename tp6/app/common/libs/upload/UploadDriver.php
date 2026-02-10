<?php

/**
 * 存储驱动抽象类
 * 
 * 支持本地存储、阿里云 OSS、腾讯云 COS、七牛云等多种存储方式
 */

namespace app\common\libs\upload;

use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;

abstract class UploadDriver
{
    /**
     * 是否私有读
     */
    protected bool $isPrivate = false;

    /**
     * 配置信息
     */
    protected array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // ========== 核心方法（必须实现）==========

    /**
     * 上传文件
     *
     * @param AttachmentModel $attachmentModel 附件模型，需填充 filepath、fileurl 等字段
     * @return bool 上传是否成功
     */
    abstract public function upload(AttachmentModel $attachmentModel): bool;

    /**
     * 删除文件
     *
     * @param string $filePath 文件路径（相对路径）
     * @return bool 删除是否成功
     */
    abstract public function delete(string $filePath): bool;

    /**
     * 获取公开访问 URL
     *
     * @param string $filePath 文件路径
     * @return string 公开访问 URL
     */
    abstract public function getPublicUrl(string $filePath): string;

    /**
     * 获取私有访问 URL（带签名）
     *
     * @param string $filePath      文件路径
     * @param int    $expireSeconds 签名有效期（秒）
     * @return string 带签名的临时访问 URL
     */
    abstract public function getPrivateUrl(string $filePath, int $expireSeconds = 3600): string;

    // ========== 扩展方法（默认实现，子类可覆写）==========

    /**
     * 检查文件是否存在
     *
     * @param string $filePath 文件路径
     * @return bool 文件是否存在
     */
    public function exists(string $filePath): bool
    {
        return !empty($filePath);
    }

    /**
     * 获取前端直传凭证
     *
     * @param string $module 模块类型 (image/video/file)
     * @return array 直传凭证信息，为空表示不支持直传
     */
    public function getUploadCredential(string $module = 'image', string $filename = '', string $fileext = ''): array
    {
        return [];
    }

    /**
     * 渲染上传目录模板
     * 支持占位符：{module} {Y} {m} {d}
     *
     * @throws \Exception
     */
    protected function renderFileDir(string $module): string
    {
        $template = trim($this->config['attachment_direct_file_dir_template'] ?? '{module}/{Y}/{m}/{d}/');
        if ($template === '') {
            throw new \Exception('file_dir 模板非法');
        }

        if (preg_match_all('/\{([^}]+)\}/', $template, $matches)) {
            $allow = ['module', 'Y', 'm', 'd'];
            foreach ($matches[1] as $token) {
                if (!in_array($token, $allow, true)) {
                    throw new \Exception('file_dir 模板非法');
                }
            }
        }

        $dateMap = [
            '{module}' => $module,
            '{Y}' => date('Y'),
            '{m}' => date('m'),
            '{d}' => date('d'),
        ];
        $fileDir = strtr($template, $dateMap);
        if (strpos($fileDir, '{') !== false || strpos($fileDir, '}') !== false) {
            throw new \Exception('file_dir 模板非法');
        }

        $fileDir = preg_replace('#/+#', '/', $fileDir);
        $fileDir = trim($fileDir, '/');
        if ($fileDir === '' || strpos($fileDir, '..') !== false) {
            throw new \Exception('file_dir 模板非法');
        }

        return $fileDir . '/';
    }

    /**
     * 构建对象 key
     */
    protected function buildObjectKey(string $module, string $filename = '', string $fileext = ''): string
    {
        $normalizedExt = UploadService::normalizeFileExt($fileext, $filename);
        if ($normalizedExt === '') {
            throw new \Exception('无法识别文件后缀');
        }

        $fileDir = $this->renderFileDir($module);
        $suffix = date('His') . '_' . substr(md5(uniqid('', true)), 0, 10);

        return $fileDir . $suffix . '.' . $normalizedExt;
    }

    /**
     * 是否支持客户端直传
     *
     * @return bool
     */
    public function supportsDirectUpload(): bool
    {
        return false;
    }

    // ========== Getter/Setter ==========

    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }

    public function getIsPrivate(): bool
    {
        return $this->isPrivate;
    }
}
