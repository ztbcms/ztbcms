<?php
/**
 * User: jayinton
 * Date: 2019/3/5
 * Time: 10:58
 */

namespace Upload\Controller;


use Common\Controller\AdminBase;

class UploadCenterController extends AdminBase
{
    function imageUploadPanel(){
        $this->display();
    }

    function fileUploadPanel(){
        $this->display();
    }

    //编辑水印配置
    function editWatermarkConfig(){
        $this->display();
    }


}