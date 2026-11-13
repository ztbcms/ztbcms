<?php

namespace app\common\controller\upload;


use think\facade\View;
use think\Request;
use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use app\common\service\ConfigService;
use app\common\service\jwt\JwtService;
use app\common\service\upload\UploadService;

class Upload extends AdminController
{
    /**
     * 上传凭证默认有效期（秒）
     */
    const UPLOAD_TOKEN_TTL = 300;

    /**
     * 无需菜单鉴权的方法（需后台登录，供上传面板获取当前管理员上传凭证）
     * @var array
     */
    public $noNeedPermission = ['getUploadToken'];

    /**
     * 上传示例
     *
     * @return string
     */
    function demo()
    {
        return View::fetch('demo');
    }

    /**
     * 直传示例
     *
     * @return string
     */
    function directUploadDemo()
    {
        return View::fetch('direct_upload_demo');
    }

    /**
     * 获取上传用的临时 JWT Token
     * 用于后台管理员上传文件时的身份识别
     *
     * @return \think\response\Json
     */
    function getUploadToken()
    {
        // 仅从服务端登录态获取当前管理员身份，忽略任何请求参数输入
        $adminUserService = AdminUserService::getInstance();
        $adminInfo = $adminUserService->getInfo();
        $userId = intval($adminInfo['id'] ?? 0);

        if (empty($adminInfo) || $userId <= 0) {
            return self::returnErrorJson('未登录');
        }

        $ttl = intval(config('jwt.scene.upload.ttl', self::UPLOAD_TOKEN_TTL));
        if ($ttl <= 0) {
            $ttl = self::UPLOAD_TOKEN_TTL;
        }
        $expireTime = time() + $ttl;

        // 使用独立 upload 场景签发短期上传专用 JWT Token
        $jwtService = new JwtService('upload');
        $payload = [
            'uid' => $userId,
            'user_type' => AttachmentModel::USER_TYPE_ADMIN,
            'purpose' => 'upload',
            'exp' => $expireTime,
        ];

        $token = $jwtService->createToken($payload);

        return self::returnSuccessJson([
            'token' => $token,
            'expire_time' => $expireTime,
            'ttl' => $ttl,
        ]);
    }

    /**
     * 上传设置
     *
     * @param Request $request
     * @return string|\think\response\Json
     */
    function setting(Request $request)
    {
        if ($request->isPost()) {
            $configService = ConfigService::getInstance();

            return $configService->updateConfig($request->post()) ? self::returnSuccessJson() : self::returnErrorJson('操作失败');
        }
        if ($request->isAjax()) {

            $fields = [
                'attachment_driver',
                'attachment_local_domain',
                // 阿里云 OSS
                'attachment_aliyun_key_id',
                'attachment_aliyun_key_secret',
                'attachment_aliyun_endpoint',
                'attachment_aliyun_bucket',
                'attachment_aliyun_sts_role_arn',
                'attachment_aliyun_is_direct',
                'attachment_aliyun_privilege',
                'attachment_aliyun_expire_time',
                // 七牛云
                'attachment_qiniu_access_key',
                'attachment_qiniu_secret_key',
                'attachment_qiniu_bucket',
                'attachment_qiniu_domain',
                'attachment_qiniu_privilege',
                'attachment_qiniu_expire_time',
                // 直传路径模板
                'attachment_direct_file_dir_template',
                // 通用配置
                'uploadmaxsize',
                'uploadallowext',
                'qtuploadmaxsize',
                'qtuploadallowext',
                'fileexclude',
                'watermarkenable',
                'watermarkminwidth',
                'watermarkminheight',
                'watermarkimg',
                'watermarkpct',
                'watermarkquality',
                'watermarkpos',
            ];

            $config = (new ConfigModel)->whereIn('varname', $fields)
                ->column('value', 'varname');

            return self::returnSuccessJson(compact('config'));
        }

        return View::fetch('setting');
    }

    /**
     * 附件管理
     */
    function attachments()
    {
        $action = input('_action');
        if ($action == 'getList') {
            $where = [];
            $tab = input('tab', 0);
            if (!empty($tab)) {
                if ($tab == 1) {
                    $where[] = ['module', '=', AttachmentModel::MODULE_IMAGE];
                }
                if ($tab == 2) {
                    $where['module'] = AttachmentModel::MODULE_VIDEO;
                }
                if ($tab == 3) {
                    $where[] = ['module', '=', AttachmentModel::MODULE_FILE];
                }
            }
            $filename = input('filename', '');
            if (!empty($filename)) {
                $where[] = ['filename', 'like', '%' . $filename . '%'];
            }
            $createTime = input('create_time/a', []);
            if (is_array($createTime) && count($createTime) === 2 && !empty($createTime[0]) && !empty($createTime[1])) {
                $where[] = ['create_time', 'between', [strtotime($createTime[0]), strtotime($createTime[1]) + 24 * 60 * 60 - 1]];
            }
            $page = input('page', 1);
            $limit = input('limit', 15);
            $items = AttachmentModel::where($where)
                ->page($page)
                ->limit($limit)
                ->order('aid desc')
                ->select();
            foreach ($items as &$item) {
                $item['filesize'] = round($item['filesize'] / 1000);
            }
            $total = AttachmentModel::where($where)
                ->count();
            $res = BaseService::createReturnList(true, $items, $page, $limit, $total, ceil($total / $limit));

            return json($res);
        }
        if ($action == 'doDelete') {
            $aid = input('aid');
            if (empty($aid)) {
                return self::makeJsonReturn(false, null, '参数异常');
            }
            $attachment = AttachmentModel::where('aid', $aid)
                ->find();
            if (empty($attachment)) {
                return self::makeJsonReturn(false, null, '附件不存在或已删除');
            }
            if ($attachment && $attachment->filepath) {
                $uploadService = new \app\common\service\upload\UploadService($attachment->driver ?: '');
                $uploadService->deleteFile($attachment->filepath);
            }
            $attachment->delete();

            return self::makeJsonReturn(true, null, '操作完成');
        }
        if ($action == 'doBatchDelete') {
            $aids = input('aids/a', []);
            if (empty($aids)) {
                return self::makeJsonReturn(false, null, '请选择附件');
            }
            $aidList = array_map('intval', $aids);
            $aidList = array_unique(array_filter($aidList, function ($item) {
                return $item > 0;
            }));
            if (empty($aidList)) {
                return self::makeJsonReturn(false, null, '请选择有效附件');
            }
            $attachments = AttachmentModel::where('aid', 'in', $aidList)
                ->select();
            foreach ($attachments as $attachment) {
                if ($attachment->filepath) {
                    $uploadService = new \app\common\service\upload\UploadService($attachment->driver ?: '');
                    $uploadService->deleteFile($attachment->filepath);
                }
            }
            $attachments->delete();

            return self::makeJsonReturn(true, null, '操作完成');
        }

        return view();
    }
}
