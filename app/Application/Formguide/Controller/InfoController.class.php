<?php

// +----------------------------------------------------------------------
// | 表单信息管理
// +----------------------------------------------------------------------

namespace Formguide\Controller;

use Common\Controller\AdminBase;

class InfoController extends AdminBase {

	//数据库对象
	protected $db = NULL;
	//当前表单ID
	public $formid;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->formid = I('request.formid', 0, 'intval');
		if (!empty($this->formid)) {
			$this->db = \Content\Model\ContentModel::getInstance($this->formid);
		}
		$this->assign('formid', $this->formid);
	}

	//信息列表
	public function index() {
		if (empty($this->formid)) {
			$this->error("该表单不存在！");
		}
		$where = array();
		$search = I('get.search');
		if ($search) {
			//添加开始时间
			$start_time = I('get.start_time');
			if (!empty($start_time)) {
				$start_time = strtotime($start_time);
				$where["datetime"] = array("EGT", $start_time);
			}
			//添加结束时间
			$end_time = I('get.end_time');
			if (!empty($end_time)) {
				$end_time = strtotime($end_time);
				$where["datetime"] = array("ELT", $end_time);
			}
			if ($end_time > 0 && $start_time > 0) {
				$where['datetime'] = array(array('EGT', $start_time), array('ELT', $end_time));
			}
			//类型
			$type = I('get.type', 0, 'intval');
			//搜索字段
			$keyword = \Input::getVar(I('get.keyword'));
			$this->assign("keyword", $keyword);
			if ($type) {
				$this->assign("searchtype", $type);
				if ($type == 1) {
					$where["ip"] = array("LIKE", "%{$keyword}%");
				}
				if ($type == 2) {
					$where["username"] = array("LIKE", "%{$keyword}%");
				}
			}
		}
		$count = $this->db->where($where)->count();
		$page = $this->page($count, 20);
		$data = $this->db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("dataid" => "DESC"))->select();

		$this->assign("Page", $page->show('Admin'));
		$this->assign("data", $data);
		$this->display();
	}

	//删除信息
	public function delete() {
		if (IS_POST) {
			$dataid = I('post.dataid');
			if (!is_array($dataid)) {
				$this->error("操作失败！");
			}
			if ($this->db->where(array('dataid' => array('IN', $dataid)))->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		} else {
			$dataid = I('get.dataid', 0, 'intval');
			if (empty($dataid)) {
				$this->error('该信息不存在！');
			}
			if ($this->db->where(array('dataid' => $dataid))->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}

	//信息查看
	public function public_view() {
		$dataid = I('get.dataid', 0, 'intval');
		if (!$this->formid || !$dataid) {
			$this->error("该信息不存在！<script>setTimeout(function(){window.top.art.dialog.list['check'].close();},1500);</script>");
		}
		if (empty($this->db)) {
			$this->error("该表单不存在！<script>setTimeout(function(){window.top.art.dialog.list['check'].close();},1500);</script>");
		}
		$data = $this->db->where(array("dataid" => $dataid))->find();
		if (!$data) {
			$this->error("该信息不存在！<script>setTimeout(function(){window.top.art.dialog.list['check'].close();},1500);</script>");
		}
		$content_form = new \content_output($this->formid);
		$data['modelid'] = $this->formid;
		//字段内容
		$forminfos = $content_form->get($data);
		$ModelField = cache('ModelField');
		$fields = $ModelField[$this->formid];
		unset($forminfos['dataid'], $forminfos['userid'], $forminfos['username'], $forminfos['datetime'], $forminfos['ip'], $forminfos['modelid']);
		$this->assign("forminfos", $forminfos);
		$this->assign("data", $data);
		$this->assign("fields", $fields);
		$this->display("view");
	}

}
