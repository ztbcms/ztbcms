<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-16
 * Time: 15:55.
 */

namespace app\common\controller\upload;


use app\BaseController;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;

class Api extends BaseController
{
    /**
     * 上传图片（可以根据实际需要写自己的api接口）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return \think\response\Json
     */
    function imageUpload()
    {
        $uploadService = new UploadService();
        $attachmentModel = $uploadService->uploadImage(0, 0, 0);
        if (!$attachmentModel) {
            return json(self::createReturn(false, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->field(['aid', 'filename', 'filepath', 'fileurl', 'filethumb', 'driver', 'module'])//driver,module数据处理需要
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }

    /**
     * 上传视频（可以根据实际需要写自己的api接口）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return \think\response\Json
     */
    function videoUpload()
    {
        $uploadService = new UploadService();
        $attachmentModel = $uploadService->uploadVideo(0, 0, 0);
        if (!$attachmentModel) {
            return json(self::createReturn(false, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->field(['aid', 'filename', 'filepath', 'fileurl', 'filethumb', 'driver', 'module'])//driver,module数据处理需要
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }

    /**
     * 上传文件（可以根据实际需要写自己的api接口）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return \think\response\Json
     */
    function fileUpload()
    {
        $uploadService = new UploadService();
        $attachmentModel = $uploadService->uploadFile(0, 0, 0);
        if (!$attachmentModel) {
            return json(self::createReturn(false, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->field(['aid', 'filename', 'filepath', 'fileurl', 'filethumb', 'driver', 'module'])//driver,module数据处理需要
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }
}