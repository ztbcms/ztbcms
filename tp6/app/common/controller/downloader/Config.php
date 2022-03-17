<?php
/**
 * Author: cycle_3
 */

namespace app\common\controller\downloader;

use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use think\Request;


class Config extends AdminController
{

    /**
     * 下载中心配置
     *
     * @param Request $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index(Request $request)
    {
        $adminConfigService = AdminConfigService::getInstance();
        if ($request->isPost()) {
            // 设置
            $data = [
                'downloader_retry_switch' => $request->post("downloader_retry_switch"),
                'downloader_retry_num' => $request->post("downloader_retry_num"),
                'downloader_timeout' => $request->post("downloader_timeout"),
                'downloader_domain' => $request->post("downloader_domain"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $_config = $adminConfigService->getConfig(null, false)['data'];
            $fields = [
                'downloader_retry_switch', 'downloader_retry_num', 'downloader_timeout', 'downloader_domain'
            ];
            $config = [];
            foreach ($fields as $i => $key) {
                $config[$key] = $_config[$key] ?? '';
            }
            return self::makeJsonReturn(true, $config);
        }
        return view('index');
    }

}