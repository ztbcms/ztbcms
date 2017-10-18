<?php

// +----------------------------------------------------------------------
// | 后台附件上传处理程序
// +----------------------------------------------------------------------

namespace Attachment\Controller;

use Common\Controller\AdminBase;

class AdminController extends AdminBase {

	//附件存在物理地址
	public $path = "";

	const isadmin = 1; //是否后台

	//初始化
	protected function _initialize() {
		//除了swfupload不验证，其他都验证
		if (ACTION_NAME != 'swfupload') {
			parent::_initialize();
		}
		//附件目录强制/d/file/ 后台设置的附件目录，只对网络地址有效
		$this->path = C("UPLOADFILEPATH");
	}

	/**
	 * swfupload 上传
	 * 通过swf上传成功以后回调处理时会调用swfupload_json方法增加cookies！
	 */
	public function swfupload() {
		if (IS_POST) {
			$sess_id = I("post.sessid", 0);
			$key = md5(C("AUTHCODE") . $sess_id . self::isadmin);
			//密钥验证
			if (I("post.swf_auth_key") != $key) {
				exit("0,权限认证失败！" . I("post.swf_auth_key") . "|" . C("AUTHCODE"));
			}
			//回调函数
			$Callback = false;
			//用户ID
			$upuserid = I('post.uid', 0, 'intval');
			//取得栏目ID
			$catid = I('post.catid', 0, 'intval');
			//取得模块名称
			$module = I('post.module', 'content', 'trim,strtolower');
			//获取附件服务
			$Attachment = service("Attachment", array('module' => $module, 'catid' => $catid, 'isadmin' => self::isadmin, 'userid' => $upuserid));
			//缩略图宽度
			$thumb_width = I('post.thumb_width', 0, 'intval');
			$thumb_height = I('post.thumb_height', 0, 'intval');
			//图片裁减相关设置，如果开启，将不保留原图
			if ($thumb_width && $thumb_height) {
				$Attachment->thumb = true;
				$Attachment->thumbRemoveOrigin = true;
				//设置缩略图最大宽度
				$Attachment->thumbMaxWidth = $thumb_width;
				//设置缩略图最大高度
				$Attachment->thumbMaxHeight = $thumb_height;
			}
			//是否添加水印  post:watermark_enable 等于1也需要加水印
			if (I('post.watermark_enable', 0, 'intval')) {
				$Callback = array(
					array("\\Attachment\\Controller\\AttachmentsController", "water"),
				);
			}

			//开始上传
			$info = $Attachment->upload($Callback);
			if ($info) {
				if (in_array(strtolower($info[0]['extension']), array("jpg", "png", "jpeg", "gif"))) {
					// 附件ID 附件网站地址 图标(图片时为1) 文件名
					echo "{$info[0]['aid']}," . $info[0]['url'] . ",1," . str_replace(array("\\", "/"), "", $info[0]['name']);
					exit;
				} else {
					$fileext = $info[0]['extension'];
					if ($fileext == 'zip' || $fileext == 'rar') {
						$fileext = 'rar';
					} elseif ($fileext == 'doc' || $fileext == 'docx') {
						$fileext = 'doc';
					} elseif ($fileext == 'xls' || $fileext == 'xlsx') {
						$fileext = 'xls';
					} elseif ($fileext == 'ppt' || $fileext == 'pptx') {
						$fileext = 'ppt';
					} elseif ($fileext == 'flv' || $fileext == 'swf' || $fileext == 'rm' || $fileext == 'rmvb') {
						$fileext = 'flv';
					} else {
						$fileext = 'do';
					}

					echo "{$info[0]['aid']}," . $info[0]['url'] . "," . $fileext . "," . str_replace(array("\\", "/"), "", $info[0]['name']);
					exit;
				}
			} else {
				//上传失败，返回错误
				exit("0," . $Attachment->getErrorMsg());
			}
		} else {
			exit("0,上传失败！");
		}
	}

