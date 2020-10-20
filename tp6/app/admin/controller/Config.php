<?php
/**
 * User: jayinton
 */

namespace app\admin\controller;

use app\admin\service\AdminConfigService;
use app\admin\service\ConfigFieldService;
use app\common\controller\AdminController;
use think\facade\Db;
use think\Request;

/**
 * 系统设置
 * Class Config
 */
class Config extends AdminController
{
    /**
     * 基础设置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            // 更新
            $data = [
                'sitename'       => $request->post("sitename"),
                'siteurl'        => $request->post("siteurl"),
                'sitefileurl'    => $request->post("sitefileurl"),
                'siteemail'      => $request->post("siteemail"),
                'sitekeywords'   => $request->post("sitekeywords"),
                'siteinfo'       => $request->post("siteinfo"),
                'checkcode_type' => $request->post("checkcode_type"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $_config = $adminConfigService->getConfig()['data'];
            $fields = [
                'sitename', 'siteurl', 'sitefileurl', 'siteemail', 'sitekeywords', 'siteinfo', 'checkcode_type'
            ];
            $config = [];
            foreach ($fields as $i => $key) {
                $config[$key] = $_config[$key];
            }
            return self::makeJsonReturn(true, $config);
        }

        return view('index');
    }

    /**
     * 邮箱设置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function email(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            $data = [
                'mail_type'     => $request->post("mail_type"),
                'mail_server'   => $request->post("mail_server"),
                'mail_port'     => $request->post("mail_port"),
                'mail_from'     => $request->post("mail_from"),
                'mail_fname'    => $request->post("mail_fname"),
                'mail_auth'     => $request->post("mail_auth"),
                'mail_user'     => $request->post("mail_user"),
                'mail_password' => $request->post("mail_password"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $_config = $adminConfigService->getConfig()['data'];
            $fields = [
                'mail_type', 'mail_server', 'mail_port', 'mail_from', 'mail_fname', 'mail_auth', 'mail_user', 'mail_password'
            ];
            $config = [];
            foreach ($fields as $i => $key) {
                $config[$key] = $_config[$key];
            }
            return self::makeJsonReturn(true, $config);
        }

        return view('email');
    }

    /**
     * 附件设置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function attach(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            $data = [
                'attachment_driver'            => $request->post("attachment_driver"),
                'attachment_aliyun_key_id'     => $request->post("attachment_aliyun_key_id"),
                'attachment_aliyun_key_secret' => $request->post("attachment_aliyun_key_secret"),
                'attachment_aliyun_endpoint'   => $request->post("attachment_aliyun_endpoint"),
                'attachment_aliyun_bucket'     => $request->post("attachment_aliyun_bucket"),
                'attachment_aliyun_domain'     => $request->post("attachment_aliyun_domain"),
                'uploadmaxsize'                => $request->post("uploadmaxsize"),
                'uploadallowext'               => $request->post("uploadallowext"),
                'qtuploadmaxsize'              => $request->post("qtuploadmaxsize"),
                'qtuploadallowext'             => $request->post("qtuploadallowext"),
                'fileexclude'                  => $request->post("fileexclude"),
                'ftphost'                      => $request->post("ftphost"),
                'ftpport'                      => $request->post("ftpport"),
                'ftpuppat'                     => $request->post("ftpuppat"),
                'ftpuser'                      => $request->post("ftpuser"),
                'ftppassword'                  => $request->post("ftppassword"),
                'ftppasv'                      => $request->post("ftppasv"),
                'ftpssl'                       => $request->post("ftpssl"),
                'ftptimeout'                   => $request->post("ftptimeout"),
                'watermarkenable'              => $request->post("watermarkenable"),
                'watermarkminwidth'            => $request->post("watermarkminwidth"),
                'watermarkminheight'           => $request->post("watermarkminheight"),
                'watermarkimg'                 => $request->post("watermarkimg"),
                'watermarkpct'                 => $request->post("watermarkpct"),
                'watermarkquality'             => $request->post("watermarkquality"),
                'watermarkpos'                 => $request->post("watermarkpos"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $_config = $adminConfigService->getConfig()['data'];
            $fields = [
                'attachment_driver',
                'attachment_aliyun_key_id',
                'attachment_aliyun_key_secret',
                'attachment_aliyun_endpoint',
                'attachment_aliyun_bucket',
                'attachment_aliyun_domain',
                'uploadmaxsize',
                'uploadallowext',
                'qtuploadmaxsize',
                'qtuploadallowext',
                'fileexclude',
                'ftphost',
                'ftpport',
                'ftpuppat',
                'ftpuser',
                'ftppassword',
                'ftppasv',
                'ftpssl',
                'ftptimeout',
                'watermarkenable',
                'watermarkminwidth',
                'watermarkminheight',
                'watermarkimg',
                'watermarkpct',
                'watermarkquality',
                'watermarkpos',
            ];
            $config = [];
            foreach ($fields as $i => $key) {
                $config[$key] = isset($_config[$key]) ? $_config[$key] : '';
            }
            return self::makeJsonReturn(true, [
                'config' => $config,
            ]);
        }

        return view('attach');
    }

    /**
     * 拓展配置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\concern\PDOException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function extend(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        $configFieldList = $adminConfigService->getConfigFielList()['data'];
        if ($request->isPost()) {
            // 更新
            $post_data = $request->post();
            $data = [];
            foreach ($configFieldList as $index => $item) {
                if (isset($post_data[$item['fieldname']])) {
                    $data[$item['fieldname']] = $post_data[$item['fieldname']];
                }
            }
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $config = $adminConfigService->getConfig()['data'];
            $configMap = [];
            foreach ($configFieldList as $item) {
                $configMap[$item['fieldname']] = $config[$item['fieldname']];
            }
            return self::makeJsonReturn(true, [
                'configFieldList' => $configFieldList,
                'configMap'       => $configMap
            ]);
        }

        return view('extend');
    }

    /**
     * 添加/更新拓展配置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    function editExtend(Request $request)
    {
        $configFieldService = new ConfigFieldService();
        if ($request->isPost()) {
            $data = [
                'fid'       => $request->post("fid"),
                'fieldname' => $request->post("fieldname"),
                'type'      => $request->post("type"),
                'setting'   => $request->post("setting"),
            ];
            $res = $configFieldService->addOrUpdateConfigField($data);
            return json($res);
        }

        $fid = $request->get("fid");
        $configField = null;
        if (!empty($fid)) {
            $configField = $configFieldService->getConfigField($fid)['data'];
            $optionList = $configField['setting']['option'];
            $option = [];
            foreach ($optionList as $item) {
                $option [] = $item['title'].'|'.$item['value'];
            }
            $configField['setting']['option'] = join('\n', $option);
        }
        return view('editExtend', [
            'configField' => $configField,
        ]);
    }
}