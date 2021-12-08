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
        'Local'  => LocalDriver::class,
        'Aliyun' => AliyunDriver::class,
    ];

    const FILE_THUMB_ARRAY = [
        'ppt'   => '/statics/admin/upload/ppt.png',
        'pptx'  => '/statics/admin/upload/ppt.png',
        'doc'   => '/statics/admin/upload/doc.png',
        'docx'  => '/statics/admin/upload/doc.png',
        'xls'   => '/statics/admin/upload/xls.png',
        'xlsx'  => '/statics/admin/upload/xls.png',
        'file'  => '/statics/admin/upload/file.png',
        'video' => '/statics/admin/upload/video.png'
    ];

    /**
     * 上传UEditor图片
     * @param  int  $groupId
     * @param  int  $userid
     * @param  int  $isadmin
     * @return AttachmentModel|bool
     */
    function uploadUEImage($groupId = 0, $userid = 0, $isadmin = 1)
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->userid = $userid;
            $attachmentModel->is_admin = $isadmin;
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
     * @param  int  $groupId
     * @param  int  $user_id
     * @param  string  $user_type
     *
     * @return AttachmentModel|false
     */
    function uploadFile($groupId = 0, $user_id = 0, $user_type = '')
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
                $attachmentModel->filethumb = isset(self::FILE_THUMB_ARRAY[$attachmentModel->fileext]) ? self::FILE_THUMB_ARRAY[$attachmentModel->fileext] : self::FILE_THUMB_ARRAY['doc'];
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
     * @param  int  $groupId
     * @param  int  $userid
     * @param  int  $isadmin
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
     * @param  int  $groupId
     * @param  int  $user_id
     * @param  string  $user_type
     *
     * @return AttachmentModel|false
     */
    function uploadImage($groupId = 0, $user_id = 0, $user_type = '')
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
     * @param $attachmentModel
     * @return bool
     */
    private function upload($attachmentModel)
    {
        $file = request()->file('file');
        $attachmentModel->filename = $file->getOriginalName();
        $attachmentModel->filesize = $file->getSize();
        $attachmentModel->fileext = $file->getOriginalExtension();
        $attachmentModel->uploadtime = time();
        $attachmentModel->uploadip = get_client_ip();
        $attachmentModel->is_private = $this->isPrivate;
        $attachmentModel->hash = $file->hash('md5');

        $config = ConfigModel::getConfigs();
        $attachmentDriver = isset($config['attachment_driver']) ? $config['attachment_driver'] : 'Local';
        $attachmentModel->driver = $attachmentDriver;

        //最大上传大小
        $maxFileSize = ($attachmentModel->is_admin == 1 ? $config['uploadmaxsize'] : $config['qtuploadmaxsize']) * 1024;
        //允许上传文件格式
        $uploadAllowExt = str_replace('|', ',',
            ($attachmentModel->is_admin == 1 ? $config['uploadallowext'] : $config['qtuploadallowext']));
        try {
            validate(['file' => "filesize:{$maxFileSize}|fileExt:{$uploadAllowExt}"])
                ->message([
                    'file.filesize' => '文件大小不能超过'.round($maxFileSize / 1024 / 1024, 1).'MB',
                    'file.fileExt'  => '文件格式不对',
                ])
                ->check(request()->file());
        } catch (\think\exception\ValidateException $e) {
            $this->setError($e->getMessage());
            return false;
        }
        if (isset($this->uploadDrivers[$attachmentDriver])) {
            try {
                $driver = new $this->uploadDrivers[$attachmentDriver]($config);
                $driver->setIsPrivate($this->isPrivate);
                $driver->upload($attachmentModel);
            } catch (\Exception $exception) {
                $this->setError($exception->getMessage());
                return false;
            }
        } else {
            $this->setError('找不到相应上传驱动，请联系管理员！');
            return false;
        }

        return true;
    }

    /**
     * 获取私有访问链接
     * @param $filepath
     * @param $attachmentDriver
     * @return bool
     */
    public function getPrivateUrl($filepath, $attachmentDriver)
    {
        $config = ConfigModel::getConfigs();
        if (isset($this->uploadDrivers[$attachmentDriver])) {
            $driver = new $this->uploadDrivers[$attachmentDriver]($config);
            return $driver->getPrivateUrl($filepath);
        } else {
            $this->setError('找不到相应上传驱动，请联系管理员！');
            return false;
        }
    }

    /**
     * 获取私有缩略图URL
     * @param $filepath
     * @param $attachmentDriver
     * @return bool
     */
    public function getPrivateThumbUrl($filepath, $attachmentDriver)
    {
        $config = ConfigModel::getConfigs();
        if (isset($this->uploadDrivers[$attachmentDriver])) {
            $driver = new $this->uploadDrivers[$attachmentDriver]($config);
            return $driver->getPrivateThumbUrl($filepath);
        } else {
            $this->setError('找不到相应上传驱动，请联系管理员！');
            return false;
        }
    }
}