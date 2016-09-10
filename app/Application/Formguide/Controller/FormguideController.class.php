<?php

// +----------------------------------------------------------------------
// | 表单管理
// +----------------------------------------------------------------------

namespace Formguide\Controller;

use Common\Controller\AdminBase;

class FormguideController extends AdminBase {

	//模板存放目录
	protected $filepath, $tpl;
	//表单模型对象
	protected $db = NULL;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D("Formguide/Formguide");
		//模块安装后，模板安装在Default主题下！
		$this->filepath = TEMPLATE_PATH . "Default/Formguide/";
		C('HTML_FILE_SUFFIX', "");
	}

	//表单列表
	public function index() {
		if (IS_POST) {
			//删除
			$formid = $_POST['formid'];
			if (is_array($formid)) {
				foreach ($formid as $modelid) {
					$this->db->deleteModel($modelid);
				}
				$this->success("删除成功！");
			} else {
				$this->error("请选择需要删除的表单！");
			}
		} else {
			$where = array("type" => 3);
			$count = $this->db->where($where)->count();
			$page = $this->page($count, 20);
			$data = $this->db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("modelid" => "DESC"))->select();
			$this->assign("Page", $page->show());
			$this->assign("menuid", I('ger.menuid', 0, 'intval'));
			$this->assign("data", $data);
			$this->display();
		}
	}

	//添加表单
	public function add() {
		if (IS_POST) {
			$info = I('post.info');
			$info['tablename'] = $info['tablename'] ? 'form_' . $info['tablename'] : '';
			$setting = I('post.setting');
			$setting['starttime'] = strtotime($setting['starttime']);
			$setting['endtime'] = strtotime($setting['endtime']);
			$info['setting'] = serialize($setting);
			//表单令牌
			$info[C("TOKEN_NAME")] = $_POST[C("TOKEN_NAME")];
			$data = $this->db->create($info);
			if ($data) {
				$data['type'] = $this->db->getModelType(); //类型
				$modelid = $this->db->add($data);
				if ($modelid) {
					//创建表
					$statis = $this->db->addModelFormguide($data['tablename'], $modelid);
					if (false === $statis) {
						$this->db->where(array('modelid' => $modelid))->delete();
						$this->error("表创建失败！");
					}
					//创建表
					$this->success("添加表单成功！", U('Formguide/index'));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$this->tpl = str_replace($this->filepath, "", glob($this->filepath . "Show" . DIRECTORY_SEPARATOR . 'show*'));
			$this->tpl = str_replace(array("Show" . DIRECTORY_SEPARATOR, C("TMPL_TEMPLATE_SUFFIX")), "", $this->tpl);
			foreach ($this->tpl as $v) {
				$show_template[$v] = $v;
			}
			$this->tpl = str_replace($this->filepath, "", glob($this->filepath . "Show" . DIRECTORY_SEPARATOR . 'js*'));
			$this->tpl = str_replace(array("Show" . DIRECTORY_SEPARATOR, C("TMPL_TEMPLATE_SUFFIX")), "", $this->tpl);
			foreach ($this->tpl as $v) {
				$show_js_template[$v] = $v;
			}
			$this->assign('show_template', $show_template);
			$this->assign("show_js_template", $show_js_template);
			$this->display();
		}
	}

	//表单编辑
	public function edit() {
		if (IS_POST) {
			$modelid = I('post.modelid', 0, 'intval');
			$info = I('post.info');
			$setting = I('post.setting');
			$setting['starttime'] = strtotime($setting['starttime']);
			$setting['endtime'] = strtotime($setting['endtime']);
			$info['setting'] = serialize($setting);
			unset($info['type'], $info['tablename']);
			if (I('post._name') == $info['name']) {
				unset($info['name']);
			}
			//表单令牌
			$info[C("TOKEN_NAME")] = $_POST[C("TOKEN_NAME")];
			$data = $this->db->create($info, 2);
			if ($data) {
				if ($this->db->where(array('modelid' => $modelid, 'type' => $this->db->getModelType()))->save($data) !== false) {
					$this->success("更新模型成功！", U("Formguide/index"));
				} else {
					$this->error("更新失败！");
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$formid = I('get.formid', 0, 'intval');
			$r = $this->db->where(array("modelid" => $formid))->find();
			if (!$r) {
				$this->error("该表单不存在！");
			}
			$r['setting'] = unserialize($r['setting']);
			$r['tablename'] = str_replace("form_", "", $r['tablename']);
			$this->assign($r);
			$this->tpl = str_replace($this->filepath, "", glob($this->filepath . "Show" . DIRECTORY_SEPARATOR . 'show*'));
			$this->tpl = str_replace(array("Show" . DIRECTORY_SEPARATOR, C("TMPL_TEMPLATE_SUFFIX")), "", $this->tpl);
			foreach ($this->tpl as $v) {
				$show_template[$v] = $v;
			}
			$this->tpl = str_replace($this->filepath, "", glob($this->filepath . "Show" . DIRECTORY_SEPARATOR . 'js*'));
			$this->tpl = str_replace(array("Show" . DIRECTORY_SEPARATOR, C("TMPL_TEMPLATE_SUFFIX")), "", $this->tpl);
			foreach ($this->tpl as $v) {
				$show_js_template[$v] = $v;
			}
			$this->assign('show_template', $show_template);
			$this->assign("show_js_template", $show_js_template);
			$this->display();
		}
	}

	//删除表单
	public function delete() {
		$formid = I('get.formid', 0, 'intval');
		if ($this->db->deleteModel($formid)) {
			$this->success("删除成功！");
		} else {
			$this->error('删除失败！');
		}
	}

	//禁用/启用状态转换
	public function status() {
		$modelid = I('get.formid', 0, 'intval');
		$disabled = $_GET['disabled'] ? 0 : 1;
		$status = $this->db->where(array('modelid' => $modelid, 'type' => $this->db->getModelType()))->save(array('disabled' => $disabled));
		if (false !== $status) {
			$this->success("操作成功，请更新缓存！");
		} else {
			$this->error("操作失败！");
		}
	}

	//调用
	public function public_call() {
		$formid = I('get.formid', 0, 'intval');
		$this->assign("formid", $formid);
		$this->display("call");
	}

}
