<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/21
 * Time: 15:59
 */

namespace Upload\Controller;
use Common\Controller\AdminBase;

/**
 * 上传图片裁剪
 * Class UploadCropImageController
 * @package Upload\Controller
 */
class UploadCropImageController extends AdminBase
{
    function cropImage(){
        $this->display();
    }
}