<?php

// +----------------------------------------------------------------------
// | 下载相关
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\Base;

class DownloadController extends Base {

	//信息ID
	public $id = 0, $catid = 0;
	//用户相关信息
	protected $userid = 0;
	protected $groupid = 8;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->id = I('get.id', 0, 'intval');
		$this->catid = I('get.catid', 0, 'intval');
		$this->userid = service("Passport")->userid ?: 0;
		$this->groupid = service("Passport")->groupid;
	}

	//显示下载页面
	public function index() {
		//编号，也就是下载第几个链接，有的是多文件下载用的！
		$k = I('get.k', 0, 'intval');
		//字段名称
		$f = I('get.f', '');
		if (empty($this->id) || empty($this->catid) || empty($f)) {
			$this->error("参数有误！");
		}
		//模型ID
		$modelid = getCategory($this->catid, 'modelid');
		$ModelField = cache('ModelField');
		$Model_field = $ModelField[$modelid];
		//判断字段类型
		if (!in_array($Model_field[$f]['formtype'], array('downfiles', 'downfile'))) {
			$this->error('下载地址错误！');
		}
		$this->db = \Content\Model\ContentModel::getInstance($modelid);
		$data = $this->db->relation(true)->where(array("id" => $this->id, 'status' => 99))->find();
		if (empty($data)) {
			$this->error("该信息不存在！");
		}
		$this->db->dataMerger($data);
		if (!empty($data)) {
			//取得下载字段信息
			$downfiles = $data[$f];
			$dowUnserialize = unserialize($downfiles);
			if ($dowUnserialize) {
				$info = $dowUnserialize[$k];
				if (empty($info)) {
					$this->error("该下载地址已经失效！");
				}
			} else {
				$info = array();
				$info['filename'] = basename($downfiles);
				$info['point'] = 0;
				$info['groupid'] = 0;
			}
			//当前客户端标识
			$aut = md5($this->userid . $this->groupid . substr($_SERVER['HTTP_USER_AGENT'], 0, 254));
			//加密
			//格式：aut|栏目ID|信息id|下载编号|字段
			$key = \Libs\Util\Encrypt::authcode(implode('|', array(
				$aut,
				$this->catid,
				$this->id,
				$k,
				$f)), '', '', 3600);
			$this->assign("info", $data);
			$this->assign("fileurl", U("Download/d", array('key' => urlencode($key))));
			$this->assign("filename", $info['filename']);
			$this->assign("point", $info['point']);
			$this->assign("groupid", $info['groupid']);
			$this->assign("Member_group", cache("Member_group"));
			$this->assign("SEO", seo($this->catid, urldecode($info['filename']), '', ''));
			$this->display("Public:download");
		} else {
			$this->error("该信息不存在！");
		}
	}

	//文件下载
	public function d() {
		//当前客户端标识
		$aut = md5($this->userid . $this->groupid . substr($_SERVER['HTTP_USER_AGENT'], 0, 254));
		//key
		$key = I('get.key', '', 'trim');
		if (!empty($key)) {
			$key = str_replace(array('+', '%23', '%2F', '%3F', '%26', '%3D', '%2B'), array(' ', '#', '/', '?', '&', '=', '+'), $key);
		}
		$key = \Libs\Util\Encrypt::authcode($key, "DECODE");
		if (empty($key)) {
			$this->error('下载地址非法！');
		}

		//格式：aut|栏目ID|信息id|下载编号|字段
		$key = explode("|", $key);
		//栏目ID
		$this->catid = $key[1];
		//信息ID
		$this->id = $key[2];
		//编号
		$k = $key[3];
		//字段名称
		$f = $key[4];
		//模型ID
		$modelid = getCategory($this->catid, 'modelid');
		$ModelField = cache('ModelField');
		$Model_field = $ModelField[$modelid];
		//判断字段类型
		if (!in_array($Model_field[$f]['formtype'], array('downfiles', 'downfile'))) {
			$this->error('下载地址错误！');
		}
		//主表名称
		if ((int) $Model_field[$f]['issystem'] == 1) {
			$tablename = ucwords(getModel($modelid, 'tablename'));
		} else {
			$tablename = ucwords(getModel($modelid, 'tablename')) . "_data";
		}
		//字段配置
		$setting = unserialize($Model_field[$f]['setting']);
		if ($aut == $key[0] && $setting) {
			//取得下载字段内容
			$downfiles = M($tablename)->where(array("id" => $this->id))->getField($f);
			$dowUnserialize = unserialize($downfiles);
			//判断是否可以反序列化
			if ($dowUnserialize) {
				$info = $dowUnserialize[$k];
				//判断会有组
				if ((int) $info['groupid'] > 0 || (int) $info['point'] > 0) {
					if (!$this->userid) {
						$this->error("请登录后再下载！", U("Member/Index/login", "forward=" . urlencode(get_url())));
					}
					if ((int) $info['groupid'] > 0 && (int) $this->groupid != (int) $info['groupid']) {
						$this->error("您所在的会有组不能下载该附件！");
					}
					if ((int) $info['point'] > 0) {
						$point = 0 - $info['point'];
						$status = service("Passport")->userIntegration($this->userid, $point);
						if ($status !== true) {
							$this->error(service("Passport")->getError() ?: '积分扣除失败！');
						}
						//下载记录
						$content_download_log = array(
							'id' => $this->id,
							'catid' => $this->catid,
							'field' => $f,
							'userid' => $this->userid,
							'groupid' => $this->groupid,
							'info' => $info,
						);
						tag('content_download_log', $content_download_log);
					}
				}
				//下载地址
				$fileurl = $info['fileurl'];
			} else {
				//下载地址
				$fileurl = $downfiles;
				$info = array();
				$info['filename'] = basename($fileurl);
				$info['filename'] = str_replace('.' . fileext($info['filename']), '', $info['filename']);
			}

			//下载统计+1
			if (!empty($setting['statistics'])) {
				$statistics = trim($setting['statistics']);
				M(ucwords(getModel($modelid, 'tablename')))->where(array("id" => $this->id))->setInc($statistics);
			}
			$urlDomain = urlDomain(self::$Cache['Cache']['siteurl']);
			if (!$urlDomain) {
				$urlDomain = urlDomain(get_url()); //当前页面地址域名
			}
			//不管附件地址是远程地址，还是不带域名的地址，都进行替换
			$fileurl = str_replace($urlDomain, "", $fileurl);
			//远程文件
			if (strpos($fileurl, ':/')) {
				header("Location: $fileurl");
				exit;
			}
			//取得文件后缀
			$houz = "." . fileext(basename($fileurl));
			$fileurl = SITE_PATH . $fileurl;
			if (file_exists($fileurl)) {
				$this->downfiles($fileurl, urldecode($info['filename'] . $houz));
			} else {
				$this->error("需要下载的文件不存在！");
			}
		} else {
			$this->error("下载地址不正确！");
		}
	}

	//开始下载
	protected function downfiles($file, $basename) {
		//获取用户客户端UA，用来处理中文文件名
		$ua = $_SERVER["HTTP_USER_AGENT"];
		//从下载文件地址中获取的后缀
		$fileExt = fileext(basename($file));
		//下载文件名后缀
		$baseNameFileExt = fileext($basename);
		if (preg_match("/MSIE/", $ua)) {
			$filename = iconv("UTF-8", "GB2312//IGNORE", $baseNameFileExt ? $basename : ($basename . "." . $fileExt));
		} else {
			$filename = $baseNameFileExt ? $basename : ($basename . "." . $fileExt);
		}
		header("Content-type: application/octet-stream");
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
			header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header("Content-Length: " . filesize($file));
		readfile($file);
	}

}
