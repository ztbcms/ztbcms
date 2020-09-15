<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 11:59.
 */

namespace app\common\controller\upload;


use app\common\controller\AdminController;
use app\common\model\ConfigModel;
use think\facade\View;
use think\Request;

class Upload extends AdminController
{
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
        if ($request->post()) {
            $updataData = [];
            foreach ($request->post() as $key => $value) {
                $updataData[] = ['varname' => $key, 'value' => $value];
            }
            $configModel = new ConfigModel();
            $configModel->saveAll($updataData);
            return self::createReturn(true, '', '保存成功');
        }
        $config = ConfigModel::column('value', 'varname');
        $dirverList = [
            'Local' => '本地存储驱动',
            'Aliyun' => '阿里云OSS上传驱动【暂不支持水印】',
        ];
        return View::fetch('setting', [
            'config' => $config,
            'dirverList' => $dirverList
        ]);
    }
}