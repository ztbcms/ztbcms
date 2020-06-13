<?php

// +----------------------------------------------------------------------
// | 本地存储方案
// +----------------------------------------------------------------------

namespace Libs\Driver\Attachment;

use Libs\Service\Attachment;

class Local extends Attachment {

	/**
	 * 架构函数
	 * @param array $options 配置参数
	 * @access public
	 */
	function __construct($options = array()) {
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
		//附件访问地址
		$this->options['sitefileurl'] = $this->config['sitefileurl'];
		//附件存放路径
		$this->options['uploadfilepath'] = C('UPLOADFILEPATH');
		//允许上传的附件大小
		if (empty($this->options['uploadmaxsize'])) {
			$this->options['uploadmaxsize'] = $this->options['isadmin'] ? (int) $this->config['uploadmaxsize'] * 1024 : (int) $this->config['qtuploadmaxsize'] * 1024;
		}
		//允许上传的附件类型
		if (empty($this->options['uploadallowext'])) {
			$this->options['uploadallowext'] = $this->options['isadmin'] ? explode("|", $this->config['uploadallowext']) : explode("|", $this->config['qtuploadallowext']);
		}
		//上传目录
		$this->options['savePath'] = D('Attachment/Attachment')->getFilePath($this->options['module'], $this->options['dateFormat'], $this->options['time']);
		//如果生成缩略图是否移除原图
		$this->options['thumbRemoveOrigin'] = false;

		$this->handler = new \UploadFile();
		//设置上传类型
		$this->handler->allowExts = $this->options['uploadallowext'];
		//设置上传大小
		$this->handler->maxSize = $this->options['uploadmaxsize'];
		//设置本次上传目录，不存在时生成
		$this->handler->savePath = $this->options['savePath'];
	}

