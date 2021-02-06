<?php
/**
 * User: zhlhuang
 * Date: 2020-09-15
 */

namespace app\common\libs\upload;


use app\common\model\upload\AttachmentModel;
use think\facade\Filesystem;
use think\facade\Request;

/**
 * 本地上传驱动
 *
 * @package app\common\libs\upload
 */
class LocalDriver extends UploadDriver
{

    const DISKCONFIG = "ztbcms";

    protected $file_domain = "";

    public function __construct($config)
    {
        // 默认当前请求的域名
        $this->file_domain = isset($config['attachment_local_domain']) && !empty($config['attachment_local_domain']) ? $config['attachment_local_domain'] : Request::domain();
    }

    function upload(AttachmentModel $attachmentModel)
    {
        $file = request()->file('file');
        $url = Filesystem::disk(self::DISKCONFIG)->getConfig()->get('url');
        $saveName = Filesystem::disk(self::DISKCONFIG)->putFile($attachmentModel->module, $file);
        $attachmentModel->filepath = $saveName;
        $attachmentModel->fileurl = $this->file_domain.$url.$saveName;
    }

    public function getPrivateUrl($url)
    {
        return $url;
    }

    public function getPrivateThumbUrl($url)
    {
        return $url;
    }
}