<?php

/**
 * 上传服务
 */

namespace app\common\service\upload;

use app\common\libs\upload\AliyunDriver;
use app\common\libs\upload\LocalDriver;
use app\common\libs\upload\QiniuDriver;
use app\common\libs\upload\UploadDriver;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use think\exception\ValidateException;
use function EasyWeChat\Kernel\Support\get_client_ip;

class UploadService extends BaseService
{
    public bool $isPrivate = false;

    protected UploadDriver $driver;
    protected string $driverName = 'Local';  // 当前使用的驱动名称

    private array $uploadDrivers = [
        'Local' => LocalDriver::class,
        'Aliyun' => AliyunDriver::class,
        'Qiniu' => QiniuDriver::class,
    ];

    const FILE_THUMB_ARRAY = [
        'pdf' => '/statics/admin/upload/pdf.png',
        'ppt' => '/statics/admin/upload/ppt.png',
        'pptx' => '/statics/admin/upload/ppt.png',
        'doc' => '/statics/admin/upload/doc.png',
        'docx' => '/statics/admin/upload/doc.png',
        'xls' => '/statics/admin/upload/xls.png',
        'xlsx' => '/statics/admin/upload/xls.png',
        'file' => '/statics/admin/upload/file.png',
        'video' => '/statics/admin/upload/video.png'
    ];

    public function __construct(string $driver = '')
    {
        $config = ConfigModel::getConfigs();
        $this->driverName = $driver ?: $config['attachment_driver'] ?? 'Local';
        $driverClass = $this->uploadDrivers[$this->driverName] ?? LocalDriver::class;
        $this->driver = new $driverClass($config);
    }

