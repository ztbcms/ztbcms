<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 14:49.
 */

namespace app\common\model\upload;


use think\Model;
use think\model\concern\SoftDelete;

class AttachmentModel extends Model
{
    use SoftDelete;
    protected $defaultSoftDelete = 0;
    protected $deleteTime = 'delete_status';
    protected $name = 'attachment';
    protected $pk = 'aid';

    const MODULE_IMAGE = "module_upload_images";
    const MODULE_VIDEO = "module_upload_video";
    const MODULE_FILE = "module_upload_files";

    const IS_IMAGES_YES = 1;
    const IS_IMAGES_NO = 0;
}