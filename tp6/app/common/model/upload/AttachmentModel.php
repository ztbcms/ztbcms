<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 14:49.
 */

namespace app\common\model\upload;

use app\common\service\upload\UploadService;
use think\Model;
use think\model\concern\SoftDelete;

class AttachmentModel extends Model
{
    use SoftDelete;
    protected $defaultSoftDelete = 0;
    protected $deleteTime = 'delete_status';
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

    const IS_IMAGES_YES = 1;
    const IS_IMAGES_NO = 0;

    const IS_ADMIN_YES = 1;
    const IS_ADMIN_NO = 0;

    /**
     * 获取缩略图处理器
     *
     * @param $value
     * @param $data
     * @return bool
     */
    public function getFilethumbAttr($value, $data)
    {
        if (isset($data['driver']) && $data['driver'] == self::DRIVER_ALIYUN && $data['module'] == self::MODULE_VIDEO) {
            $uploadService = new UploadService();
            $res = $uploadService->getPrivateThumbUrl($data['filepath'], self::DRIVER_ALIYUN);
            if ($res) {
                return $res;
            } else {
                return $value;
            }
        }
        return $value;
    }

    /**
     * 获取访问链接处理
     *
     * @param $value
     * @param $data
     * @return bool
     */
    public function getFileurlAttr($value, $data)
    {
        if (isset($data['driver']) && $data['driver'] == self::DRIVER_ALIYUN && $data['module'] == self::MODULE_IMAGE) {
            $uploadService = new UploadService();
            $res = $uploadService->getPrivateUrl($data['filepath'], self::DRIVER_ALIYUN);
            if ($res) {
                return $res;
            } else {
                return $value;
            }
        }
        return $value;
    }
}