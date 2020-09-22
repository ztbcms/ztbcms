<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020/9/22
 * Time: 20:41.
 */

namespace app\common\libs\upload;


use app\common\model\upload\AttachmentModel;

abstract class UploadDriver
{
    protected $isPrivate;

    /**
     * @return mixed
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @param  mixed  $isPrivate
     */
    public function setIsPrivate($isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }

    abstract function upload(AttachmentModel $attachmentModel);

    abstract function getPrivateUrl($filePath);

    abstract function getPrivateThumbUrl($filePath);
}