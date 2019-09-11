<?php

// +----------------------------------------------------------------------
// | 附件模型
// +----------------------------------------------------------------------

namespace Attachment\Model;

use Common\Model\Model;

class AttachmentModel extends Model {

    //上传目录
    public $dateFormat = 'Y/m';
    //当前时间戳
    public $time;

    // 删除状态
    const DELETE_STATUS_YES = 1;
    const DELETE_STATUS_NO = 0;

    protected function _initialize() {
        parent::_initialize();
        $this->time = time();
    }

    /**
     *  生成上传保存目录，不存在尝试创建
     *
     * @return string 成功返回网站路径，失败反驳false
     */
    public function getFilePath($module, $dateFormat = '', $time = 0) {
        $filePath = C("UPLOADFILEPATH") . strtolower(trim($module)) . '/' . date($dateFormat ? $dateFormat : $this->dateFormat,
                $time ? $time : $this->time) . '/';
        //检测目录是否存在，不存在创建
        if (!is_dir($filePath)) {
            if (!mkdir($filePath, 0777, true)) {
                $this->error = '目录创建失败！';

                return false;
            }
        }

        return $filePath;
    }

    /**
     * 记录上传的附件信息入库
     *
     * @param array  $info    文件信息，数组
     *                        array(
     *                        'name' => '6.jpg',//上传文件名
     *                        'type' => 'application/octet-stream',//文件类型
     *                        'size' => 112102,//文件大小
     *                        'key' => 0,
     *                        'extension' => 'jpg',//上传文件后缀
     *                        'savepath' => '/home/wwwroot/ztbcms.com/d/file/content/2012/07/',//文件保存完整路径
     *                        'savename' => '5002ba343fc9d.jpg',//保存文件名
     *                        'hash' => '77b5118c1722da672b0ddce3c4388e64',
     *                        )
     * @param string $module  模块
     * @param int    $catid   栏目id
     * @param int    $isthumb 是否缩略图
     * @param int    $isadmin 是否后台
     * @param int    $userid  用户id
     * @param int    $time    时间戳
     * @return boolean|int
     */
    public function fileInfoAdd(
        array $info,
        $module = 'contents',
        $catid = 0,
        $isthumb = 0,
        $isadmin = 0,
        $userid = 0,
        $time = 0
    ) {
        if (empty($info) || !is_array($info)) {
            return false;
        }
        //后缀强制小写
        $info['extension'] = strtolower($info['extension']);
        //文件保存物理地址
        $filePath = $info['savepath'] . $info['savename'];
        if (empty($filePath)) {
            return false;
        }
        //文件url地址，不包含附件目录 例如 content/2012/07/5002ba343fc9d.jpg
        $fileUrlPath = str_replace(C("UPLOADFILEPATH"), '', $filePath);
        //保存数据
        $data = array(
            //模块名称
            "module" => strtolower($module),
            //栏目ID
            "catid" => (int)$catid,
            //附件名称
            "filename" => $info['name'],
            //附件路径
            "filepath" => $fileUrlPath,
            //附件大小
            "filesize" => $info['size'],
            //附件扩展名
            "fileext" => $info['extension'],
            //是否为图片附件
            "isimage" => in_array($info['extension'], array("jpg", "png", "jpeg", "gif")) ? 1 : 0,
            //是否为缩略图
            "isthumb" => $isthumb,
            //上传用户ID
            "userid" => (int)$userid,
            //是否后台上传
            'isadmin' => $isadmin ? 1 : 0,
            //上传时间
            "uploadtime" => $time ? $time : time(),
            //上传IP
            "uploadip" => get_client_ip(),
            //附件状态
            "status" => 0,
            //附件hash
            "authcode" => $info['hash'],
        );

        return $this->add($data);
    }

}
