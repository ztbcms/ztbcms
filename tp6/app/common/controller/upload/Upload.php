<?php

namespace app\common\controller\upload;


use app\common\controller\AdminController;
use app\common\model\ConfigModel;
use think\facade\View;
use think\Request;

class Upload extends AdminController
{
    /**
     * 上传示例
     * @return string
     */
    function demo()
    {
        return View::fetch('demo');
    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return array|string
     */
    function setting(Request $request)
    {
        if ($request->isPost()) {
            ConfigModel::editConfigs($request->post());
            return self::createReturn(true, '', '保存成功，更新缓存后生效');
        }
        $config = ConfigModel::column('value', 'varname');
        $dirverList = [
            'Local' => '本地',
            'Aliyun' => '阿里云OSS【暂不支持水印】',
        ];
        return View::fetch('setting', [
            'config' => $config,
            'dirverList' => $dirverList
        ]);
    }
}