<?php
/**
 * User: jayinton
 */

namespace app\admin\controller;

use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use think\Request;

class Config extends AdminController
{
    function index(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            $data = [
                'sitename'       => $request->get("sitename"),
                'siteurl'        => $request->get("siteurl"),
                'sitefileurl'    => $request->get("sitefileurl"),
                'siteemail'      => $request->get("siteemail"),
                'sitekeywords'   => $request->get("sitekeywords"),
                'siteinfo'       => $request->get("siteinfo"),
                'checkcode_type' => $request->get("checkcode_type"),
            ];

        }

        $config = $adminConfigService->getConfig()['data'];
        return view('index', [
            'Site' => $config
        ]);
    }

    function email()
    {
        return view('index');
    }

    function attach()
    {
        return view('attach');
    }

    function addition()
    {
        return view('addition');
    }

    function extend()
    {
        return view('extend');
    }

    function doEditExtend()
    {

    }
}