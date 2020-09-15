<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-15
 * Time: 18:23.
 */

namespace app\common\libs\upload;


use app\common\model\upload\AttachmentModel;
use think\facade\Filesystem;

class LocalDriver
{

    const DISKCONFIG = "ztbcms";

    protected $siteurl = "";

    public function __construct($config)
    {
        $this->siteurl = isset($config['siteurl']) ? $config['siteurl'] : '';
    }

    function upload(AttachmentModel $attachmentModel)
    {
        $file = request()->file('file');
        $url = Filesystem::disk(self::DISKCONFIG)->getConfig()->get('url');
        //兼容原CMS
        $saveName = Filesystem::disk(self::DISKCONFIG)->putFile($attachmentModel->module, $file);
        $attachmentModel->filepath = $saveName;
        $attachmentModel->fileurl = ($this->siteurl != '/' ? $this->siteurl : '') . $url . $saveName;
    }
}