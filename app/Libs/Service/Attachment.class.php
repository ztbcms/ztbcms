<?php

// +----------------------------------------------------------------------
// | 附件系统
// +----------------------------------------------------------------------

namespace Libs\Service;

use Libs\System\Service;

class Attachment extends Service {

	/**
	 * 操作句柄
	 * @var string
	 * @access protected
	 */
	protected $handler;

	/**
	 * 缓存连接参数
	 * @var integer
	 * @access protected
	 */
	protected $options = array();
	//网站配置参数
	protected $config = array();
	//错误信息
	public $error = null;

	/**
	 * 魔术方法，获取配置
	 * @param string $name
	 * @return string
	 */
	public function __get($name) {
		return isset($this->options[$name]) ? $this->options[$name] : NULL;
	}

	/**
	 *  魔术方法，设置options参数
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this->options[$name] = $value;
	}

	/**
	 * 连接附件系统
	 * @param string $name 服务名
	 * @param array $options 参数
	 * @return Attachment
	 */
	public static function connect($name = '', $options = array()) {
		if (empty($options['type'])) {
			//驱动类型
			$attachment_driver = cache("Config.attachment_driver");
			if (empty($attachment_driver)) {
				$attachment_driver = 'Local';
			} else {
				$type = $attachment_driver;
			}
		} else {
			$type = $options['type'];
		}
		//附件存储方案
		$class = strpos($type, '\\') ? $type : 'Libs\\Driver\\Attachment\\' . ucwords(strtolower($type));
		if (class_exists($class)) {
			$connect = new $class($options);
		} else {
            $connect = null;
			E("附件驱动 {$class} 不存在！");
		}
		return $connect;
	}

	/**
	 * 返回最近一条错误信息
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 上传全部文件
	 */
	public function upload() {

	}

	/**
	 * 把一个文件移动到另外一个位置
	 * @param string $originalFilesPath 原文件地址
	 * @param string $movingFilesPath 移动目标地址 SITE_PATH
	 * @return boolean
	 */
	public function movingFiles($originalFilesPath, $movingFilesPath) {
		$originalFilesPath = str_replace(SITE_PATH, '', $originalFilesPath);
		$movingFilesPath = str_replace(SITE_PATH, '', $movingFilesPath);
		if ($originalFilesPath == $movingFilesPath) {
			return true;
		}
		if (copy(SITE_PATH . $originalFilesPath, SITE_PATH . $movingFilesPath)) {
			unlink(SITE_PATH . $originalFilesPath);
			return true;
		} else {
			$this->error = '文件移动失败！';
			return false;
		}
	}

	/**
	 * 删除文件
	 * @param string $file 如果为数字，表示根据aid删除，其他为文件路径
	 * @return boolean
	 */
	public function delFile($file) {
		return true;
	}

	/**
	 * 删除文件夹（包括下面的文件）
	 * @param string $file 如果为数字，表示根据aid删除，其他为文件路径
	 * @return boolean
	 */
	public function delDir($dirPath) {
		$Dir = new \Dir();
		if ($Dir->delDir($dirPath)) {
			return true;
		} else {
			$this->error = $Dir->error;
			return false;
		}
	}

	/**
	 * 图片加水印
	 * @param string $source 原图文件名。
	 * @param string $water 水印图片文件名
	 * @param string $savename 要保存的图片名，如果留空则用source
	 * @param string $alpha  水印图片的alpha值，默认为80，范围为0~100
	 * @param string $waterPos 水印位置。
	 * @param string $quality jpg图片质量
     * @return  string
	 */
	public function water($source, $water = null, $savename = null, $alpha = null, $waterPos = null, $quality = null) {
		//设置默认水印
		if ($water == '') {
			$water = SITE_PATH . $this->config['watermarkimg'];
		}
		//图像信息
		$sInfo = \Image::getImageInfo($source);
		//如果图片小于系统设置，不进行水印添加
		if ($sInfo["width"] < (int) $this->config['watermarkminwidth'] || $sInfo['height'] < (int) $this->config['watermarkminheight']) {
			return false;
		}
		//水印位置
		if (empty($waterPos)) {
			$waterPos = (int) $this->config['watermarkpos'];
		}
		//水印透明度
		if (empty($alpha)) {
			$alpha = (int) $this->config['watermarkpct'];
		}
		//jpg图片质量
		if (empty($quality)) {
			$quality = (int) $this->config['watermarkquality'];
		}
		return \Image::water($source, $water, $savename, $alpha, $waterPos, $quality);
	}

	/**
	 * 远程保存
	 * @param string $value 传入下载内容
	 * @param string $watermark 是否加入水印
	 * @param string $ext 下载扩展名
     * @return string
	 */
	public function download($value, $watermark = null, $ext = 'gif|jpg|jpeg|bmp|png') {
		return $value;
	}

	/**
	 * 生成文件
	 * @param string $file 需要写入的文件或者二进制流
	 * @param string $filename 需要生成的文件名的绝对路径
	 * @return boolean
	 */
	protected function build_file($file, $filename) {
		$write = @fopen($filename, "w");
		if ($write == false) {
			return false;
		}
		if (fwrite($write, $file) == false) {
			return false;
		}
		if (fclose($write) == false) {
			return false;
		}
		return true;
	}

