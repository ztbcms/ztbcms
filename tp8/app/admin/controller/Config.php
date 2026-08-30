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
            $_config = $adminConfigService->getConfig(null, false)['data'];
            $fields = [
                'sitename', 'siteurl', 'siteemail', 'sitekeywords', 'siteinfo', 'checkcode_type'
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
     * 拓展配置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
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
            $config = $adminConfigService->getConfig(null, false)['data'];
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

    /**
     * 删除拓展字段
     * @param  Request  $request
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    function doDeleteExtendField(Request $request){
        $configFieldService = new ConfigFieldService();
        $fid = $request->post("fid");
        $res = $configFieldService->deleteConfigField($fid);
        return json($res);
    }
}