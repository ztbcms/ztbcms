<?php
/**
 * User: zhlhuang
 * Date: 2020-09-15
 */

namespace app\common\libs\upload;


use app\common\model\upload\AttachmentModel;
use think\facade\Filesystem;

class LocalDriver extends UploadDriver
{

    const DISKCONFIG = "ztbcms";

    protected $siteUrl = "";

    public function __construct($config)
    {
        $this->siteUrl = isset($config['siteurl']) ? $config['siteurl'] : '';
    }

    function upload(AttachmentModel $attachmentModel)
    {
        $file = request()->file('file');
        $url = Filesystem::disk(self::DISKCONFIG)->getConfig()->get('url');
        //兼容原CMS
        $saveName = Filesystem::disk(self::DISKCONFIG)->putFile($attachmentModel->module, $file);
        $attachmentModel->filepath = $saveName;
        $attachmentModel->fileurl = ($this->siteUrl != '/' ? $this->siteUrl : '').$url.$saveName;
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