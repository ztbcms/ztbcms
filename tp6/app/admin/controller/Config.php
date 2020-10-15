<?php
/**
 * User: jayinton
 */

namespace app\admin\controller;

use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use think\facade\Db;
use think\Request;

class Config extends AdminController
{
    function index(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
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

        $config = $adminConfigService->getConfig()['data'];
        return view('index', [
            'Site' => $config
        ]);
    }

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

        $config = $adminConfigService->getConfig()['data'];
        return view('email', [
            'Site' => $config
        ]);
    }

    function attach(Request $request)
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

        $config = $adminConfigService->getConfig()['data'];
        return view('email', [
            'Site' => $config
        ]);
    }

    function extend(Request $request)
    {
        $adminConfigService = new AdminConfigService();

        $configFieldList = $adminConfigService->getConfigFielList()['data'];
        if ($request->isPost()) {
            foreach ($configFieldList as $index => $item) {
                $data[$item['fieldname']] = $request->post($item['fieldname']);
            }
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }
//        var_dump(json_encode($configFieldList));exit;
        $configList = Db::name('config')->where('groupid', 2)->select()->toArray();
        $configMap = [];
        foreach ($configList as $item) {
            $configMap[$item['varname']] = $item['value'];
        }
        return view('extend', [
            'configFieldList' => $configFieldList,
            'configMap'           => $configMap
        ]);
    }

    function editExtend()
    {
        return view('editExtend');
    }

    function doEditExtend()
    {

    }
}