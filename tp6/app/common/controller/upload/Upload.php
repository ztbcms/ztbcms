<?php

namespace app\common\controller\upload;


use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use app\common\model\ConfigModel;
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
}