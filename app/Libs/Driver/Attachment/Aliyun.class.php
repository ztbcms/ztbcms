<?php

// +----------------------------------------------------------------------
// | 本地存储方案
// +----------------------------------------------------------------------

namespace Libs\Driver\Attachment;

use Libs\Service\Attachment;

class Aliyun extends Attachment
{

    /**
     * 架构函数
     * @param array $options 配置参数
     * @access public
     */
    function __construct($options = array())
    {
        //网站配置
        $this->config = cache("Config");
        $options = array_merge(array(
            //上传用户ID
            'userid' => 0,
            //上传用户组
            'groupid' => 8,
            //是否后台
            'isadmin' => 0,
            //上传栏目
            'catid' => 0,
            //上传模块
            'module' => 'content',
            //是否添加水印
            'watermarkenable' => $this->config['watermarkenable'],
            //生成缩略图
            'thumb' => false,
            //上传时间戳
            'time' => time(),
            //上传目录创建规则
            'dateFormat' => 'Y/m',
            //分组id
            'group_id' => '0',
        ), $options);
        $this->options = $options;
        //允许上传的附件大小
        if (empty($this->options['uploadmaxsize'])) {
            $this->options['uploadmaxsize'] = $this->options['isadmin'] ? (int)$this->config['uploadmaxsize'] * 1024 : (int)$this->config['qtuploadmaxsize'] * 1024;
        }
        //允许上传的附件类型
        if (empty($this->options['uploadallowext'])) {
            $this->options['uploadallowext'] = $this->options['isadmin'] ? explode("|", $this->config['uploadallowext']) : explode("|", $this->config['qtuploadallowext']);
        }
        $this->handler = new \AliyunUploadFile(array_merge($this->config, [
            'maxSize' => $this->options['uploadmaxsize'],
            'allowExts' => $this->options['uploadallowext'],
            //设置上传目录路径【阿里云OSS上传，savePath是本地路径】
            'savePath' => $this->options['module'] . '/' . (date($this->options['dateFormat'], $this->options['time'])) . '/'
        ]));
    }

    /**
     * 上传全部文件
     * @param boolean $Callback 上传回调，数组
     * @return boolean|array
     */
    public function upload($Callback = false)
    {
        if ($this->handler->upload($Callback)) {
            //获取上传后的文件信息
            $info = $this->handler->getUploadFileInfo();
            //写入附件数据库信息
            foreach ($info as $i => $value) {
                $aid = D('Attachment/Attachment')->fileInfoAdd($value, $this->options['module'], $this->options['catid'], $this->options['thumb'], $this->options['isadmin'], $this->options['userid'], $this->options['time'], $this->options['group_id'], $this->options['filethumb']);
                if ($aid) {
                    $info[$i]['aid'] = $aid;
                    //附件完整访问地址，OSS上传savePath包含了域名
                    $info[$i]['url'] = $value['url'];
                } else {
                    //入库信息写入失败，删除上传好的文件！
                    try {
                        unlink($info[$i]['savepath'] . $info[$i]['savename']);
                    } catch (Exception $exc) {

                    }
                    unset($info[$i]);
                }
            }
            return $info;
        } else {
            $this->error = $this->handler->getErrorMsg();
            return false;
        }
    }

    /**
     * 删除文件
     * @param string $file
     * @return bool|void
     */
    public function delFile($file)
    {

    }

    /**
     * 远程保存
     * @param string $value 传入下载内容
     * @param boolean|null $watermark 是否加入水印
     * @param string $ext 下载扩展名
     * @return string
     */
    public function download($value, $watermark = null, $ext = 'gif|jpg|jpeg|bmp|png')
    {
    }

    /**
     * 获取上传错误信息
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->error;
    }

}
