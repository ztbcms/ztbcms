<?php
/**
 * User: zhlhuang
 * Date: 2020-09-15
 */

namespace app\common\service\upload;

use app\common\libs\upload\AliyunDriver;
use app\common\libs\upload\LocalDriver;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use think\exception\ValidateException;
use function EasyWeChat\Kernel\Support\get_client_ip;

/**
 * 上传服务
 *
 * @package app\common\service\upload
 */
class UploadService extends BaseService
{
    public $isPrivate = false;

    private $uploadDrivers = [
        'Local' => LocalDriver::class,
        'Aliyun' => AliyunDriver::class,
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

    public function __construct($driver = '')
    {
        $config = ConfigModel::getConfigs();
        $attachmentDriver = $driver ?: $config['attachment_driver'] ?? 'Local';
        $driver_class = $this->uploadDrivers[$attachmentDriver] ?? LocalDriver::class;
        $this->driver = new $driver_class($config);
    }

    /**
     * 上传UEditor图片
     *
     * @param int $groupId
     * @param int $userid
     * @param int $is_admin
     * @return AttachmentModel|bool
     */
    function uploadUEImage(int $groupId = 0, int $userid = 0, int $is_admin = 1)
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->userid = $userid;
            $attachmentModel->is_admin = $is_admin;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_UE_IMAGE;
            if (!$this->upload($attachmentModel)) {
                return false;
            }
            //getData('fileurl') 获取原始数据，防止获取器造成的影响
            $attachmentModel->filethumb = $attachmentModel->getData('fileurl');
            $attachmentModel->save();

            return $attachmentModel;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }

    /**
     * 上传文件
     *
     * @param int    $groupId
     * @param int    $user_id
     * @param string $user_type
     *
     * @return AttachmentModel|false
     */
    function uploadFile(int $groupId = 0, int $user_id = 0, string $user_type = '')
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = $user_type;
            $attachmentModel->user_id = $user_id;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_FILE;
            if (!$this->upload($attachmentModel)) {
                return false;
            }
            if (!$attachmentModel->filethumb) {
                $attachmentModel->filethumb = self::FILE_THUMB_ARRAY[$attachmentModel->fileext] ?? self::FILE_THUMB_ARRAY['doc'];
            }
            $attachmentModel->save();

            return $attachmentModel;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }

    /**
     * 上传视频
     *
     * @param int $groupId
     * @param int $userid
     * @param int $isadmin
     * @return AttachmentModel|bool
     */
    function uploadVideo($groupId = 0, $user_id = 0, $user_type = '')
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = $user_type;
            $attachmentModel->user_id = $user_id;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_VIDEO;
            if (!$this->upload($attachmentModel)) {
                return false;
            }
            if (!$attachmentModel->filethumb) {
                $attachmentModel->filethumb = self::FILE_THUMB_ARRAY['video'];
            }
            $attachmentModel->save();

            return $attachmentModel;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }

    /**
     * 上传图片
     *
     * @param int    $groupId
     * @param int    $user_id
     * @param string $user_type
     *
     * @return AttachmentModel|false
     */
    function uploadImage(int $groupId = 0, int $user_id = 0, string $user_type = '')
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = $user_type;
            $attachmentModel->user_id = $user_id;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_IMAGE;
            if (!$this->upload($attachmentModel)) {
                return false;
            }
            // 获取原始数据，防止获取器造成的影响
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
     *
     * @param $attachmentModel
     * @return bool
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    private function upload($attachmentModel): bool
    {
        $file = request()->file('file');
        $attachmentModel->filename = $file->getOriginalName();
        $attachmentModel->filesize = $file->getSize();
        $attachmentModel->fileext = $file->getOriginalExtension();
        $attachmentModel->uploadtime = time();
        $attachmentModel->upload_ip = get_client_ip();
        $attachmentModel->is_private = $this->isPrivate;
        $attachmentModel->hash = $file->hash('md5');

        $config = ConfigModel::getConfigs();
        $attachmentDriver = $config['attachment_driver'] ?? 'Local';
        $attachmentModel->driver = $attachmentDriver;

        //最大上传大小
        $maxFileSize = ($attachmentModel->is_admin == 1 ? $config['uploadmaxsize'] : $config['qtuploadmaxsize']) * 1024;
        //允许上传文件格式
        $uploadAllowExt = str_replace('|', ',',
            ($attachmentModel->is_admin == 1 ? $config['uploadallowext'] : $config['qtuploadallowext']));
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
            $this->driver->upload($attachmentModel);
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }

        return true;
    }

    /**
     * 获取文件上传的缩略图
     * @param $attachmentModel
     * @return string
     */
    function getFileThumbUrl($attachmentModel): string
    {
        if ($attachmentModel->module == AttachmentModel::MODULE_IMAGE) {
            return $attachmentModel->fileurl;
        }
        if ($attachmentModel->module == AttachmentModel::MODULE_VIDEO) {
            return $this->driver->getVideoThumbUrl($attachmentModel) ?: self::FILE_THUMB_ARRAY['video'];
        } else {
            return self::FILE_THUMB_ARRAY[$attachmentModel->fileext] ?? self::FILE_THUMB_ARRAY['doc'];
        }
    }

    /**
     * 获取私有访问链接
     *
     * @param $filepath
     * @return mixed
     */
    public function getPrivateUrl($filepath)
    {
        return $this->driver->getPrivateUrl($filepath);
    }

    /**
     * 获取私有缩略图URL
     *
     * @param $filepath
     * @return mixed
     */
    public function getPrivateThumbUrl($filepath)
    {
        return $this->driver->getPrivateThumbUrl($filepath);
    }

    /**
     * @param bool $isPrivate
     */
    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
        $this->driver->setIsPrivate($isPrivate);
    }
}