	/**
	 * 通过附件关系删除附件
	 * @param string $keyid 关联ID
	 * @return boolean 布尔值
	 */
	public function api_delete($keyid) {
		if (empty($keyid)) {
			return false;
		}
		$db = M("AttachmentIndex");
		$data = $db->where(array("keyid" => $keyid))->select();
		if ($data) {
			foreach ($data as $aid) {
				//统计使用同一个附件的次数，如果大于一，表示还有其他地方使用，将不删除附件
				$count = $db->where(array("aid" => $aid['aid']))->count();
				if ($count > 1) {
					//只删除附件关系，不删除真实附件
					continue;
				} else {
					if ($this->delFile((int) $aid['aid'])) {

					} else {
						return false;
					}
				}
			}
		}
		$db->where(array("keyid" => $keyid))->delete();
		return true;
	}

	/**
	 * 附件更新接口.
	 * @param string $content 可传入空，html，数组形式url，url地址，传入空时，以cookie方式记录。
	 * @param string $keyid 传入附件关系表中的组装id
	 * @isurl int $isurl 为本地地址时设为1,以cookie形式管理时设置为2
     * @return boolean
	 */
	public function api_update($content, $keyid, $isurl = 0) {
		$keyid = trim($keyid);
		$isurl = intval($isurl);
		if ($isurl == 2 || empty($content)) {
			$this->api_update_cookie($keyid);
		} else {
			$config = cache('Config');
			$att_index_db = M("AttachmentIndex");
			//http附件地址 http://file.ztbcms.com/d/file/
			$upload_url = $config['sitefileurl'];
			if (strpos($upload_url, '://') !== false) {
				$pos = strpos($upload_url, "/", 8);
				//附件域名 http://file.ztbcms.com/
				$domain = substr($upload_url, 0, $pos) . '/';
				//附件目录 d/file/
				$dir_name = substr($upload_url, $pos + 1);
			} else {
				//如果附件地址是类似“/d/file/”这样的
				$dir_name = $upload_url;
			}
			if ($isurl == 0) {
                //分析$content中的附件地址
				$pattern = '/(href|src)=\"(.*)\"/isU';
				preg_match_all($pattern, $content, $matches);
				if (is_array($matches) && !empty($matches)) {
					//移除数组中的重复的值
					$att_arr = array_unique($matches[2]);
					//开始计算md5
					foreach ($att_arr as $_k => $_v) {
						$att_arrs[$_k] = md5(str_replace(array($domain, $dir_name), '', $_v));
					}
				}
			} elseif ($isurl == 1) {
                //不用分析$content中的地址，$content本身就是一个地址，或者是一个一个数组的情况
				//如果传入的是数组
				if (is_array($content)) {
					$att_arr = array_unique($content);
					foreach ($att_arr as $_k => $_v) {
						$att_arrs[$_k] = md5(str_replace(array($domain, $dir_name), '', $_v));
					}
				} else {
					$att_arrs[] = md5(str_replace(array($domain, $dir_name), '', $content));
				}
			}
			//删除旧的关联关系，从新绑定附件和信息关系
			$att_index_db->where(array('keyid' => $keyid))->delete();
			if (is_array($att_arrs) && !empty($att_arrs)) {
				foreach ($att_arrs as $r) {
					$aid = M("Attachment")->where(array('authcode' => $r))->getField("aid");
					if ($aid) {
						M("Attachment")->where(array('aid' => $aid))->save(array('status' => 1));
						$att_index_db->add(array('keyid' => $keyid, 'aid' => $aid), array(), true);
					}
				}
			}
		}
		//删除附件cookie
		cookie('att_json', NULL);
		return true;
	}

	/**
	 * cookie 方式关联附件
	 * @param type $keyid 关联ID
	 * @return boolean 失败返回false
	 */
	private function api_update_cookie($keyid) {
		$att_index_db = M("AttachmentIndex");
		$att_json = cookie('att_json');
		if ($att_json) {
			$att_cookie_arr = explode('||', $att_json);
			$att_cookie_arr = array_unique($att_cookie_arr);
		} else {
			return false;
		}
		foreach ($att_cookie_arr as $_att_c) {
			$att[] = json_decode($_att_c, true);
		}
		foreach ($att as $_v) {
			M("Attachment")->where(array('aid' => $_v['aid']))->save(array('status' => 1));
			$att_index_db->add(array('keyid' => $keyid, 'aid' => $_v['aid']));
		}
	}

	/**
	 * 设置upload上传的json格式cookie
	 * @param string $aid 附件ID
	 * @param string $src 附件地址
	 * @param string $filename 附件名称
	 * @return boolean 返回布尔值
	 */
	public function upload_json($aid, $src, $filename) {
		$arr['aid'] = $aid;
		$arr['src'] = trim($src);
		$arr['filename'] = urlencode($filename);
		$json_str = json_encode($arr);
		$att_arr_exist = cookie('att_json');
		$att_arr_exist_tmp = explode('||', $att_arr_exist);
		if (is_array($att_arr_exist_tmp) && in_array($json_str, $att_arr_exist_tmp)) {
			return true;
		} else {
			$json_str = $att_arr_exist ? $att_arr_exist . '||' . $json_str : $json_str;
			cookie('att_json', $json_str);
			return true;
		}
	}

	/**
	 * 获取上传错误信息
	 * @return string
	 */
	public function getErrorMsg() {
		return $this->error;
	}

}
