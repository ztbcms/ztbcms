<?php

// +----------------------------------------------------------------------
// | 前台附件上传处理程序
// +----------------------------------------------------------------------

namespace Attachment\Controller;

use Common\Controller\Base;

class UploadController extends Base {

	const isadmin = 0; //是否后台

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
			//用户组
			$groupid = I("post.groupid", 8, "intval");
			$Member_group = cache("Member_group");
			if ((int) $Member_group[$groupid]['allowattachment'] < 1) {
				exit("0,所在的用户组没有附件上传权限！");
			}
			//取得栏目ID
			$catid = I('post.catid', 0, 'intval');
			//取得模块名称
			$module = I('post.module', '', 'trim,strtolower');
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

}