    // 图片后缀
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];
    // 视频后缀
    const VIDEO_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', 'm4v', '3gp'];

    /**
     * 统一上传文件（自动判断类型）
     * @return AttachmentModel|false
     */
    public function uploadAny(int $groupId = 0, int $userId = 0, string $userType = '')
    {
        try {
            $file = request()->file('file');
            throw_if(!$file, new \Exception('未选择文件或文件过大'));

            $fileext = self::normalizeFileExt($file->getOriginalExtension());
            $module = self::inferModuleByExt($fileext);

            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = $userType;
            $attachmentModel->user_id = $userId;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = $module;

            if (!$this->upload($attachmentModel)) {
                return false;
            }

            // 自动设置缩略图
            if ($module === AttachmentModel::MODULE_IMAGE) {
                $attachmentModel->filethumb = $attachmentModel->getData('fileurl');
            } elseif ($module === AttachmentModel::MODULE_VIDEO) {
                $attachmentModel->filethumb = self::FILE_THUMB_ARRAY['video'];
            } else {
                $attachmentModel->filethumb = self::FILE_THUMB_ARRAY[$fileext] ?? self::FILE_THUMB_ARRAY['file'];
            }

            $attachmentModel->save();

            return $attachmentModel;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());
            return false;
        }
    }

    /**
     * 仅上传文件（不保存附件记录）
     * @return array|false
     */
    public function uploadDirectOnly(int $userId = 0, string $userType = '', string $module = '')
    {
        try {
            $file = request()->file('file');
            throw_if(!$file, new \Exception('未选择文件或文件过大'));

            $fileext = self::normalizeFileExt($file->getOriginalExtension());
            if (empty($fileext)) {
                throw new \Exception('无法识别文件后缀');
            }

            $module = trim($module);
            if (empty($module)) {
                $module = self::inferModuleByExt($fileext);
            }

            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = $userType;
            $attachmentModel->user_id = $userId;
            $attachmentModel->group_id = 0;
            $attachmentModel->module = $module;

            if (!$this->upload($attachmentModel)) {
                return false;
            }

            return [
                'driver' => $attachmentModel->driver,
                'module' => $attachmentModel->module,
                'filename' => $attachmentModel->filename,
                'filesize' => intval($attachmentModel->filesize),
                'fileext' => $attachmentModel->fileext,
                'filepath' => $attachmentModel->filepath,
                'fileurl' => $attachmentModel->fileurl,
                'is_private' => intval($attachmentModel->is_private),
            ];
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());
            return false;
        }
    }

    /**
     * 获取前台上传配置
     * @return array
     */
    public static function getUploadConfig(): array
    {
        $config = ConfigModel::getConfigs();
        $maxSize = intval($config['qtuploadmaxsize'] ?? 10240); // KB
        $allowExtStr = $config['qtuploadallowext'] ?? 'jpg|png|gif';
        $allowExt = array_map('strtolower', array_filter(explode('|', $allowExtStr)));

        return [
            'max_size' => $maxSize,
            'max_size_bytes' => $maxSize * 1024,
            'allow_ext' => $allowExt,
            'allow_ext_str' => join('|', $allowExt),
        ];
    }

    /**
     * 规范化文件后缀（统一为小写且不带点）
     */
    public static function normalizeFileExt(string $fileext = '', string $filename = ''): string
    {
        $ext = strtolower(ltrim(trim($fileext), '.'));
        if (!empty($ext)) {
            return $ext;
        }

        if (!empty($filename) && strpos($filename, '.') !== false) {
            return strtolower(trim(pathinfo($filename, PATHINFO_EXTENSION)));
        }

        return '';
    }

    /**
     * 根据后缀推断模块
     */
    public static function inferModuleByExt(string $fileext): string
    {
        $fileext = strtolower(trim($fileext));
        if (in_array($fileext, self::IMAGE_EXTENSIONS, true)) {
            return AttachmentModel::MODULE_IMAGE;
        }
        if (in_array($fileext, self::VIDEO_EXTENSIONS, true)) {
            return AttachmentModel::MODULE_VIDEO;
        }
        return AttachmentModel::MODULE_FILE;
    }

    /**
     * 上传UEditor图片
     * @return AttachmentModel|false
     */
    public function uploadUEImage(int $groupId = 0, int $userId = 0, string $userType = '')
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_id = $userId;
            $attachmentModel->user_type = $userType;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_UE_IMAGE;

            if (!$this->upload($attachmentModel)) {
                return false;
            }

            $attachmentModel->filethumb = $attachmentModel->getData('fileurl');
            $attachmentModel->save();

            return $attachmentModel;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());
            return false;
        }
    }

    /**
     * 统一文件上传操作
     */
    private function upload(AttachmentModel $attachmentModel): bool
    {
        $file = request()->file('file');
        throw_if(!$file, new \Exception('未选择文件或文件过大'));

        $attachmentModel->filename = $file->getOriginalName();
        $attachmentModel->filesize = $file->getSize();
        $attachmentModel->fileext = self::normalizeFileExt($file->getOriginalExtension());
        $attachmentModel->create_time = time();
        $attachmentModel->update_time = time();
        $attachmentModel->upload_ip = get_client_ip();
        $attachmentModel->is_private = $this->isPrivate;
        $attachmentModel->hash = $file->hash('md5');

        $config = ConfigModel::getConfigs();
        $attachmentModel->driver = $this->driverName;  // 使用实例中的驱动名称

        $maxFileSize = ($attachmentModel->user_type == 'admin' ? $config['uploadmaxsize'] : $config['qtuploadmaxsize']) * 1024;
        $uploadAllowExt = str_replace(
            '|',
            ',',
            ($attachmentModel->user_type == 'admin' ? $config['uploadallowext'] : $config['qtuploadallowext'])
        );

        try {
            validate(['file' => "filesize:{$maxFileSize}|fileExt:{$uploadAllowExt}"])
                ->message([
                    'file.filesize' => '文件大小不能超过' . round($maxFileSize / 1024 / 1024, 1) . 'MB',
                    'file.fileExt' => '文件格式不对',
                ])
                ->check(request()->file());
        } catch (ValidateException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        try {
            $this->driver->setIsPrivate($this->isPrivate);
            return $this->driver->upload($attachmentModel);
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());
            return false;
        }
    }

    /**
     * 获取文件 URL
     */
    public function getFileUrl(string $filePath, bool $isPrivate = false): string
    {
        if ($isPrivate) {
            return $this->driver->getPrivateUrl($filePath);
        }
        return $this->driver->getPublicUrl($filePath);
    }

    /**
     * 获取私有访问链接
     */
    public function getPrivateUrl(string $filePath): string
    {
        return $this->driver->getPrivateUrl($filePath);
    }

    /**
     * 删除文件
     */
    public function deleteFile(string $filePath): bool
    {
        return $this->driver->delete($filePath);
    }

    /**
     * 检查文件是否存在
     */
    public function fileExists(string $filePath): bool
    {
        return $this->driver->exists($filePath);
    }

    /**
     * 获取直传凭证
     */
    public function getUploadCredential(string $module = 'image', string $filename = '', string $fileext = ''): array
    {
        return $this->driver->getUploadCredential($module, $filename, $fileext);
    }

    /**
     * 是否支持直传
     */
    public function supportsDirectUpload(): bool
    {
        return $this->driver->supportsDirectUpload();
    }

    /**
     * 设置私有读
     */
    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
        $this->driver->setIsPrivate($isPrivate);
    }
}
