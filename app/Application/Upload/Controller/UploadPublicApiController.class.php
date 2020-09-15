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
    const MODULE_VIDEO = 'module_upload_video';

    const FILE_THUMB_ARRAY = [
        'ppt' => '/statics/admin/upload/ppt.png',
        'pptx' => '/statics/admin/upload/ppt.png',
        'doc' => '/statics/admin/upload/doc.png',
        'docx' => '/statics/admin/upload/doc.png',
        'xls' => '/statics/admin/upload/xls.png',
        'xlsx' => '/statics/admin/upload/xls.png',
        'pdf' => '/statics/admin/upload/pdf.png',
        'file' => '/statics/admin/upload/file.png',
    ];

    protected function _initialize()
    {
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
    private function _upload($module, $filethumb = "")
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
                $extension = $info[0]['extension'];
                $aid = $info[0]['aid'];
                if (!$filethumb) {
                    if (!empty(self::FILE_THUMB_ARRAY[$extension])) {
                        $filethumb = self::FILE_THUMB_ARRAY[$extension];
                        M('Attachment')->where(['aid' => $aid])->save([
                            'filethumb' => $filethumb
                        ]);
                    }
                }
                $data = [
                    'filethumb' => $filethumb, //文件缩略图，图片可以为空
                    'name' => $info[0]['name'],//名称
                    'type' => $info[0]['type'],//类型 eg:image/png
                    'size' => $info[0]['size'],// 容量,单位 byte eg: 1KB=1024byte
                    'extension' => $info[0]['extension'],// eg:png
                    'savepath' => $info[0]['savepath'],// eg:"/root/project/ztbcms/d/file/module_upload_images/2019/09/"
                    'savename' => $info[0]['savename'],// eg:image/png
                    'hash' => $info[0]['hash'],// hash
                    'aid' => $info[0]['aid'],// 附件ID
                    'url' => $info[0]['url'],//上传文件路径, e.g: http://ztbcms.biz:8888/d/file/module_upload_images/2019/09/5d89d09186329.jpeg 、 或 /d/file/module_upload_images/2019/09/5d89d09186329.jpeg
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
        $upload_file_info = $result['data'];
        //是否添加水印
        if ($watermark_enable == WatermarkService::ENABLE_YES) {
            $source_image_path = $upload_file_info['url'];
            $save_image_path = $upload_file_info['savepath'] . $upload_file_info['savename'];
            $watermarkService->addWaterMark($source_image_path, $save_image_path, $watermark_config);
        }
        //调整链接
        $upload_file_info['url'] = formatResourceUrl($upload_file_info['url']);
        $return = [
            'name' => $upload_file_info['name'],//名称
            'size' => $upload_file_info['size'],// 容量,单位 byte eg: 1KB=1024byte
            'aid' => $upload_file_info['aid'],// 附件ID
            'url' => $upload_file_info['url'],
        ];
        $this->ajaxReturn(self::createReturn(true, $return));

    }

    /**
     * 上传文件
     */
    function uploadFile()
    {
        $result = $this->_upload(self::MODULE_FILE);
        $upload_file_info = $result['data'];
        $return = [
            'name' => $upload_file_info['name'],//名称
            'filethumb' => $upload_file_info['filethumb'],//名称
            'size' => $upload_file_info['size'],// 容量,单位 byte eg: 1KB=1024byte
            'aid' => $upload_file_info['aid'],// 附件ID
            'url' => $upload_file_info['url'],
        ];
        $this->ajaxReturn(self::createReturn(true, $return));
    }

    /**
     * 上传视频
     */
    function uploadVideo()
    {
        $filethumb = "/statics/admin/upload/video.png";
        $result = $this->_upload(self::MODULE_VIDEO, $filethumb);
        if (!$result['status']) {
            $this->ajaxReturn($result);
            return;
        }
        $attachmentDriverConfig = cache("Config.attachment_driver");
        if ($attachmentDriverConfig && $attachmentDriverConfig == 'Aliyun') {
            //如果是aliyun上传机制，修改视频封面缩略图
            $data = $result['data'];
            $filethumb = $data['url'] . '?x-oss-process=video/snapshot,t_500,f_png';
            M('Attachment')->where(['aid' => $data['aid']])->save([
                'filethumb' => $filethumb
            ]);
            $result['data']['filethumb'] = $filethumb;
        }
        $upload_file_info = $result['data'];
        $return = [
            'name' => $upload_file_info['name'],//名称
            'filethumb' => $upload_file_info['filethumb'],//名称
            'size' => $upload_file_info['size'],// 容量,单位 byte eg: 1KB=1024byte
            'aid' => $upload_file_info['aid'],// 附件ID
            'url' => $upload_file_info['url'],
        ];
        $this->ajaxReturn(self::createReturn(true, $return));
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