<?php

// +----------------------------------------------------------------------
// | 内容模型管理
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\AdminBase;

class ModelsController extends AdminBase {

    //初始化
    protected function _initialize() {
        parent::_initialize();
        load('Content/iconvfunc');
        //取得当前内容模型模板存放目录
        $this->filepath = TEMPLATE_PATH . (empty(self::$Cache["Config"]['theme']) ? "Default" : self::$Cache["Config"]['theme']) . "/Content/";
        //取得栏目频道模板列表
        $this->tp_category = str_replace($this->filepath . "Category/", '', glob($this->filepath . 'Category/category*'));
        //取得栏目列表模板列表
        $this->tp_list = str_replace($this->filepath . "List/", '', glob($this->filepath . 'List/list*'));
        //取得内容页模板列表
        $this->tp_show = str_replace($this->filepath . "Show/", '', glob($this->filepath . 'Show/show*'));
        //取得单页模板
        $this->tp_page = str_replace($this->filepath . "Page/", '', glob($this->filepath . 'Page/page*'));
        //取得评论模板列表
        $this->tp_comment = str_replace($this->filepath . "Comment/", '', glob($this->filepath . 'Comment/comment*'));
    }

    //显示模型列表
    public function index() {
        $data = D("Content/Model")->where(array("type" => 0))->select();
        $this->assign("data", $data);
        $this->display();
    }

	//添加模型
	public function add() {
		if (IS_POST) {
			$data = I('post.');
			if (empty($data)) {
				$this->error('提交数据不能为空！');
			}
			if (D("Content/Model")->addModel($data)) {
				$this->success("添加模型成功！");
			} else {
				$error = D("Content/Model")->getError();
				$this->error($error ? $error : '添加失败！');
			}
		} else {
			$this->display();
		}
	}

	//编辑模型
	public function edit() {
		if (IS_POST) {
			$data = I('post.');
			if (empty($data)) {
				$this->error('提交数据不能为空！');
			}
			if (D("Content/Model")->editModel($data)) {
				$this->success('模型修改成功！', U('index'));
			} else {
				$error = D("Content/Model")->getError();
				$this->error($error ? $error : '修改失败！');
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$data = D("Content/Model")->where(array("modelid" => $modelid))->find();
			$this->assign("data", $data);
			$this->display();
		}
	}

	//删除模型
	public function delete() {
		$modelid = I('get.modelid', 0, 'intval');
		//检查该模型是否已经被使用
		$count = M("Category")->where(array("modelid" => $modelid))->count();
		if ($count) {
			$this->error("该模型已经在使用中，请删除栏目后再进行删除！");
		}
		//这里可以根据缓存获取表名
		$modeldata = D("Content/Model")->where(array("modelid" => $modelid))->find();
		if (!$modeldata) {
			$this->error("要删除的模型不存在！");
		}
		if (D("Content/Model")->deleteModel($modelid)) {
			$this->success("删除成功！", U("index"));
		} else {
			$this->error("删除失败！");
		}
	}

	//检查表是否已经存在
	public function public_check_tablename() {
		$tablename = I('get.tablename', '', 'trim');
		$count = D("Content/Model")->where(array("tablename" => $tablename))->count();
		if ($count == 0) {
			$this->success('表名不存在！');
		} else {
			$this->error('表名已经存在！');
		}
	}

	//模型的禁用与启用
	public function disabled() {
		$modelid = I('get.modelid', 0, 'intval');
		$disabled = I('get.disabled') ? 0 : 1;
		$status = D("Content/Model")->where(array('modelid' => $modelid))->save(array('disabled' => $disabled));
		if ($status !== false) {
			$this->success("操作成功！");
		} else {
			$this->error("操作失败！");
		}
	}

	//模型导入
	public function import() {
		if (IS_POST) {
			if (empty($_FILES['file'])) {
				$this->error("请选择上传文件！");
			}
			$filename = $_FILES['file']['tmp_name'];
			if (strtolower(substr($_FILES['file']['name'], -3, 3)) != 'txt') {
				$this->error("上传的文件格式有误！");
			}
			//读取文件
			$data = file_get_contents($filename);
			//删除
			@unlink($filename);
			//模型名称
			$name = I('post.name', NULL, 'trim');
			//模型表键名
			$tablename = I('post.tablename', NULL, 'trim');
			//导入
			$status = D("Content/Model")->import($data, $tablename, $name);
			if ($status) {
				$this->success("模型导入成功，请及时更新缓存！");
			} else {
				$this->error(D("Content/Model")->getError() ? D("Content/Model")->getError() : '模型导入失败！');
			}
		} else {
			$this->display();
		}
	}

	//模型导出
	public function export() {
		//需要导出的模型ID
		$modelid = I('get.modelid', 0, 'intval');
		if (empty($modelid)) {
			$this->error('请指定需要导出的模型！');
		}
		C('SHOW_PAGE_TRACE', false);
		//导出模型
		$status = D("Content/Model")->export($modelid);
		if ($status) {
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=ztb_model_" . $modelid . '.txt');
			echo $status;
		} else {
			$this->error(D("Content/Model")->getError() ? D("Content/Model")->getError() : '模型导出失败！');
		}
	}

}
