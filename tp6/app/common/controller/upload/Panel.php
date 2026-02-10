<?php

/**
 * User: zhlhuang
 */

namespace app\common\controller\upload;

use app\common\controller\AdminController;
use app\common\model\upload\AttachmentGroupModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\upload\UploadService;
use think\facade\View;
use think\Request;

/**
 * 上传面板
 *
 * @package app\common\controller\upload
 */
class Panel extends AdminController
{
    public $noNeedPermission = ['*'];

    /**
     * 统一上传面板入口
     *
     * @param Request $request
     * @return string
     */
    function index(Request $request)
    {
        $module = $this->normalizeModule((string)$request->get('module', AttachmentModel::MODULE_IMAGE));
        $isPrivate = intval($request->param('is_private', 0));

        $panelTitleMap = [
            AttachmentModel::MODULE_IMAGE => '图片上传',
            AttachmentModel::MODULE_VIDEO => '视频上传',
            AttachmentModel::MODULE_FILE => '文件上传',
        ];
        $acceptMap = [
            AttachmentModel::MODULE_IMAGE => 'image/*',
            AttachmentModel::MODULE_VIDEO => 'video/*',
            AttachmentModel::MODULE_FILE => '.xls,.doc,.ppt,.xlsx,.docx,.pptx,.pdf',
        ];
        $callbackMap = [
            AttachmentModel::MODULE_IMAGE => 'ZTBCMS_UPLOAD_IMAGE',
            AttachmentModel::MODULE_VIDEO => 'ZTBCMS_UPLOAD_VIDEO',
            AttachmentModel::MODULE_FILE => 'ZTBCMS_UPLOAD_FILE',
        ];
        $maxUploadMap = [
            AttachmentModel::MODULE_IMAGE => 99,
            AttachmentModel::MODULE_VIDEO => 99,
            AttachmentModel::MODULE_FILE => 10,
        ];

        return View::fetch('index', [
            'module' => $module,
            'groupType' => $module,
            'isPrivate' => $isPrivate,
            'panelTitle' => $panelTitleMap[$module] ?? $panelTitleMap[AttachmentModel::MODULE_IMAGE],
            'accept' => $acceptMap[$module] ?? $acceptMap[AttachmentModel::MODULE_IMAGE],
            'callbackDefault' => $callbackMap[$module] ?? $callbackMap[AttachmentModel::MODULE_IMAGE],
            'maxUpload' => $maxUploadMap[$module] ?? $maxUploadMap[AttachmentModel::MODULE_IMAGE],
        ]);
    }

    /**
     * 归一化模块类型
     *
     * @param string $module
     * @return string
     */
    private function normalizeModule(string $module): string
    {
        $module = strtolower(trim($module));
        $allowModules = [
            AttachmentModel::MODULE_IMAGE,
            AttachmentModel::MODULE_VIDEO,
            AttachmentModel::MODULE_FILE,
        ];

        return in_array($module, $allowModules, true) ? $module : AttachmentModel::MODULE_IMAGE;
    }

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
        $module = $this->normalizeModule((string)$request->get('module', AttachmentModel::MODULE_IMAGE));
        $where[] = ['module', '=', $module];
        $where[] = ['user_type', '=', AttachmentModel::USER_TYPE_ADMIN];

        $groupId = $request->get('group_id', 'all');
        if ($groupId !== 'all') {
            $where[] = ['group_id', '=', $groupId];
        }
        $limit = $request->get('limit', 10);
        $file_list = AttachmentModel::where($where)
            ->visible(['aid', 'filename', 'filepath', 'fileurl', 'filethumb', 'filesize', 'create_time'])
            ->order('aid', 'DESC')
            ->paginate($limit);

        $setting = compact('module');

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
            if ($uploadService->uploadUEImage(
                $groupId == 'all' ? 0 : $groupId,
                $userInfo['id'],
                AttachmentModel::USER_TYPE_ADMIN
            )) {
                return json(self::createReturn(true, [], '上传成功'));
            } else {
                return json(self::createReturn(false, [], $uploadService->getError()));
            }
        }

        return View::fetch('imageUEUpload');
    }
}
