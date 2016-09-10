<?php

// +----------------------------------------------------------------------
// | 会员模型字段管理
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\AdminBase;

class FieldController extends AdminBase {

	private $modelfield, $fields, $banfie;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->modelfield = D("Member/MemberField");
		//字段类型存放目录
		$this->fields = D('Content/ModelField')->getFieldPath();
		//允许使用的字段列表
		$this->banfie = array("text", "textarea", "box", "number", "datetime", "map", "omnipotent");
	}

	//显示字段列表
	public function index() {
		$modelid = I('get.modelid', 0, 'intval');
		if (empty($modelid)) {
			$this->error('该模型不存在！');
		}
		$model = M("Model")->where(array("modelid" => $modelid))->find();
		if (empty($model)) {
			$this->error('该模型不存在！');
		}
		//根据模型读取字段列表
		$data = $this->modelfield->getModelField($modelid);
		//不允许删除的字段，这些字段讲不会在字段添加处显示
		$this->assign("not_allow_fields", $this->modelfield->not_allow_fields);
		//允许添加但必须唯一的字段
		$this->assign("unique_fields", $this->modelfield->unique_fields);
		//禁止被禁用的字段列表
		$this->assign("forbid_fields", $this->modelfield->forbid_fields);
		//禁止被删除的字段列表
		$this->assign("forbid_delete", $this->modelfield->forbid_delete);
		//可以追加 JS和CSS 的字段
		$this->assign("att_css_js", $this->modelfield->att_css_js);
		$this->assign("modelinfo", $model);
		$this->assign("modelid", $modelid);
		$this->assign("data", $data);
		$this->display();
	}

	//编辑字段
	public function edit() {
		//模型ID
		$modelid = I('request.modelid', 0, 'intval');
		//字段ID
		$fieldid = I('request.fieldid', 0, 'intval');
		if (empty($modelid)) {
			$this->error('模型ID不能为空！');
		}
		if (empty($fieldid)) {
			$this->error('字段ID不能为空！');
		}
		if (IS_POST) {
			$post = I('post.', '', '');
			if (empty($post)) {
				$this->error('数据不能为空！');
			}
			$post['issystem'] = 1;
			$post['isbase'] = 1;
			if ($this->modelfield->editField($post, $fieldid)) {
				cache('Model_Member', NULL);
				$this->success("更新成功！", U("Field/index", array("modelid" => $modelid)));
			} else {
				$error = $this->modelfield->getError();
				$this->error($error ?: '更新失败！');
			}
		} else {
			//模型信息
			$modedata = M("Model")->where(array("modelid" => $modelid))->find();
			if (empty($modedata)) {
				$this->error('该模型不存在！');
			}
			//字段信息
			$fieldData = $this->modelfield->where(array("fieldid" => $fieldid, "modelid" => $modelid))->find();
			if (empty($fieldData)) {
				$this->error('该字段信息不存在！');
			}
			//字段路径
			$fiepath = $this->fields . $fieldData['formtype'] . "/";
			extract($fieldData);
			//字段扩展配置
			$setting = unserialize($fieldData['setting']);
			//打开缓冲区
			ob_start();
			include $fiepath . 'field_edit_form.inc.php';
			$form_data = ob_get_contents();
			//关闭缓冲
			ob_end_clean();
			//载入字段配置
			$fields = include $this->fields . "fields.inc.php";
			//字段类型过滤
			foreach ($fields as $_k => $_v) {
				if (!in_array($_k, $this->banfie)) {
					continue;
				}

				$all_field[$_k] = $_v;
			}
			$this->assign("all_field", $all_field);
			$this->assign("modelinfo", M("Model")->where(array("modelid" => $modelid))->find());
			$this->assign("modelid", $modelid);
			$this->assign("data", $fieldData);
			$this->assign("form_data", $form_data);
			$this->assign("fieldid", $fieldid);
			$this->display();
		}
	}

	//增加字段
	public function add() {
		//模型ID
		$modelid = I('request.modelid', 0, 'intval');
		if (empty($modelid)) {
			$this->error('模型ID不能为空！');
		}
		if (IS_POST) {
			$post = I('post.', '', '');
			if (empty($post)) {
				$this->error('数据不能为空！');
			}
			$post['issystem'] = 1;
			$post['isbase'] = 1;
			if ($this->modelfield->addField($post)) {
				cache('Model_Member', NULL);
				$this->success('添加成功！', U("Field/index", array("modelid" => $modelid)));
			} else {
				$error = $this->modelfield->getError();
				$this->error($error ?: '添加失败！');
			}
		} else {
			$fields = include $this->fields . 'fields.inc.php';
			//字段类型过滤
			foreach ($fields as $_k => $_v) {
				if (!in_array($_k, $this->banfie)) {
					continue;
				}
				$all_field[$_k] = $_v;
			}
			$this->assign('all_field', $all_field);
			$this->assign('modelinfo', M("Model")->where(array("modelid" => $modelid))->find());
			$this->assign('modelid', $modelid);
			$this->display();
		}
	}

	//删除字段
	public function delete() {
		//字段ID
		$fieldid = I('get.fieldid', 0, 'intval');
		if (empty($fieldid)) {
			$this->error('字段ID不能为空！');
		}
		if ($this->modelfield->deleteField($fieldid)) {
			cache('Model_Member', NULL);
			$this->success("字段删除成功！");
		} else {
			$error = $this->modelfield->getError();
			$this->error($error ? $error : "删除字段失败！");
		}
	}

	//字段排序
	public function listorder() {
		if (IS_POST) {
			foreach ($_POST['listorders'] as $id => $listorder) {
				$this->modelfield->where(array('fieldid' => $id))->save(array('listorder' => $listorder));
			}
			cache('Model_Member', NULL);
			$this->success("排序更新成功！");
		} else {
			$this->error("排序失败！");
		}
	}

	//验证字段是否重复 AJAX
	public function public_checkfield() {
		//新字段名称
		$field = I('get.field');
		//原来字段名
		$oldfield = I('get.oldfield');
		if ($field == $oldfield) {
			$this->ajaxReturn($field, "字段没有重复！", true);
		}
		//模型ID
		$modelid = I('get.modelid');
		$status = $this->modelfield->where(array("field" => $field, "modelid" => $modelid))->count();
		if ($status == 0) {
			$this->ajaxReturn($field, "字段没有重复！", true);
		} else {
			$this->ajaxReturn($field, "字段有重复！", false);
		}
	}

	//字段属性配置
	public function public_field_setting() {
		//字段类型
		$fieldtype = I('get.fieldtype');
		$fiepath = $this->fields . $fieldtype . "/";
		//载入对应字段配置文件 config.inc.php
		require $fiepath . "config.inc.php";
		ob_start();
		include $fiepath . "field_add_form.inc.php";
		$data_setting = ob_get_contents();
		ob_end_clean();
		$settings = array('field_basic_table' => $field_basic_table, 'field_minlength' => $field_minlength, 'field_maxlength' => $field_maxlength, 'field_allow_search' => $field_allow_search, 'field_allow_fulltext' => $field_allow_fulltext, 'field_allow_isunique' => $field_allow_isunique, 'setting' => $data_setting);
		echo json_encode($settings);
		return true;
	}

	//字段的启用与禁用
	public function disabled() {
		$fieldid = intval($_GET['fieldid']);
		$disabled = $_GET['disabled'] ? 0 : 1;
		$status = $this->modelfield->where(array('fieldid' => $fieldid))->save(array('disabled' => $disabled));
		if ($status !== false) {
			cache('Model_Member', NULL);
			$this->success("操作成功！");
		} else {
			$this->error("操作失败！");
		}
	}

}
