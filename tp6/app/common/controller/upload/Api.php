<?php

declare(strict_types=1);

namespace app\common\controller\upload;


use think\facade\Event;
use think\response\Json;
use app\BaseController;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;

/**
 * 上传接口，前端接口继承 BaseController
 *
 * @package app\common\controller\upload
 */
class Api extends BaseController
{
    /**
     * 控制器中间件 - 启用 JWT 鉴权
     * @var array
     */
    protected $middleware = [
        \app\api\middleware\ApiAuth::class,
    ];

    /**
     * 获取前台上传配置
     * 前端可通过此接口获取允许的文件后缀和大小限制
     * @return Json
     */
    function getUploadConfig(): Json
    {
        $config = UploadService::getUploadConfig();
        return json(self::createReturn(true, $config));
    }

    /**
     * 统一上传接口（自动判断文件类型）
     * @return Json
     */
    function upload(): Json
    {
        $driver = request()->param('driver', '');  // 支持指定驱动
        $uploadService = new UploadService($driver);

        // 设置私有读
        $isPrivate = request()->param('is_private', 0);
        $uploadService->isPrivate = $isPrivate == 1;

        // 从 JWT 中获取用户信息
        $authorization = request()->authorization ?? [];
        $userId = $authorization['uid'] ?? 0;
        $userType = $authorization['user_type'] ?? 'user';

        $attachmentModel = $uploadService->uploadAny(0, $userId, $userType);
        if (!$attachmentModel) {
            return json(self::createReturn(false, null, $uploadService->getError()));
        }

        $attachmentModelResult = AttachmentModel::where('aid', $attachmentModel->aid)
            ->visible(['aid', 'filename', 'module', 'fileurl', 'filethumb'])
            ->find();

        return json(self::createReturn(true, $attachmentModelResult));
    }

    /**
     * 本地直传（仅上传不保存附件记录）
     * @return Json
     */
    function uploadLocalDirect(): Json
    {
        $isPrivate = request()->param('is_private', 0);
        $module = trim((string)request()->param('module', ''));

        $authorization = request()->authorization ?? [];
        $userId = intval($authorization['uid'] ?? 0);
        $userType = $authorization['user_type'] ?? 'user';

        $uploadService = new UploadService('Local');
        $uploadService->setIsPrivate(intval($isPrivate) == 1);

        $result = $uploadService->uploadDirectOnly($userId, $userType, $module);
        if (!$result) {
            return json(self::createReturn(false, null, $uploadService->getError()));
        }

        return json(self::createReturn(true, $result));
    }

    /**
     * 获取直传凭证
     * 用于客户端直传到云存储（阿里云 OSS、七牛云等）
     * @param string driver 可选，指定上传驱动（Local/Aliyun/Qiniu），默认使用系统配置
     * @return Json
     */
    function getDirectUploadCredential(): Json
    {
        $filename = trim(request()->param('filename', ''));
        if (empty($filename)) {
            return json(self::createReturn(false, null, '参数不完整'));
        }

        $fileext = UploadService::normalizeFileExt('', $filename);
        if (empty($fileext)) {
            return json(self::createReturn(false, null, '无法识别文件后缀'));
        }

        $module = UploadService::inferModuleByExt($fileext);
        $driver = request()->param('driver', '');  // 支持指定驱动，空则使用系统配置
        $uploadService = new UploadService($driver);

        if (!$uploadService->supportsDirectUpload()) {
            return json(self::createReturn(false, null, '当前存储驱动不支持直传'));
        }

        $credential = $uploadService->getUploadCredential($module, $filename, $fileext);
        if (empty($credential)) {
            return json(self::createReturn(false, null, '获取直传凭证失败'));
        }

        return json(self::createReturn(true, $credential));
    }