	//加载图片库
	public function public_album_load() {
		if (IS_POST) {
			$this->redirect('public_album_load', $_POST);
		}
		$config = cache('Config');
		$where = array();
		$db = M("Attachment");
		$filename = I('get.filename', '', '');
		$args = I('get.args', '1,jpg|jpeg|gif|png|bmp,1,,,0', '');
		$args = explode(",", $args);
		empty($filename) ?: $where['filename'] = array('like', '%' . $filename . '%');
		$uploadtime = I('get.uploadtime', '', '');
		if (!empty($uploadtime)) {
			$start_uploadtime = strtotime($uploadtime . ' 00:00:00');
			$stop_uploadtime = strtotime($uploadtime . ' 23:59:59');
			if ($start_uploadtime) {
				$where['uploadtime'] = array('EGT', $start_uploadtime);
			}
			if ($stop_uploadtime) {
				$where['uploadtime'] = array(array('EGT', $start_uploadtime), array('ELT', $stop_uploadtime), 'and');
			}
		}
		//强制只是图片类型
		$where['isimage'] = array("eq", 1);
		$count = $db->where($where)->count();
		//启用分页
        $limit = I('get.limit', 12);
        $page = I('get.page', 1);
        $page = $this->page($count, $limit, $page);
		$data = $db->where($where)->order(array("uploadtime" => "DESC"))->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($data as $k => $v) {
			$data[$k]['filepath'] = $config['sitefileurl'] . $data[$k]['filepath'];
		}
		$this->assign("Page", $page->show());
		$this->assign("data", $data);
		$this->assign("file_upload_limit", $args[0]);
		$this->display();
	}

	//图片在线裁减，保存图片
	public function public_crop_upload() {
		$Prefix = "thumb_"; //默认裁减图片前缀
		C('SHOW_PAGE_TRACE', false);
		if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
			$pic = $GLOBALS["HTTP_RAW_POST_DATA"];
			if (isset($_GET['width']) && !empty($_GET['width'])) {
				$width = intval($_GET['width']);
			}
			if (isset($_GET['height']) && !empty($_GET['height'])) {
				$height = intval($_GET['height']);
			}
			if (isset($_GET['file']) && !empty($_GET['file'])) {
				if (isImage($_GET['file']) == false) {
					exit();
				}
				$file = urldecode($_GET['file']);
				$basename = basename($file);
				if (strpos($basename, $Prefix) !== false) {
					$file_arr = explode('_', $basename);
					$basename = array_pop($file_arr);
				}
                $width = isset($width) ? $width : 'auto';
                $height = isset($height) ? $height : 'auto';
				$new_file = $Prefix . $width . '_' . $height . '_' . $basename;
				//栏目ID
				$catid = I('get.catid', 0, 'intval');
				$module = I('get.module');
				$Attachment = service("Attachment", array("module" => $module, "catid" => $catid));
				//附件存放路径
				$file_path = $Attachment->savePath;
				//附件原始名称
				$filename = basename($file);
				//上传文件的后缀类型
				$fileextension = fileext($file);
				//保存图片
				file_put_contents($file_path . $new_file, $pic);
				//图片信息
				$info = array(
					"name" => $filename,
					"type" => "",
					"size" => filesize($file_path . $new_file),
					"key" => "",
					"extension" => $fileextension,
					"savepath" => $file_path,
					"savename" => $new_file,
					"hash" => md5(str_replace($Attachment->uploadfilepath, "", $file_path . $new_file)),
				);
				$info['url'] = $Attachment->sitefileurl . str_replace($Attachment->uploadfilepath, '', $info['savepath'] . $info['savename']);
				$Attachment->movingFiles($info['savepath'] . $info['savename'], $info['savepath'] . $info['savename']);
			} else {
				return false;
			}
			echo $info['url'];
			exit;
		}
	}

	//显示附件下的缩图
	public function public_showthumbs() {
		$config = cache('Config');
		$aid = I('get.aid');
		$info = M("Attachment")->where(array('aid' => $aid))->find();
        $thumbs = [];
		if ($info) {
			$infos = glob(dirname($this->path . $info['filepath']) . '/thumb_*' . basename($info['filepath']));
			foreach ($infos as $n => $thumb) {
				$thumbs[$n]['thumb_url'] = str_replace($this->path, $config['sitefileurl'], $thumb);
				$thumbinfo = explode('_', basename($thumb));
				$thumbs[$n]['thumb_filepath'] = $thumb;
				$thumbs[$n]['width'] = $thumbinfo[1];
				$thumbs[$n]['height'] = $thumbinfo[2];
			}
		}
		$this->assign("thumbs", $thumbs);
		$this->display();
	}

	//删除附件缩图
	public function public_delthumbs() {
		//检查是否有删除附件权限
		if (\Libs\System\RBAC::authenticate('Attachment/Atadmin/delete') == false) {
			exit('您没有附件删除权限！');
		}
		$filepath = urldecode(I('get.filepath', '', ''));
		$reslut = @unlink($filepath);
		if ($reslut) {
			exit('1');
		}
		exit('附件删除失败！');
	}

}
