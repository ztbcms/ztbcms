<?php

// +----------------------------------------------------------------------
// | 附件管理
// +----------------------------------------------------------------------

namespace Attachment\Controller;

use Common\Controller\AdminBase;

class AtadminController extends AdminBase {

	//附件存在物理地址
	public $path = '';

	function _initialize() {
		parent::_initialize();
		//附件目录强制/d/file/ 后台设置的附件目录，只对网络地址有效
		$this->path = C("UPLOADFILEPATH");
	}

	/**
	 * 附件管理
	 */
	public function index() {
		if (IS_POST) {
			$this->redirect('index', $_POST);
		}
		$db = M("Attachment");
		$where = array();
		$filename = I('get.filename', '', 'trim');
		if ($filename) {
			$where['filename'] = array('like', '%' . $filename . '%');
		}
		//时间范围搜索
		$start_uploadtime = I('get.start_uploadtime');
		$end_uploadtime = I('get.end_uploadtime');
		if (!empty($start_uploadtime)) {
			$where['uploadtime'] = array('EGT', strtotime($start_uploadtime));
			if ($end_uploadtime) {
				$where['uploadtime'] = array(array('EGT', strtotime($start_uploadtime)), array('ELT', strtotime($end_uploadtime)), 'AND');
			}
		}
		$fileext = I('get.fileext');
		if ($where['fileext']) {
			$where['fileext'] = array('eq', $fileext);
		}
		//附件使用状态
		$status = I('get.status');
		if ($status != '') {
			$where['status'] = array('eq', $status);
		}
		$count = $db->where($where)->count();
		$page = $this->page($count, 20);
		$data = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("uploadtime" => "DESC"))->select();
		foreach ($data as $k => $v) {
			$data[$k]['filesize'] = round($data[$k]['filesize'] / 1024, 2);
			$data[$k]['thumb'] = glob(dirname($this->path . $data[$k]['filepath']) . '/thumb_*' . basename($data[$k]['filepath']));
		}
		$this->assign("data", $data);
		$this->assign("Page", $page->show());
		$this->display();
	}

	/**
	 * 删除附件 get单个删除 post批量删除
	 */
	public function delete() {
		$Attachment = service("Attachment");
		if (IS_POST) {
			$aid = $_POST['aid'];
			foreach ($aid as $k => $v) {
				if ($Attachment->delFile((int) $v)) {
					//删除附件关系
					M("AttachmentIndex")->where(array("aid" => $v))->delete();
				}
			}
			$status = true;
		} else {
			$aid = I('get.aid', 0, 'intval');
			if (empty($aid)) {
				$this->error("缺少参数！");
			}
			if ($Attachment->delFile((int) $aid)) {
				M("AttachmentIndex")->where(array("aid" => $aid))->delete();
				$status = true;
			} else {
				$status = false;
			}
		}
		if ($status) {
			$this->success("删除附件成功！");
		} else {
			$this->error("删除附件失败！");
		}
	}

}