	/**
	 * 上传全部文件
	 * @param boolean $Callback 上传回调，数组
	 * @return boolean|array
	 */
	public function upload($Callback = false) {
		//是否生成缩略图
		if ($this->options['thumb']) {
			if ($this->options['thumbMaxWidth'] && $this->options['thumbMaxHeight']) {
				//开启生成缩略图
				$this->handler->thumb = true;
				//如果生成缩图，且缩图扩展名为空，不允许设置删除原图
				if ($this->handler->thumb && empty($this->handler->thumbPrefix)) {
					$this->handler->thumbRemoveOrigin = false;
				} else {
					//是否移除原图
					$this->handler->thumbRemoveOrigin = $this->options['thumbRemoveOrigin'] ? true : false;
				}
				//设置缩略图最大宽度
				$this->handler->thumbMaxWidth = $this->options['thumbMaxWidth'];
				//设置缩略图最大高度
				$this->handler->thumbMaxHeight = $this->options['thumbMaxHeight'];
			}
		}
		if ($this->handler->upload($Callback)) {
			//获取上传后的文件信息
			$info = $this->handler->getUploadFileInfo();
			//写入附件数据库信息
			foreach ($info as $i => $value) {
				//如果需要生成缩图，但也要删除原图时，文件名换成生成后的缩图文件名
				if ($this->handler->thumb && $this->handler->thumbRemoveOrigin) {
					$info[$i]['savename'] = $value['savename'] = $this->handler->thumbPrefix . $value['savename'];
				}
				$aid = D('Attachment/Attachment')->fileInfoAdd($value, $this->options['module'], $this->options['catid'], $this->options['thumb'], $this->options['isadmin'], $this->options['userid'], $this->options['time'], $this->options['group_id']);
				if ($aid) {
					$filePath = $value['savepath'] . $value['savename'];
					$info[$i]['aid'] = $aid;
					//附件完整访问地址
					$info[$i]['url'] = $this->options['sitefileurl'] . str_replace(array($this->options['uploadfilepath'], '//', '\\'), array('', '/', '\/'), $filePath);
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
	 * @param string|int $file 如果为数字，表示根据aid删除，其他为文件路径
	 * @return boolean
	 */
	public function delFile($file) {
		//是否为数字
		if (is_int($file)) {
			$info = D('Attachment')->where(array("aid" => $file))->find();
			if ($info) {
				//附件地址
				$filepath = $this->options['uploadfilepath'] . $info['filepath'];
				if (!D('Attachment/Attachment')->where(array("aid" => $file))->delete()) {
					$this->error = '无法删除数据库记录的附件信息！';
					return false;
				}
				try {
					return unlink($filepath);
				} catch (\Exception $exc) {
					$this->error = '文件[' . $filepath . ']删除失败！';
					return false;
				}
			}
			return true;
		} else {
			//传入的是附件路径
			//取得附件存放目录
			//去除网站的安装路径 ，例如地址 /d/file/contents/2012/07/5002ba343fc9d.jpg
			$uploadfilepath = str_replace(SITE_PATH, '', $this->options['uploadfilepath']);
			$newFile = str_replace(array(SITE_PATH, $uploadfilepath), '', $file);
			//如果是远程附件 http://file.ztbcms.com/d/file/album/2013/08/thumb_51ff5fa9155cc.jpg
			$sitefileurl = parse_url($this->config['sitefileurl']);
			if ($sitefileurl['host']) {
				$newFile = str_replace($sitefileurl['scheme'] . "://" . $sitefileurl['host'], '', $newFile);
			}
			//附件路径MD5值
			$authcode = md5($newFile);
			$info = D('Attachment/Attachment')->where(array("authcode" => $authcode))->find();
			if ($info) {
				if (D('Attachment/Attachment')->where(array("authcode" => $authcode))->delete()) {
					try {
						return unlink($this->options['uploadfilepath'] . $newFile);
					} catch (\Exception $exc) {
						$this->error = '文件[' . $this->options['uploadfilepath'] . $newFile . ']删除失败！';
						return false;
					}
				} else {
					$this->error = '无法删除数据库记录的附件信息！';
					return false;
				}
			} else {
				//附件表没有记录相应信息，也进行删除操作
				try {
					if (strpos($newFile, 'http://')) {
						return false;
					}
					return unlink($this->options['uploadfilepath'] . $newFile);
				} catch (\Exception $exc) {
					$this->error = '文件[' . $this->options['uploadfilepath'] . $newFile . ']删除失败！';
					return false;
				}
			}
		}
	}

	/**
	 * 远程保存
	 * @param string $value 传入下载内容
	 * @param boolean|null $watermark 是否加入水印
	 * @param string $ext 下载扩展名
     * @return string
	 */
	public function download($value, $watermark = null, $ext = 'gif|jpg|jpeg|bmp|png') {
		//检查是否有开启CURL
		if (!function_exists('curl_init')) {
			return $value;
		}
		//水印开关
		if (is_null($watermark)) {
			if ($this->config['watermarkenable']) {
				$watermark = true;
			}
		}
		$curl = curl_init();
		//正则匹配是否有符合数据
		if (!preg_match_all("/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $value, $matches)) {
			return $value;
		}
		$remotefileurls = array();
		//过滤域名
		$NoDomain = explode("|", $this->config['fileexclude']);
		//当前程序所在域名地址
		$NoDomain[] = urlDomain(get_url());
		//附件地址
		$upload_url = urlDomain($this->options['sitefileurl']);
		foreach ($matches[3] as $matche) {
			//过滤远程地址
			if (strpos($matche, '://') === false) {
				continue;
			}
			//过滤后台设置的域名，和本站域名
			if (in_array(urlDomain($matche), $NoDomain)) {
				continue;
			}
			$remotefileurls[] = $matche;
		}
		$oldpath = $newpath = array();
		foreach ($remotefileurls as $k => $file) {
			if (strpos($file, '://') === false || strpos($file, $upload_url) !== false) {
				continue;
			}
			//取得文件扩展
			$file_fileext = fileext($file);
			//取得文件名
			$file_name = basename($file);
			//保存文件名
			$filename = $this->handler->getSaveName(array(
				'name' => $file_name,
				'extension' => $file_fileext,
				'savename' => $this->options['savePath'],
			));
			// 设置你需要抓取的URL
			curl_setopt($curl, CURLOPT_URL, cn_urlencode($file));
			// 设置header
			curl_setopt($curl, CURLOPT_HEADER, 0);
			// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			//超时设置
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			// 运行cURL，请求网页
			$filedata = curl_exec($curl);
			//保存图片
			$status = $this->build_file($filedata, $this->options['savePath'] . $filename);
			if ($status) {
				//加水印
				if ($watermark) {
					$this->water($this->options['savePath'] . $filename, "", $this->options['savePath'] . $filename);
				}
				$oldpath[] = $file;
				$newpath[] = str_replace($this->options['uploadfilepath'], $this->options['sitefileurl'], $this->options['savePath'] . $filename);
				$info = array(
					"name" => $file_name,
					"type" => "",
					"size" => filesize($this->options['savePath'] . $filename),
					"key" => "",
					"extension" => $file_fileext,
					"savepath" => $this->options['savePath'],
					"savename" => $filename,
					"hash" => md5(str_replace($this->options['uploadfilepath'], "", $this->options['savePath'] . $filename)),
				);
				$info['url'] = $this->options['sitefileurl'] . str_replace($this->options['uploadfilepath'], '', $info['savepath'] . $info['savename']);
				$aid = D('Attachment/Attachment')->fileInfoAdd($info, $this->options['module'], $this->options['catid'], $this->options['thumb'], $this->options['isadmin'], $this->options['userid'], $this->options['time'],$this->options['group_id']);
				//设置标识
				$this->upload_json($aid, $info['url'], $filename);
			}
		}
		// 关闭URL请求
		curl_close($curl);
		$value = str_replace($oldpath, $newpath, $value);
		return $value;
	}

	/**
	 * 获取上传错误信息
	 * @return string
	 */
	public function getErrorMsg() {
		return $this->error;
	}

}
