<?php
/**
 * User: jayinton
 * Date: 2019/3/5
 * Time: 10:58
 */

namespace Upload\Controller;


use Common\Controller\AdminBase;

/**
 * 后台上传
 * Class UploadCenterController
 * @package Upload\Controller
 */
class UploadCenterController extends AdminBase
{
    function imageUploadPanel()
    {

        $this->display('imageUploadPanelv2');
    }

    function videoUploadPanel()
    {
        $this->display();
    }

    function fileUploadPanel()
    {
        $this->display();
    }

    //编辑水印配置
    function editWatermarkConfig()
    {
        $this->display();
    }


}