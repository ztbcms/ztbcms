<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 18:01.
 */

namespace app\common\service\upload;

use app\common\libs\upload\AliyunDriver;
use app\common\libs\upload\LocalDriver;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use function EasyWeChat\Kernel\Support\get_client_ip;

class UploadService extends BaseService
{
    private $uploadDrivers = [
        'Local' => LocalDriver::class,
        'Aliyun' => AliyunDriver::class,
    ];

    const FILE_THUMB_ARRAY = [
        'ppt' => '/statics/admin/upload/ppt.png',
        'pptx' => '/statics/admin/upload/ppt.png',
        'doc' => '/statics/admin/upload/doc.png',
        'docx' => '/statics/admin/upload/doc.png',
        'xls' => '/statics/admin/upload/xls.png',
        'xlsx' => '/statics/admin/upload/xls.png',
        'file' => '/statics/admin/upload/file.png',
        'video' => '/statics/admin/upload/video.png'
    ];

    /**
     *上传文件
     * @param int $groupId
     * @param int $userid
     * @param int $isadmin
     * @return bool
     */
    function uploadFile($groupId = 0, $userid = 0, $isadmin = 1)
    {
        $attachmentModel = new AttachmentModel();
        $attachmentModel->userid = $userid;
        $attachmentModel->isadmin = $isadmin;
        $attachmentModel->group_id = $groupId;
        $attachmentModel->module = AttachmentModel::MODULE_FILE;
        if (!$this->upload($attachmentModel)) {
            return false;
        }
        if (!$attachmentModel->filethumb) {
            $attachmentModel->filethumb = isset(self::FILE_THUMB_ARRAY[$attachmentModel->fileext]) ? self::FILE_THUMB_ARRAY[$attachmentModel->fileext] : self::FILE_THUMB_ARRAY['doc'];
        }
        $attachmentModel->isimage = AttachmentModel::IS_IMAGES_NO;
        $attachmentModel->save();
        return true;
    }

    /**
     *上传视频
     * @param int $groupId
     * @param int $userid
     * @param int $isadmin
     * @return bool
     */
    function uploadVideo($groupId = 0, $userid = 0, $isadmin = 1)
    {
        $attachmentModel = new AttachmentModel();
        $attachmentModel->userid = $userid;
        $attachmentModel->isadmin = $isadmin;
        $attachmentModel->group_id = $groupId;
        $attachmentModel->module = AttachmentModel::MODULE_VIDEO;
        if (!$this->upload($attachmentModel)) {
            return false;
        }
        if (!$attachmentModel->filethumb) {
            $attachmentModel->filethumb = self::FILE_THUMB_ARRAY['video'];
        }
        $attachmentModel->isimage = AttachmentModel::IS_IMAGES_NO;
        $attachmentModel->save();
        return true;
    }

    /**
     * 上传图片
     * @param int $groupId
     * @param int $userid
     * @param int $isadmin
     * @return bool
     */
    function uploadImage($groupId = 0, $userid = 0, $isadmin = 1)
    {
        try {
            $attachmentModel = new AttachmentModel();
            $attachmentModel->userid = $userid;
            $attachmentModel->isadmin = $isadmin;
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_IMAGE;
            if (!$this->upload($attachmentModel)) {
                return false;
            }
            $attachmentModel->filethumb = $attachmentModel->fileurl;
            $attachmentModel->isimage = AttachmentModel::IS_IMAGES_YES;
            $attachmentModel->save();
            return true;
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

        $config = ConfigModel::getConfigs();
        //最大上传大小
        $maxFileSize = ($attachmentModel->isadmin == 1 ? $config['uploadmaxsize'] : $config['qtuploadmaxsize']) * 1024;
        //允许上传文件格式
        $uploadAllowExt = str_replace('|', ',', ($attachmentModel->isadmin == 1 ? $config['uploadallowext'] : $config['qtuploadallowext']));
        try {
            validate(['file' => "filesize:{$maxFileSize}|fileExt:{$uploadAllowExt}"])
                ->message([
                    'file.filesize' => '文件大小不能超过' . ($maxFileSize / 1024) . '文件大小不能超过',
                    'file.fileExt' => '文件格式不对',
                ])
                ->check(request()->file());
        } catch (\think\exception\ValidateException $e) {
            $this->setError($e->getMessage());
            return false;
        }
        $attachmentDriver = isset($config['attachment_driver']) ? $config['attachment_driver'] : 'Local';
        if (isset($this->uploadDrivers[$attachmentDriver])) {
            $driver = new $this->uploadDrivers[$attachmentDriver]($config);
            $driver->upload($attachmentModel);
        } else {
            $this->setError('找不到相应上传驱动，请联系管理员！');
            return false;
        }

        return true;
    }
}