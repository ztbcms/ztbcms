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

class AttachmentGroupModel extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    protected $name = 'attachment_group';
    const TYPE_IMAGE = "image";
}