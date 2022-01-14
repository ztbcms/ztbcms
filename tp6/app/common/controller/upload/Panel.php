<?php
/**
 * User: zhlhuang
 */

namespace app\common\controller\upload;

use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use app\common\libs\upload\AliyunDriver;
use app\common\model\upload\AttachmentGroupModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\ConfigService;
use app\common\service\upload\UploadService;
use think\App;
use think\facade\View;
use think\Request;
use function EasyWeChat\Kernel\Support\get_client_ip;

/**
 * 上传面板
 *
 * @package app\common\controller\upload
 */
class Panel extends AdminController
{
    public $noNeedPermission = ['*'];

    /**
     * 删除文件
     *
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    function deleteFiles(Request $request)
    {
        $files = $request->post('files');
        $uploadData = [];
        foreach ($files as $file) {
            $uploadData[] = [
                'aid' => $file['aid'],
                'delete_time' => time()
            ];
        }
        $attachmentModel = new AttachmentModel();
        if ($attachmentModel->saveAll($uploadData)) {
            return self::createReturn(true, [], '删除成功');
        } else {
            return self::createReturn(false, [], '操作失败');
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    function moveGralleryGroup(Request $request)
    {
        $files = $request->post('files');
        $groupId = $request->post('group_id');
        $uploadData = [];
        foreach ($files as $file) {
            $uploadData[] = [
                'aid' => $file['aid'],
                'group_id' => $groupId
            ];
        }
        $attachmentModel = new AttachmentModel();
        if ($attachmentModel->saveAll($uploadData)) {
            return self::createReturn(true, [], '移动成功');
        } else {
            return self::createReturn(false, [], '操作失败');
        }
    }

    /**
     * 资源列表
     *
     * @param Request $request
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    function getFilesByGroupIdList(Request $request)
    {
        $module = $request->get('module', AttachmentModel::MODULE_IMAGE);
        $where[] = ['module', '=', $module];
        $where[] = ['user_type', '=', AttachmentModel::USER_TYPE_ADMIN];

        $groupId = $request->get('group_id', 'all');
        if ($groupId !== 'all') {
            $where[] = ['group_id', '=', $groupId];
        }
        $file_list = AttachmentModel::where($where)
            ->visible(['aid', 'filename', 'filepath', 'fileurl', 'filethumb'])
            ->order('aid', 'DESC')
            ->paginate(15);

        $aliyun_is_direct = ConfigService::getInstance()
            ->getConfig('attachment_aliyun_is_direct', '0');
        $aliyun_sts = [];
        if (intval($aliyun_is_direct) === 1) {
            $aliyun_sts = (new AliyunDriver(ConfigService::getInstance()
                ->getConfigList()))->getStsToken($module);
            if (empty($aliyun_sts)) {
                //如果返回为空，则不使用直传
                $aliyun_is_direct = 0;
            }
        }
        $setting = compact('aliyun_is_direct', 'aliyun_sts');

        return self::createReturn(true, compact('file_list', 'setting'), 'ok');
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    function delGalleryGroup(Request $request)
    {
        $groupId = $request->post('group_id', '');
        $attachmentGroupModel = AttachmentGroupModel::where('group_id', $groupId)
            ->findOrEmpty();
        if ($groupId && !$attachmentGroupModel->isEmpty()) {
            if ($attachmentGroupModel->delete()) {
                return self::createReturn(true, [], '删除成功');
            } else {
                return self::createReturn(false, [], '数据未删除');
            }
        } else {
            return self::createReturn(false, [], '未找到相应记录');
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    function editGalleryGroup(Request $request)
    {
        $groupId = $request->post('group_id', '');
        $attachmentGroupModel = AttachmentGroupModel::where('group_id', $groupId)
            ->findOrEmpty();
        if ($groupId && !$attachmentGroupModel->isEmpty()) {
            $attachmentGroupModel->group_name = $request->post('group_name', '');
            if ($attachmentGroupModel->save()) {
                return self::createReturn(true, [], '更新成功');
            } else {
                return self::createReturn(false, [], '数据未更新');
            }
        } else {
            return self::createReturn(false, [], '未找到相应记录');
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    function addGalleryGroup(Request $request)
    {
        $attachmentGroupModel = new AttachmentGroupModel();
        $attachmentGroupModel->group_name = $request->post('group_name', '');
        $attachmentGroupModel->group_type = $request->post('group_type', AttachmentGroupModel::TYPE_IMAGE);
        if ($attachmentGroupModel->save()) {
            return self::createReturn(true, [], '创建成功');
        } else {
            return self::createReturn(false, [], '创建失败');
        }
    }

    /**
     * 获取文件分组
     *
     * @param Request $request
     *
     * @return array
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    function getGalleryGroup(Request $request)
    {
        //分组类型、默认是图片
        $groupType = $request->get('group_type', AttachmentGroupModel::TYPE_IMAGE);
        $lists = AttachmentGroupModel::where('group_type', $groupType)
            ->field(['group_id', 'group_name'])
            ->order('sort', 'DESC')
            ->select();

        return self::createReturn(true, $lists, 'ok');
    }

    /**
     * 图片上传面板
     *
     * @param Request $request
     *
     * @return string|\think\response\Json
     */
    function imageUpload(Request $request)
    {
        $isPrivate = $request->param('is_private', 0);
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            $uploadService->isPrivate = $isPrivate == 1;
            $userInfo = AdminUserService::getInstance()
                ->getInfo();
            if ($uploadService->uploadImage($groupId == 'all' ? 0 : $groupId, $userInfo['id'],
                AttachmentModel::USER_TYPE_ADMIN)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }

        return View::fetch('imageUpload', ['isPrivate' => $isPrivate]);
    }

