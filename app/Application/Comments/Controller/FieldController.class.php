<?php

// +----------------------------------------------------------------------
// | 评论自定义字段
// +----------------------------------------------------------------------

namespace Comments\Controller;

use Common\Controller\AdminBase;

class FieldController extends AdminBase {

	private $db;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D('Comments/CommentsField');
		$this->assign('menuReturn', array(
			'name' => '返回评论设置',
			'url' => U('Comments/config'),
		));
	}

	//显示字段列表
	public function index() {
		$data = $this->db->order(array("fid" => "DESC"))->select();
		$this->assign("data", $data);
		$this->display();
	}

	//添加字段
	public function add() {
		if (IS_POST) {
			$data = $this->db->create();
			if ($data) {
				$data['regular'] = \Input::forTag($_POST['regular']);
				if ($this->db->fieldAdd($data)) {
					$this->success("添加成功！");
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$this->display();
		}
	}

	//删除字段
	public function delete() {
		$fid = I('get.fid', 0, 'intval');
		if (empty($fid)) {
			$this->error("参数错误！");
		};
		if ($this->db->fieldDelete($fid)) {
			$this->success("自定义字段删除成功！");
		} else {
			$this->error("自定义字段删除失败！");
		}
	}

	//编辑字段
	public function edit() {
		if (IS_POST) {
			$post = I('post.');
			if (!$post) {
				$this->error('编辑失败！');
			}
			$data = $this->db->create($post, 2);
			if ($data) {
				$data['regular'] = \Input::forTag($_POST['regular']);
				if ($this->db->fieldEdit($data)) {
					$this->success("编辑成功！");
				} else {
					$this->error("编辑失败！");
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$fid = I('get.fid', 0, 'intval');
			$data = $this->db->where(array("fid" => $fid))->find();
			if (!$data) {
				$this->error("该自定义字段不存在！");
			}
			$data['regular'] = \Input::forTag($data['regular']);
			$this->assign("data", $data);
			$this->display();
		}
	}

	/**
	 * 检查字段是否存在
	 */
	public function public_checkfield() {
		$f = I('get.f');
		if (empty($f)) {
			$this->error("字段名称不能为空！");
		}
		$info = $this->db->where(array('f' => $f))->find();
		if ($info) {
			$this->error("该字段已经存在！");
		} else {
			$this->success('该字段可以使用！');
		}
	}

}
