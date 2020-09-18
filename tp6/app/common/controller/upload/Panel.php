<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 14:43.
 */

namespace app\common\controller\upload;

use app\common\controller\AdminController;
use app\common\model\upload\AttachmentGroupModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\facade\View;
use think\Request;

class Panel extends AdminController
{
    /**
     * @param Request $request
     * @throws \Exception
     * @return array
     */
    function deleteFiles(Request $request)
    {
        $files = $request->post('files');
        $uploadData = [];
        foreach ($files as $file) {
            $uploadData[] = [
                'aid' => $file['aid'],
                'delete_status' => 1
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
     * @throws \Exception
     * @return array
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
     * @param Request $request
     * @throws \think\db\exception\DbException
     * @return array
     */
    function getFilesByGroupIdList(Request $request)
    {
        $module = $request->get('module', AttachmentModel::MODULE_IMAGE);
        $where[] = ['module', '=', $module];
        $where[] = ['isadmin', '=', AttachmentModel::IS_ADMIN_YES];

        $groupId = $request->get('group_id', 'all');
        if ($groupId !== 'all') {
            $where[] = ['group_id', '=', $groupId];
        }
        $lists = AttachmentModel::where($where)
            ->field(['aid', 'filename', 'filepath', 'fileurl', 'filethumb', 'driver', 'module'])//driver,module数据处理需要
            ->visible(['aid', 'filename', 'filepath', 'fileurl', 'filethumb'])
            ->order('aid', 'DESC')
            ->paginate(15);
        return self::createReturn(true, $lists, 'ok');
    }

    /**
     * @param Request $request
     * @return array
     */
    function delGalleryGroup(Request $request)
    {
        $groupId = $request->post('group_id', '');
        $attachmentGroupModel = AttachmentGroupModel::where('group_id', $groupId)->findOrEmpty();
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
     * @return array
     */
    function editGalleryGroup(Request $request)
    {
        $groupId = $request->post('group_id', '');
        $attachmentGroupModel = AttachmentGroupModel::where('group_id', $groupId)->findOrEmpty();
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
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @return array
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
     * @return array|string
     */
    function imageUpload(Request $request)
    {
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            if ($uploadService->uploadImage($groupId == 'all' ? 0 : $groupId, $this->user->id)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }
        return View::fetch('imageUpload');
    }

    /**
     * 视频上传面板
     * @param Request $request
     * @return array|string
     */
    function videoUpload(Request $request)
    {
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            if ($uploadService->uploadVideo($groupId == 'all' ? 0 : $groupId, $this->user->id)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }
        return View::fetch('videoUpload');
    }

    /**
     * 文件（文档）上传面板
     *
     * @param Request $request
     * @return string|\think\response\Json
     */
    function fileUpload(Request $request)
    {
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            if ($uploadService->uploadFile($groupId == 'all' ? 0 : $groupId, $this->user->id)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }
        return View::fetch('fileUpload');
    }

    /**
     * 上传UEditor文件图片（公开读）
     * @param Request $request
     * @return string|\think\response\Json
     */
    function imageUEUpload(Request $request)
    {
        if ($request->isPost()) {
            $groupId = $request->post('group_id', '');
            $uploadService = new UploadService();
            if ($uploadService->uploadUEImage($groupId == 'all' ? 0 : $groupId, $this->user->id)) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }
        return View::fetch('imageUEUpload');
    }
}