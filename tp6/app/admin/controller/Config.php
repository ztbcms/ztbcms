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
     * @param Request $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            $data = [
                'sitename' => $request->post("sitename"),
                'siteurl' => $request->post("siteurl"),
                'sitefileurl' => $request->post("sitefileurl"),
                'siteemail' => $request->post("siteemail"),
                'sitekeywords' => $request->post("sitekeywords"),
                'siteinfo' => $request->post("siteinfo"),
                'checkcode_type' => $request->post("checkcode_type"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        $config = $adminConfigService->getConfig()['data'];
        return view('index', [
            'Site' => $config
        ]);
    }

    /**
     * 邮箱设置
     * @param Request $request
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
                'mail_type' => $request->post("mail_type"),
                'mail_server' => $request->post("mail_server"),
                'mail_port' => $request->post("mail_port"),
                'mail_from' => $request->post("mail_from"),
                'mail_fname' => $request->post("mail_fname"),
                'mail_auth' => $request->post("mail_auth"),
                'mail_user' => $request->post("mail_user"),
                'mail_password' => $request->post("mail_password"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        $config = $adminConfigService->getConfig()['data'];
        return view('email', [
            'Site' => $config
        ]);
    }

    /**
     * 附件设置
     * @param Request $request
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
                'mail_type' => $request->post("mail_type"),
                'mail_server' => $request->post("mail_server"),
                'mail_port' => $request->post("mail_port"),
                'mail_from' => $request->post("mail_from"),
                'mail_fname' => $request->post("mail_fname"),
                'mail_auth' => $request->post("mail_auth"),
                'mail_user' => $request->post("mail_user"),
                'mail_password' => $request->post("mail_password"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        $config = $adminConfigService->getConfig()['data'];
        return view('email', [
            'Site' => $config
        ]);
    }

    /**
     * 拓展配置
     * @param Request $request
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
        $config = $adminConfigService->getConfig()['data'];
        $configMap = [];
        foreach ($configFieldList as $item) {
            $configMap[$item['fieldname']] = $config[$item['fieldname']];
        }
        return view('extend', [
            'configFieldList' => $configFieldList,
            'configMap' => $configMap
        ]);
    }

    /**
     * 添加/更新拓展配置
     * @param Request $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    function editExtend(Request $request)
    {
        $configFieldService = new ConfigFieldService();
        if ($request->isPost()) {
            $data = [
                'fid' => $request->post("fid"),
                'fieldname' => $request->post("fieldname"),
                'type' => $request->post("type"),
                'setting' => $request->post("setting"),
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
                $option [] = $item['title'] . '|' . $item['value'];
            }
            $configField['setting']['option'] = join('\n', $option);
        }
        return view('editExtend', [
            'configField' => $configField,
        ]);
    }
}