    /**
     * 直传完成后记录附件信息
     * 客户端直传成功后调用此接口保存附件记录
     * @return Json
     */
    function saveDirectUploadRecord(): Json
    {
        $filepath = request()->param('filepath', '');
        $filename = request()->param('filename', '');
        $filesize = request()->param('filesize', 0);
        $fileext = UploadService::normalizeFileExt('', $filepath);
        if (empty($fileext)) {
            $fileext = UploadService::normalizeFileExt('', $filename);
        }
        if (empty($fileext)) {
            return json(self::createReturn(false, null, '无法识别文件后缀'));
        }
        $module = request()->param('module', '');
        $module = trim((string)$module);
        if (empty($module)) {
            $module = UploadService::inferModuleByExt($fileext);
        }
        $allowModules = [AttachmentModel::MODULE_IMAGE, AttachmentModel::MODULE_VIDEO, AttachmentModel::MODULE_FILE];
        if (!in_array($module, $allowModules, true)) {
            $module = UploadService::inferModuleByExt($fileext);
        }
        $isPrivate = request()->param('is_private', 0);
        $groupId = intval(request()->param('group_id', 0));
        $driver = request()->param('driver', '');  // 接收前端传入的驱动

        if (empty($filepath) || empty($filename)) {
            return json(self::createReturn(false, null, '参数不完整'));
        }

        // 从 JWT 中获取用户信息
        $authorization = request()->authorization ?? [];
        $userId = $authorization['uid'] ?? 0;
        $userType = $authorization['user_type'] ?? 'user';

        $config = ConfigModel::getConfigs();
        $resolvedDriver = $driver ?: ($config['attachment_driver'] ?? AttachmentModel::DRIVER_LOCAL);
        $uploadService = new UploadService($resolvedDriver);
        $fileurl = $uploadService->getFileUrl($filepath, $isPrivate == 1);

        $existsAttachment = AttachmentModel::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('driver', $resolvedDriver)
            ->where('filepath', $filepath)
            ->find();
        if (!empty($existsAttachment)) {
            return json(self::createReturn(true, [
                'aid' => $existsAttachment->aid,
                'filename' => $existsAttachment->filename,
                'fileurl' => $existsAttachment->fileurl,
                'filethumb' => $existsAttachment->filethumb,
            ]));
        }

        $attachmentModel = new AttachmentModel();
        $attachmentModel->user_id = $userId;
        $attachmentModel->user_type = $userType;
        $attachmentModel->group_id = $groupId > 0 ? $groupId : 0;
        $attachmentModel->module = $module;
        $attachmentModel->filename = $filename;
        $attachmentModel->filepath = $filepath;
        $attachmentModel->fileurl = $fileurl;
        $attachmentModel->filesize = intval($filesize);
        $attachmentModel->fileext = $fileext;
        $attachmentModel->is_private = $isPrivate == 1;
        $attachmentModel->create_time = time();
        $attachmentModel->update_time = time();
        $attachmentModel->upload_ip = request()->ip();
        $attachmentModel->hash = md5($filepath);

        // 设置缩略图
        $filethumbArray = UploadService::FILE_THUMB_ARRAY;
        if ($module == AttachmentModel::MODULE_IMAGE) {
            $attachmentModel->filethumb = $filepath;
        } elseif ($module == AttachmentModel::MODULE_VIDEO) {
            $attachmentModel->filethumb = $filethumbArray['video'];
        } else {
            $attachmentModel->filethumb = $filethumbArray[$fileext] ?? $filethumbArray['file'];
        }

        // 设置存储驱动（优先使用传入的 driver，否则使用系统配置）
        $attachmentModel->driver = $resolvedDriver;

        if (!$attachmentModel->save()) {
            return json(self::createReturn(false, null, '保存记录失败'));
        }

        Event::trigger('UploadRecorded', [
            'aid' => $attachmentModel->aid,
            'user_id' => intval($attachmentModel->user_id),
            'user_type' => (string)$attachmentModel->user_type,
            'driver' => (string)$attachmentModel->driver,
            'module' => (string)$attachmentModel->module,
            'filepath' => (string)$attachmentModel->filepath,
            'fileurl' => (string)$attachmentModel->fileurl,
            'is_private' => intval($attachmentModel->is_private),
            'upload_time' => intval($attachmentModel->create_time),
        ]);

        return json(self::createReturn(true, [
            'aid' => $attachmentModel->aid,
            'filename' => $attachmentModel->filename,
            'fileurl' => $attachmentModel->fileurl,
            'filethumb' => $attachmentModel->filethumb,
        ]));
    }
}
