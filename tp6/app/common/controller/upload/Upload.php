<?php

namespace app\common\controller\upload;


use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use app\common\model\ConfigModel;
use app\common\model\upload\AttachmentModel;
use app\common\service\BaseService;
use think\facade\Db;
use think\facade\View;
use think\Request;

class Upload extends AdminController
{
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
     * 上传设置
     * @param  Request  $request
     *
     * @return array|string
     * @throws \Exception
     */
    function setting(Request $request)
    {
        if ($request->isPost()) {
            $adminConfigService = new AdminConfigService();
            $data = [
                'attachment_driver'             => $request->post("attachment_driver"),
                'attachment_local_domain'       => $request->post("attachment_local_domain"),
                'attachment_aliyun_key_id'      => $request->post("attachment_aliyun_key_id"),
                'attachment_aliyun_key_secret'  => $request->post("attachment_aliyun_key_secret"),
                'attachment_aliyun_endpoint'    => $request->post("attachment_aliyun_endpoint"),
                'attachment_aliyun_bucket'      => $request->post("attachment_aliyun_bucket"),
                'attachment_aliyun_domain'      => $request->post("attachment_aliyun_domain"),
                'attachment_aliyun_privilege'   => $request->post("attachment_aliyun_privilege"),
                'attachment_aliyun_expire_time' => $request->post("attachment_aliyun_expire_time"),
                'uploadmaxsize'                 => $request->post("uploadmaxsize"),
                'uploadallowext'                => $request->post("uploadallowext"),
                'qtuploadmaxsize'               => $request->post("qtuploadmaxsize"),
                'qtuploadallowext'              => $request->post("qtuploadallowext"),
                'fileexclude'                   => $request->post("fileexclude"),
                'watermarkenable'               => $request->post("watermarkenable"),
                'watermarkminwidth'             => $request->post("watermarkminwidth"),
                'watermarkminheight'            => $request->post("watermarkminheight"),
                'watermarkimg'                  => $request->post("watermarkimg"),
                'watermarkpct'                  => $request->post("watermarkpct"),
                'watermarkquality'              => $request->post("watermarkquality"),
                'watermarkpos'                  => $request->post("watermarkpos"),
            ];
            return $adminConfigService->updateConfig($data);
        }
        $config = ConfigModel::column('value', 'varname');
        $fields = [
            'attachment_driver',
            'attachment_local_domain',
            'attachment_aliyun_key_id',
            'attachment_aliyun_key_secret',
            'attachment_aliyun_endpoint',
            'attachment_aliyun_bucket',
            'attachment_aliyun_domain',
            'attachment_aliyun_privilege',
            'attachment_aliyun_expire_time',
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
        $_config = [];
        foreach ($fields as $key) {
            $_config[$key] = isset($config[$key]) ? $config[$key] : '';
        }
        $dirverList = [
            'Local'  => '本地',
            'Aliyun' => '阿里云OSS【暂不支持水印】',
        ];
        return View::fetch('setting', [
            'config'     => $_config,
            'dirverList' => $dirverList
        ]);
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
                $where[] = ['filename', 'like', '%'.$filename.'%'];
            }
            $page = input('page', 1);
            $limit = input('limit', 15);
            $items = AttachmentModel::where($where)->page($page)->limit($limit)->order('aid desc')->select();
            foreach ($items as &$item) {
                $item['filesize'] = round($item['filesize'] / 1000);
            }
            $total = AttachmentModel::where($where)->count();
            $res = BaseService::createReturnList(true, $items, $page, $limit, $total, ceil($total / $limit));
            return json($res);
        }
        if ($action == 'doDelete') {
            $aid = input('aid');
            if (empty($aid)) {
                return self::makeJsonReturn(false, null, '参数异常');
            }
            $attachment = AttachmentModel::where('aid', $aid)->find();
            $attachment->delete();
            return self::makeJsonReturn(true, null, '操作完成');
        }
        if ($action == 'doBatchDelete') {
            $aids = input('aids');
            if (empty($aids)) {
                return self::makeJsonReturn(false, null, '请选择附件');
            }
            $attachments = AttachmentModel::where('aid', 'in', join(',', $aids))->select();
            $attachments->delete();
            return self::makeJsonReturn(true, null, '操作完成');
        }
        return view();
    }
}