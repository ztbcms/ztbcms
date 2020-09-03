<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use OSS\Core\OssException;
use OSS\OssClient;

/**
 * 阿里云OSS文件上传类
 * @category   ORG
 * @package  ORG
 * @subpackage  Net
 * @author    liu21st <liu21st@gmail.com>
 */
class AliyunUploadFile
{
    private $config = [
        'savePath' => '',
        'subType' => 'hash',
        'subDir' => '',
        'dateFormat' => 'Ymd',
        'hashLevel' => 1,
        'autoCheck' => true,
        'saveRule' => 'uniqid'
    ];
    // 错误信息
    private $error = '';
    // 上传成功的文件信息
    private $uploadFileInfo;

    /**
     * 架构函数
     * @access public
     * @param array $config 上传参数
     */
    public function __construct($config = array())
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 上传一个文件
     * @param $file
     * @return bool
     */
    private function save($file)
    {
        $filename = $file['savepath'] . $file['savename'];
        $accessKeyId = $this->config['attachment_aliyun_key_id'];
        $accessKeySecret = $this->config['attachment_aliyun_key_secret'];
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = $this->config['attachment_aliyun_endpoint'];
        // 设置存储空间名称。
        $bucket = $this->config['attachment_aliyun_bucket'];
        // 设置文件名称。
        $object = $filename;
        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt。
        $filePath = $file['tmp_name'];

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->uploadFile($bucket, $object, $filePath);
            if (!empty($res['oss-request-url'])) {
                return true;
            } else {
                $this->error = "上传oss失败";
                return false;
            }
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 上传所有文件
     *
     * @param bool $Callback
     * @return bool
     */
    public function upload($Callback = false)
    {
        $savePath = $this->config['savePath'];

        $fileInfo = array();
        $isUpload = false;

        // 获取上传的文件信息
        // 对$_FILES数组信息处理
        $files = $this->dealFiles($_FILES);
        foreach ($files as $key => $file) {
            //过滤无效的上传
            if (!empty($file['name'])) {
                //登记上传文件的扩展信息
                if (!isset($file['key']))
                    $file['key'] = $key;
                $file['extension'] = $this->getExt($file['name']);
                $file['savepath'] = $savePath;
                $file['savename'] = $this->getSaveName($file);
                // 自动检查附件
                if ($this->config['autoCheck']) {
                    if (!$this->check($file))
                        return false;
                }
                //保存上传文件
                if (!$this->save($file))
                    return false;
                $file['url'] = $this->config['attachment_aliyun_domain'] . '/' . $file['savepath'] . $file['savename'];
                $file['hash'] = md5($file['url']);
                //上传成功后保存文件信息，供其他地方调用
                unset($file['tmp_name'], $file['error']);
                $fileInfo[] = $file;
                $isUpload = true;
            }
        }
        if ($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            //回调
            if ($Callback) {
                call_user_func_array($Callback[0], array($this, $this->uploadFileInfo, $Callback[1]));
            }
            return true;
        } else {
            $this->error = '没有选择上传文件';
            return false;
        }
    }


    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files 上传的文件变量
     * @return array
     */
    private function dealFiles($files)
    {
        $fileArray = array();
        $n = 0;
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key) {
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            } else {
                $fileArray[$key] = $file;
            }
        }
        return $fileArray;
    }

    /**
     * 获取错误代码信息
     * @access public
     * @param string $errorNo 错误号码
     * @return void
     */
    protected function error($errorNo)
    {
        switch ($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return;
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @access private
     * @param string $filename 数据
     * @return string
     */
    public function getSaveName($filename)
    {
        $rule = $this->config['saveRule'];
        if (empty($rule)) {//没有定义命名规则，则保持文件名不变
            $saveName = $filename['name'];
        } else {
            if (function_exists($rule)) {
                //使用函数生成一个唯一文件标识号
                $saveName = $rule() . "." . $filename['extension'];
            } else {
                //使用给定的文件名作为标识号
                $saveName = $rule . "." . $filename['extension'];
            }
        }
        //强制转换为小写
        $saveName = strtolower($saveName);

        if (!empty($this->config['autoSub'])) {
            // 使用子目录保存文件
            $filename['savename'] = $saveName;
            $saveName = $this->getSubName($filename) . $saveName;
        }
        return $saveName;
    }

    /**
     * 获取子目录的名称
     * @access private
     * @param array $file 上传的文件信息
     * @return string
     */
    private function getSubName($file)
    {
        switch ($this->config['subType']) {
            case 'custom':
                $dir = $this->config['subDir'];
                break;
            case 'date':
                $dir = date($this->config['dateFormat'], time()) . ' / ';
                break;
            case 'hash':
            default:
                $name = md5($file['savename']);
                $dir = '';
                for ($i = 0; $i < $this->config['hashLevel']; $i++) {
                    $dir .= $name{$i} . ' / ';
                }
                break;
        }

        if (!is_dir($file['savepath'] . $dir)) {
            mkdir($file['savepath'] . $dir, 0777, true);
        }

        return $dir;
    }

    /**
     * 检查上传的文件
     * @access private
     * @param array $file 文件信息
     * @return boolean
     */
    private function check($file)
    {
        if ($file['error'] !== 0) {
            //文件上传失败
            //捕获错误代码
            $this->error($file['error']);
            return false;
        }
        //文件上传成功，进行自定义规则检查
        //检查文件大小
        if (!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        //检查文件Mime类型
        if (!$this->checkType($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
        //检查文件类型
        if (!$this->checkExt($file['extension'])) {
            $this->error = '上传文件类型不允许';
            return false;
        }

        //检查是否合法上传
        if (!$this->checkUpload($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }
        return true;
    }

    // 自动转换字符集 支持数组转换
    private function autoCharset($fContents, $from = 'gbk', $to = 'utf - 8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf - 8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf - 8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }

    /**
     * 检查上传的文件类型是否合法
     * @access private
     * @param string $type 数据
     * @return boolean
     */
    private function checkType($type)
    {
        if (!empty($this->config['allowTypes']))
            return in_array(strtolower($type), $this->config['allowTypes']);
        return true;
    }

    /**
     * 检查上传的文件后缀是否合法
     * @access private
     * @param string $ext 后缀名
     * @return boolean
     */
    private function checkExt($ext)
    {
        //强制危险后缀过滤
        if (in_array(strtolower($ext), array("php", "php4", "asp", "phtml", "php3", "exe", "dll", "cer", "asa", "aspx", "asax", "cgi", "fcgi", "pl"))) {
            return false;
        }

        if (!empty($this->config['allowExts'])) {
            return in_array(strtolower($ext), $this->config['allowExts'], true);
        }

        return true;
    }

    /**
     * 检查文件大小是否合法
     * @access private
     * @param integer $size 数据
     * @return boolean
     */
    private function checkSize($size)
    {
        return !($size > $this->config['maxSize']) || (-1 == $this->config['maxSize']);
    }

    /**
     * 检查文件是否非法提交
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function checkUpload($filename)
    {
        return is_uploaded_file($filename);
    }

    /**
     * 取得上传文件的后缀
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function getExt($filename)
    {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    /**
     * 取得上传文件的信息
     * @access public
     * @return array
     */
    public function getUploadFileInfo()
    {
        return $this->uploadFileInfo;
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->error;
    }

}
