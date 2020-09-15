<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 18:01.
 */

namespace app\common\service\upload;

use app\common\libs\upload\LocalDriver;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use function EasyWeChat\Kernel\Support\get_client_ip;

class UploadService extends BaseService
{
    private $uploadDrivers = [
        'Local' => LocalDriver::class
    ];

    /**
     * 上传图片
     * @param int $groupId
     * @param int $isadmin
     * @param int $userid
     * @return bool
     */
    function uploadImage($groupId = 0, $isadmin = 1, $userid = 0)
    {
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
        $attachmentModel->fileext = $file->getExtension();
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
        }

        return true;
    }
}