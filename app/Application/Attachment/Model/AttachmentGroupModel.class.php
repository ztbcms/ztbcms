<?php
/**
 * Created by PhpStorm.
 * User: FHYI
 * Date: 2020/5/20
 * Time: 10:30
 */

namespace Attachment\Model;

use Common\Model\Model;

/**
 * 附件分组模型
 * Class AttachmentGroupModel
 * @package Attachment\Model
 */
class AttachmentGroupModel extends Model
{
    protected $tableName = 'attachment_group';

    //是否删除 0否1是
    const IS_DELETE_NO = 0;
    const IS_DELETE_YES = 1;
    // 图片分类
    const GROUP_TYPE_IMAGE = 'image';
}