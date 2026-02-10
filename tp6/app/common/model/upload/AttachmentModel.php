<?php

/**
 * User: zhlhuang
 * Date: 2020-09-15
 */

namespace app\common\model\upload;

use app\common\service\upload\UploadService;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * 附件
 *
 * @package app\common\model\upload
 */
class AttachmentModel extends Model
{
    use SoftDelete;

    protected $defaultSoftDelete = 0;
    protected $autoWriteTimestamp = true;

    protected $name = 'attachment';
    protected $pk = 'aid';

    const MODULE_IMAGE = "image";
    const MODULE_VIDEO = "video";
    const MODULE_FILE = "file";
    /**
     * UE富文本图片
     */
    const MODULE_UE_IMAGE = "ue_image";

    const DRIVER_ALIYUN = "Aliyun";
    const DRIVER_LOCAL = "Local";
    const DRIVER_QINIU = "Qiniu";

    const IS_IMAGES_YES = 1;
    const IS_IMAGES_NO = 0;

    const IS_PRIVATE_YES = 1;
    const IS_PRIVATE_NO = 0;

    // 上传用户类型
    const USER_TYPE_ADMIN = 'admin';

    /**
     * 获取缩略图处理器
     *
     * @param $value
     * @param $data
     *
     * @return string
     */
    public function getFilethumbAttr($value, $data)
    {
        $isPrivate = $data['is_private'] ?? self::IS_PRIVATE_NO;
        if ($isPrivate) {
            // 私有读时，缩略图也需要签名 URL
            $uploadService = new UploadService($data['driver']);
            $uploadService->setIsPrivate(true);
            $res = $uploadService->getPrivateUrl($data['filepath']);
            return $res ?: $value;
        }

        return $value;
    }

    /**
     * 获取访问链接处理
     *
     * @param $value
     * @param $data
     * @return string
     */
    public function getFileurlAttr($value, $data)
    {
        $isPrivate = $data['is_private'] ?? self::IS_PRIVATE_NO;
        if ($isPrivate == self::IS_PRIVATE_YES) {
            $uploadService = new UploadService($data['driver']);
            $uploadService->setIsPrivate(true);
            $res = $uploadService->getPrivateUrl($data['filepath']);
            return $res ?: $value;
        }

        return $value;
    }
}
