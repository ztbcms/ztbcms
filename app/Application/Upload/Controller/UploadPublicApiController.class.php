<?php
/**
 * User: jayinton
 * Date: 2019/3/5
 * Time: 14:55
 */

namespace Upload\Controller;


use Common\Controller\Base;
use Upload\Service\WatermarkService;

/**
 * 上传开放接口
 * Class UploadPublicApiController
 * @package Upload\Controller
 */
class UploadPublicApiController extends Base
{

    const isadmin = 0; //后台上传 0否1是

    //模块
    const MODULE_IMAGE = 'module_upload_images';
    const MODULE_FILE = 'module_upload_files';

    protected function _initialize() {
        //支持跨域
        //http 预检响应
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: *');
            header('Access-Control-Allow-Headers: *');
            header('Access-Control-Max-Age: 86400');
            exit();
        }
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        parent::_initialize();
    }

    /**
     * @param $module  string 文件所属模块
     * @return array
     */
    private function _upload($module)
    {
        if (IS_POST) {
            //回调函数
            $Callback = false;
            $upuserid = 0; //用户ID，请根据实际情况获取
            //取得栏目ID
            $catid = I('post.catid', 0, 'intval');//根据实际获取
            //获取附件服务
            $Attachment = service("Attachment", array('module' => $module, 'catid' => $catid, 'isadmin' => self::isadmin, 'userid' => $upuserid));

            //开始上传
            $info = $Attachment->upload($Callback);
            if ($info) {
                $data = [
                    'url' => $info[0]['url'],//上传文件路径
                    'name' => $info[0]['name'],//名称
                ];
                return self::createReturn(true, $data, '上传成功');
            } else {
                //上传失败，返回错误
                $msg = $Attachment->getErrorMsg();
                $msg = empty($msg) ? '上传失败' : $msg;
                return self::createReturn(false, null, $msg);
            }
        } else {
            return self::createReturn(false, null, '上传失败');
        }
    }

    /**
     * 上传图片
     */
    function uploadImage()
    {
        $result = $this->_upload(self::MODULE_IMAGE);
        if (!$result['status']) {
            $this->ajaxReturn($result);
            return;
        }
        //处理水印
        $watermarkService = new WatermarkService();
        $watermark_config = $watermarkService->getWatermarkConfig()['data'];
        $watermark_enable = $watermark_config['enable'];
        //是否添加水印
        if ($watermark_enable == 1) {
            $source_image_path = SITE_PATH . $result['data']['url'];
            $save_image_path = SITE_PATH . $result['data']['url'];
            $watermarkService->addWaterMark($source_image_path, $save_image_path, $watermark_config);
        }
        //调整链接
        $result['data']['url'] = urlDomain(get_url()) . $result['data']['url'];
        $this->ajaxReturn($result);

    }

    /**
     * 上传文件
     */
    function uploadFile()
    {
        $result = $this->_upload(self::MODULE_FILE);
        $this->ajaxReturn($result);
    }


    /**
     * 获取水印配置
     */
    function getWatermarkConfig()
    {
        $system_configs = M('Config')->where([
            'varname' => ['IN', 'watermarkenable,watermarkminwidth,watermarkminheight,watermarkimg,watermarkpct,watermarkquality,watermarkpos']
        ])->select();
        $config = [];
        foreach ($system_configs as $i => $v) {
            $config[$v['varname']] = $v['value'];
        }
        $this->ajaxReturn(self::createReturn(true, $config));
    }

}