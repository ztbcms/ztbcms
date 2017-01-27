<?php

// +----------------------------------------------------------------------
// | 自定义列表
// +----------------------------------------------------------------------

namespace Template\Controller;

use Common\Controller\AdminBase;

class CustomlistController extends AdminBase {

	private $db = NULL;

	//初始
	protected function _initialize() {
		parent::_initialize();
		$this->db = D('Template/Customlist');
	}

	//列表首页
	public function index() {
		$this->basePage($this->db, '', array("id" => "desc"));
	}

	//添加列表
	public function add() {
		if (IS_POST) {
			if ($this->db->addCustomlist($_POST)) {
				$this->success('添加成功！', U('index'));
			} else {
				$error = $this->db->getError();
				$this->error($error ? $error : '自定义列表添加失败！');
			}
		} else {
			$this->templateAndRule();
			$this->display();
		}
	}

	//编辑
	public function edit() {
		if (IS_POST) {
			if ($this->db->editCustomlist($_POST)) {
				$this->success('修改成功！', U('index'));
			} else {
				$error = $this->db->getError();
				$this->error($error ? $error : '自定义列表修改失败！');
			}
		} else {
			$id = I('get.id', 0, 'intval');
			$info = $this->db->where(array('id' => $id))->find();
			if (empty($info)) {
				$this->error('该自定义列表不存在！');
			}

			$this->templateAndRule($info);
			$this->assign('info', $info);
			$this->display();
		}
	}

	//删除
	public function delete() {
		$id = I('get.id', 0, 'intval');
		if ($this->db->deleteCustomlist($id)) {
			$this->success('删除成功！');
		} else {
			$error = $this->db->getError();
			$this->error($error ? $error : '删除失败！');
		}
	}

	//生成列表
	public function generate() {
		if (IS_POST) {
			$ids = I('post.ids');
			if (empty($ids)) {
				$this->error('请指定需要生成的自定义列表！');
			}
			foreach ($ids as $id) {
				if ($this->Html->createListHtml((int) $id) == false) {
					$this->error('生成失败！');
				}
			}
			$this->success('生成成功！');
		} else {
			$id = I('get.id', 0, 'intval');
			if (empty($id)) {
				$this->error('请指定需要生成的自定义列表！');
			}
			if ($this->Html->createListHtml((int) $id)) {
				$this->success('生成成功！');
			} else {
				$this->error('生成失败！');
			}
		}
	}
	/**
	 * 初始模板和URL规则信息
	 * @param array $info
	 */
	private function templateAndRule($info = array('urlruleid' => '')) {
		$filepath = TEMPLATE_PATH . (empty(self::$Cache["Config"]['theme']) ? "Default" : self::$Cache["Config"]['theme']) . "/Content/";
		$tp_list = str_replace("{$filepath}List/", '', glob($filepath . 'List/list*'));
		$this->assign('list_html_ruleid', \Form::urlrule('content', 'category', 1, $info['urlruleid'], 'name="urlruleid"'));
		$this->assign('tp_list', $tp_list);
	}

}
