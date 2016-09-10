<?php

// +----------------------------------------------------------------------
// | 编辑器
// +----------------------------------------------------------------------

namespace Attachment\Controller;

class UeditorController extends AttachmentsController {

	//编辑器初始配置
	private $confing = array(
		/* 上传图片配置项 */
		'imageActionName' => 'uploadimage',
		'imageFieldName' => 'upfilesss',
		'imageMaxSize' => 0, /* 上传大小限制，单位B */
		'imageAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp'),
		'imageCompressEnable' => true,
		'imageCompressBorder' => 1600,
		'imageInsertAlign' => 'none',
		'imageUrlPrefix' => '',
		'imagePathFormat' => '',
		/* 涂鸦图片上传配置项 */
		'scrawlActionName' => 'uploadscrawl',
		'scrawlFieldName' => 'upfile',
		'scrawlPathFormat' => '',
		'scrawlMaxSize' => 0,
		'scrawlUrlPrefix' => '',
		'scrawlInsertAlign' => 'none',
		/* 截图工具上传 */
		'snapscreenActionName' => 'uploadimage',
		'snapscreenPathFormat' => '',
		'snapscreenUrlPrefix' => '',
		'snapscreenInsertAlign' => 'none',
		/* 抓取远程图片配置 */
		'catcherLocalDomain' => array('127.0.0.1', 'localhost', 'img.baidu.com'),
		'catcherActionName' => 'catchimage',
		'catcherFieldName' => 'source',
		'catcherPathFormat' => '',
		'catcherUrlPrefix' => '',
		'catcherMaxSize' => 0,
		'catcherAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp'),
		/* 上传视频配置 */
		'videoActionName' => 'uploadvideo',
		'videoFieldName' => 'upfile',
		'videoPathFormat' => '',
		'videoUrlPrefix' => '',
		'videoMaxSize' => 0,
		'videoAllowFiles' => array(".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg", ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"),
		/* 上传文件配置 */
		'fileActionName' => 'uploadfile',
		'fileFieldName' => 'upfile',
		'filePathFormat' => '',
		'fileUrlPrefix' => '',
		'fileMaxSize' => 0,
		'fileAllowFiles' => array(".flv", ".swf"),
		/* 列出指定目录下的图片 */
		'imageManagerActionName' => 'listimage',
		'imageManagerListPath' => '',
		'imageManagerListSize' => 20,
		'imageManagerUrlPrefix' => '',
		'imageManagerInsertAlign' => 'none',
		'imageManagerAllowFiles' => array('.png', '.jpg', '.jpeg', '.gif', '.bmp'),
		/* 列出指定目录下的文件 */
		'fileManagerActionName' => 'listfile',
		'fileManagerListPath' => '',
		'fileManagerUrlPrefix' => '',
		'fileManagerListSize' => '',
		'fileManagerAllowFiles' => array(".flv", ".swf"),
	);

	//初始化
	protected function _initialize() {
		defined('Ueditor') or define('Ueditor', true);
		if (IS_POST) {
			$authkey = I('get.authkey');
			$sess_id = I('get.sessid', 0);
			$userid = I('get.uid');
			$this->isadmin = I('get.isadmin', 0, 'intval');
			$key = md5(C("AUTHCODE") . $sess_id . $userid . $this->isadmin);
			if ($key != $authkey) {
				exit(json_encode(array('state' => '身份认证失败！')));
			} else {
				$this->uid = $userid;
				$this->groupid = I('get.groupid');
			}
		}
		parent::_initialize();
		if ($this->isadmin) {
			//上传大小
			$this->confing['imageMaxSize'] = $this->confing['scrawlMaxSize'] = $this->confing['catcherMaxSize'] = $this->confing['videoMaxSize'] = $this->confing['fileMaxSize'] = self::$Cache['Config']['uploadmaxsize'] * 1024;
			//上传文件类型
			$uploadallowext = explode('|', self::$Cache['Config']['uploadallowext']);
			foreach ($uploadallowext as $k => $rs) {
				$uploadallowext[$k] = ".{$rs}";
			}
			$this->confing['fileAllowFiles'] = $uploadallowext;
		} else {
			$this->confing['imageMaxSize'] = $this->confing['scrawlMaxSize'] = $this->confing['catcherMaxSize'] = $this->confing['videoMaxSize'] = $this->confing['fileMaxSize'] = self::$Cache['Config']['qtuploadmaxsize'] * 1024;
			//上传文件类型
			$uploadallowext = explode('|', self::$Cache['Config']['qtuploadallowext']);
			foreach ($uploadallowext as $k => $rs) {
				$uploadallowext[$k] = ".{$rs}";
			}
			$this->confing['fileAllowFiles'] = $uploadallowext;
		}
	}

	//编辑器配置
	public function run() {
		$action = I('get.action');
		$result = array();
		switch ($action) {
			case 'config':
				$result = $this->confing;
				break;
			//上传涂鸦
			case 'uploadscrawl':
				$catid = I('get.catid');
				$module = I('get.module', ($catid ? 'content' : MODULE_NAME), 'trim');
				$base64Data = $_POST[$this->confing['scrawlFieldName']];
				if (empty($base64Data)) {
					exit(json_encode(array('state' => '没有涂鸦内容！')));
				}
				$img = base64_decode($base64Data);
				$oriName = 'scrawl.png';
				$fileType = 'png';
				$fileSize = strlen($img);
				//上传目录
				$savePath = D('Attachment/Attachment')->getFilePath($module, 'Y/m', time());
				$up = new \UploadFile();
				//保存文件名
				$fileName = $up->getSaveName(array('name' => $oriName, 'extension' => 'png'));
				//保存地址
				$filePath = $savePath . $fileName;
				//保存后的访问地址
				$url = self::$Cache['Config']['sitefileurl'] . str_replace(array(C('UPLOADFILEPATH'), '//', '\\'), array('', '/', '\/'), $filePath);
				//写入临时文件
				if (file_put_contents($filePath, $img)) {
					$result = array(
						'state' => 'SUCCESS', //成功返回标准，否则是错误提示
						'url' => $url, //成功地址
						'title' => $oriName, //上传后的文件名
						'original' => $oriName, //原来的
					);
				} else {
					exit(json_encode(array('state' => '保存失败！')));
				}
				break;
			//上传图片
			case 'uploadimage':
				$catid = I('get.catid');
				$module = I('get.module', ($catid ? 'content' : MODULE_NAME), 'trim');
				$Attachment = service('Attachment', array('module' => $module, 'catid' => $catid, 'userid' => $this->uid, 'isadmin' => $this->isadmin));
				//设置上传类型，强制为图片类型
				$Attachment->uploadallowext = array("jpg", "png", "gif", "jpeg");
				if ($this->isadmin < 1) {
					//如果是非后台用户，进行权限判断
					$member_group = cache('Member_group');
					if ((int) $member_group[$this->groupid]['allowattachment'] < 1) {
						exit(json_encode(array('state' => '没有上传权限！')));
					}
				}
				//站点配置
				$siteConfig = cache('Config');
				//开始上传
				$Callback = false;
				if ($siteConfig['watermarkenable']) {
					$Callback = array(array("\\Attachment\\Controller\\AttachmentsController", "water"));
				}
				$info = $Attachment->upload($Callback);
				if ($info) {
					// 设置附件cookie
					$Attachment->upload_json($info[0]['aid'], $info[0]['url'], str_replace(array("\\", "/"), "", $info[0]['name']));
					$result = array(
						'state' => 'SUCCESS', //成功返回标准，否则是错误提示
						'url' => $info[0]['url'], //成功地址
						'title' => str_replace(array("\\", "/"), "", $info[0]['name']), //上传后的文件名
						'original' => $info[0]['name'], //原来的
					);
				} else {
					$result = array(
						'state' => $Attachment->getError() ?: '上传失败',
					);
				}
				break;
			//图片在线管理
			case 'listfile':
			case 'listimage':
				$listArr = $this->att_not_used();
				$list = array();
				foreach ($listArr as $rs) {
					if (!isImage($rs['src']) && $action != 'listfile') {
						continue;
					}
					$list[] = array(
						'url' => $rs['src'],
						'mtime' => time(),
					);
				}
				$result = array(
					'state' => 'SUCCESS',
					'list' => $list,
					'total' => count($listArr),
				);
				break;
			//上传视频
			case 'uploadvideo':
			//上传附件
			case 'uploadfile':
				$catid = I('get.catid');
				$module = I('get.module', ($catid ? 'content' : MODULE_NAME), 'trim');
				$Attachment = service('Attachment', array('module' => $module, 'catid' => $catid, 'userid' => $this->uid, 'isadmin' => $this->isadmin));
				//设置上传类型
				if ($this->isadmin) {
					$Attachment->uploadallowext = explode('|', self::$Cache['Config']['uploadallowext']);
				} else {
					$Attachment->uploadallowext = explode('|', self::$Cache['Config']['qtuploadallowext']);
				}
				//回调函数
				$Callback = false;
				if ($this->isadmin < 1) {
					//如果是非后台用户，进行权限判断
					$member_group = cache('Member_group');
					if ((int) $member_group[$this->groupid]['allowattachment'] < 1) {
						exit(json_encode(array('state' => '没有上传权限！')));
					}
				}
				//开始上传
				$info = $Attachment->upload($Callback);
				if ($info) {
					// 设置附件cookie
					$Attachment->upload_json($info[0]['aid'], $info[0]['url'], str_replace(array("\\", "/"), "", $info[0]['name']));
					$result = array(
						'state' => 'SUCCESS', //成功返回标准，否则是错误提示
						'url' => $info[0]['url'], //成功地址
						'name' => str_replace(array("\\", "/"), "", $info[0]['name']), //上传后的文件名
						'size' => $info[0]['size'],
						'type' => '.' . $info[0]['extension'],
						'original' => $info[0]['name'], //原来的
					);
				} else {
					$result = array(
						'state' => $Attachment->getError() ?: '上传失败',
					);
				}
				break;
			default:
				$result = array(
					'state' => '请求地址出错',
				);
				break;
		}
		exit(json_encode($result));
	}

}
