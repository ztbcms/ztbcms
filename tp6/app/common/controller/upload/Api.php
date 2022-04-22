<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-16
 * Time: 15:55.
 */

declare(strict_types=1);

namespace app\common\controller\upload;


use app\BaseController;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\response\Json;

/**
 * 上传接口，前端接口继承 BaseController
 *
 * @package app\common\controller\upload
 */
class Api extends BaseController
{
    /**
     * 上传图片（可以根据实际需要写自己的api接口）
     * @return Json
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    function imageUpload(): Json
    {
        $uploadService = new UploadService();
        //设置私有读
        $isPrivate = request()->param('is_private', 0);
        $uploadService->isPrivate = $isPrivate == 1;

        $attachmentModel = $uploadService->uploadImage(0, 0, 'user');
        if (!$attachmentModel) {
            return json(self::createReturn(false, null, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }

    /**
     * 上传视频（可以根据实际需要写自己的api接口）
     * @return Json
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    function videoUpload(): Json
    {
        $uploadService = new UploadService();
        //设置私有读
        $isPrivate = request()->param('is_private', 0);
        $uploadService->isPrivate = $isPrivate == 1;

        $attachmentModel = $uploadService->uploadVideo(0, 0, 'user');
        if (!$attachmentModel) {
            return json(self::createReturn(false, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }

    /**
     * 上传文件（可以根据实际需要写自己的api接口）
     * @return Json
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    function fileUpload(): Json
    {
        $uploadService = new UploadService();
        //设置私有读
        $isPrivate = request()->param('is_private', 0);
        $uploadService->isPrivate = $isPrivate == 1;
        $attachmentModel = $uploadService->uploadFile(0, 0, 'user');
        if (!$attachmentModel) {
            return json(self::createReturn(false, $uploadService->getError()));
        } else {
            $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
                ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
                ->find();
            return json(self::createReturn(true, $attachmentModelResult));
        }
    }
}