<?php

// +----------------------------------------------------------------------
// | 会员模型管理
// +----------------------------------------------------------------------

namespace Member\Controller;

use Common\Controller\AdminBase;

class ModelController extends AdminBase {

	//会员模型
	protected $memberModel = NULL;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->memberModel = D('Content/Model');
	}

	//会员模型管理
	public function index() {
		$data = $this->memberModel->where(array("type" => 2))->order(array("modelid" => "DESC"))->select();
		$this->assign("data", $data);
		$this->display();
	}

	//添加模型
	public function add() {
		if (IS_POST) {
			$_POST['type'] = 2;
			$_POST['tablename'] = $_POST['tablename'] ? "member_" . $_POST['tablename'] : "";
			$post = I('post.');
			$data = $this->memberModel->create($post);
			if ($data) {
				//插入模型表
				$modelid = $this->memberModel->add($data);
				if ($modelid) {
					//创建表
					$this->memberModel->AddModelMember($data['tablename'], $modelid);
					//更新缓存
					D('Member/Member')->member_cache();
					$this->success("添加模型成功！", U("Model/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->memberModel->getError());
			}
		} else {
			$this->display();
		}
	}

	//编辑模型
	public function edit() {
		if (IS_POST) {
			if (empty($_POST['disabled'])) {
				$_POST['disabled'] = 0;
			}
			$post = I('post.');
			$modelid = I('post.modelid', 0, 'intval');
			unset($post['modelid']);
			$data = $this->memberModel->create($post, 2);
			if ($data) {
				if ($this->memberModel->where(array('modelid' => $modelid, 'type' => 2))->save($data) !== false) {
					//更新缓存
					D('Member/Member')->member_cache();
					$this->success("更新模型成功！", U("Model/index"));
				} else {
					$this->error("更新失败！");
				}
			} else {
				$this->error($this->memberModel->getError());
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$data = $this->memberModel->where(array("modelid" => $modelid, 'type' => 2))->find();
			if (empty($data)) {
				$this->error('该会员模型不存在！');
			}
			$this->assign("data", $data);
			$this->display();
		}
	}

	//删除模型
	public function delete() {
		$modelid = I('get.modelid', 0, 'intval');
		//这里可以根据缓存获取表名
		$modeldata = $this->memberModel->where(array("modelid" => $modelid, 'type' => 2))->find();
		if (empty($modeldata)) {
			$this->error("要删除的模型不存在！");
		}
		if ($this->memberModel->deleteModel($modeldata['modelid'])) {
			//更新缓存
			D('Member/Member')->member_cache();
			$this->success("删除成功！", U("Model/index"));
		} else {
			$this->error("删除失败！");
		}
	}

	//模型移动
	public function move() {
		if (IS_POST) {
			$modelid = I('post.modelid', 0, 'intval');
			$model = cache("Model_Member");
			if (empty($model[$modelid])) {
				$this->error("该模型不存在！");
			}
			//目标模型
			$to_modelid = I('post.to_modelid', 0, 'intval');
			if (empty($to_modelid)) {
				$this->error("请选择目标模型！");
			}
			if (empty($model[$to_modelid])) {
				$this->error("目标模型不存在！");
			}
			if ($to_modelid == $modelid) {
				$this->error("目标模型与当前模型相同，无需转移！");
			}
			$member = M("Member");
			if (false !== $member->where(array("modelid" => $modelid, 'type' => 2))->save(array("modelid" => $to_modelid))) {
				$this->success("会员转移成功！", U("Model/index"));
			} else {
				$this->error("会员转移失败！", U("Model/index"));
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$model = cache("Model_Member");
			if (empty($model[$modelid])) {
				$this->error("该模型不存在！");
			}
			foreach ($model as $k => $v) {
				$modelselect[$k] = $v['name'];
			}
			$this->assign("modelid", $modelid);
			$this->assign("modelselect", $modelselect);
			$this->display();
		}
	}

}
