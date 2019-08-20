<?php
/**
 * User: jayinton
 * Date: 2019/3/5
 * Time: 14:55
 */

namespace Upload\Controller;


use Admin\Controller\AdminApiBaseController;
use Admin\Service\User;
use Upload\Service\WatermarkService;

/**
 * 后台上传管理接口
 * @package Upload\Controller
 */
class UploadAdminApiController extends AdminApiBaseController
{

    const isadmin = 1; //后台上传

    //模块
    const MODULE_IMAGE = 'module_upload_images';
    const MODULE_FILE = 'module_upload_files';

    /**
     * @param $module  string 文件所属模块
     * @return array
     */
    private function _upload($module)
    {
        if (IS_POST) {
            //回调函数
            $Callback = false;
            $userInfo = User::getInstance()->getInfo();
            $upuserid = $userInfo['id'];
            //取得栏目ID
            $catid = I('post.catid', 0, 'intval');
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
        $watermark_enable = I('enable', 0);
        $result = $this->_upload(self::MODULE_IMAGE);
        if (!$result['status']) {
            $this->ajaxReturn($result);
            return;
        }
        //处理水印
        //是否添加水印
        if($watermark_enable == 1){
            $watermarkService = new WatermarkService();
            $watermark_config = $watermarkService->getWatermarkConfig()['data'];
            $source_image_path = SITE_PATH . $result['data']['url'];
            $save_image_path = SITE_PATH . $result['data']['url'];
            $watermarkService->addWaterMark($source_image_path, $save_image_path, $watermark_config);
        }
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
     * 获取图像
     */
    function getGalleryList()
    {
        $page = I('page', 1);
        $limit = I('limit', 20);
        $userInfo = User::getInstance()->getInfo();
        $userid = $userInfo['id'];

        $db = M('Attachment');
        $where = [
            'module' => self::MODULE_IMAGE,
            'userid' => $userid,
            'isadmin' => 1,
        ];
        $total_items = $db->where($where)->count();
        $total_page = ceil($total_items / $limit);
        $list = $db->where($where)->page($page)->limit($limit)->order(array("uploadtime" => "DESC"))->select();

        $return_list = [];
        foreach ($list as $index => $item) {
            $return_list [] = [
                'name' => $item['filename'],
                'url' => cache('Config.sitefileurl') . $item['filepath'],
                'filepath' => $item['filepath'],
            ];
        }

        $this->ajaxReturn($this->createReturnList(true, $return_list, $page, $limit, $total_items, $total_page));
    }

    /**
     * 获取水印配置
     */
    function getWatermarkConfig(){
        $system_configs = M('Config')->where([
            'varname' => ['IN', 'watermarkenable,watermarkminwidth,watermarkminheight,watermarkimg,watermarkpct,watermarkquality,watermarkpos']
        ])->select();
        $config = [];
        foreach ($system_configs as $i => $v) {
            $config[$v['varname']] = $v['value'];
        }
        $this->ajaxReturn(self::createReturn(true, $config));
    }

    /**
     * 保存水印配置
     */
    function saveWatermarkConfig(){
        $post = I('post.');

        $fileds = ['watermarkenable','watermarkminwidth','watermarkminheight','watermarkimg','watermarkpct','watermarkquality','watermarkpos'];
        foreach ($post as $key => $value){
            if(in_array($key, $fileds)){
                M('Config')->where(['varname' => $key])->save(['value' => $value]);
            }
        }

        $this->ajaxReturn(self::createReturn(true, null, '操作完成'));
    }
}