    function directUpload(Request $request)
    {
        if ($request->isPost()) {
            $isPrivate = $request->param('is_private', 0);
            $groupId = intval($request->post('group_id', ''));

            $uploadService = new UploadService();
            $uploadService->isPrivate = $isPrivate == 1;
            $userInfo = AdminUserService::getInstance()
                ->getInfo();
            $attachmentModel = new AttachmentModel();
            $attachmentModel->user_type = AttachmentModel::USER_TYPE_ADMIN;
            $attachmentModel->user_id = $userInfo['id'];
            $attachmentModel->group_id = $groupId;
            $attachmentModel->module = AttachmentModel::MODULE_IMAGE;
            $attachmentModel->filename = $request->post('filename', '');
            $attachmentModel->filesize = $request->post('filesize', '');
            $attachmentModel->fileext = $request->post('fileext', '');
            $attachmentModel->fileurl = $request->post('fileurl', '');
            $attachmentModel->filethumb = $request->post('fileurl', '');
            $attachmentModel->filepath = '';
            $attachmentModel->uploadtime = time();
            $attachmentModel->uploadip = $request->ip();
            $attachmentModel->is_private = $isPrivate;
            $attachmentModel->hash = '';
            $attachmentModel->driver = ConfigService::getInstance()
                ->getConfig('attachment_driver') ?: 'Local';

            if ($attachmentModel->save()) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }
    }

    /**
     * 视频上传面板
     *
     * @param Request $request
     *
     * @return string|\think\response\Json
     */
    function videoUpload(Request $request)
    {
        $isPrivate = $request->param('is_private', 0);
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            $uploadService->isPrivate = $isPrivate == 1;
            $userInfo = AdminUserService::getInstance()
                ->getInfo();
            if ($uploadService->uploadVideo($groupId == 'all' ? 0 : $groupId, $userInfo['id'],
                AttachmentModel::USER_TYPE_ADMIN)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }

        return View::fetch('videoUpload', ['isPrivate' => $isPrivate]);
    }

    /**
     * 文件（文档）上传面板
     *
     * @param Request $request
     *
     * @return string|\think\response\Json
     */
    function fileUpload(Request $request)
    {
        $isPrivate = $request->param('is_private', 0);
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            $uploadService->isPrivate = $isPrivate == 1;
            $userInfo = AdminUserService::getInstance()
                ->getInfo();
            if ($uploadService->uploadFile($groupId == 'all' ? 0 : $groupId, $userInfo['id'],
                AttachmentModel::USER_TYPE_ADMIN)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }

        return View::fetch('fileUpload', ['isPrivate' => $isPrivate]);
    }

    /**
     * 上传UEditor文件图片
     *
     * @param Request $request
     *
     * @return string|\think\response\Json
     */
    function imageUEUpload(Request $request)
    {
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            $userInfo = AdminUserService::getInstance()
                ->getInfo();
            if ($uploadService->uploadUEImage($groupId == 'all' ? 0 : $groupId, $userInfo['id'],
                AttachmentModel::USER_TYPE_ADMIN)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }

        return View::fetch('imageUEUpload');
    }
}