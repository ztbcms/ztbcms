<?php

// +----------------------------------------------------------------------
// | 搜索管理
// +----------------------------------------------------------------------

namespace Search\Controller;

use Common\Controller\AdminBase;

class SearchController extends AdminBase {

	//搜索配置
	protected $config;
	//数据对象
	private $db;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D("Search/Search");
		$this->config = cache('Search_config');
	}

	//搜索相关配置
	public function index() {
		if (IS_POST) {
			$setting = $_POST['setting'];
			if ($this->db->search_config($setting) !== false) {
				$this->success("配置修改成功！");
			} else {
				$this->error("配置修改失败！");
			}
		} else {
			$model = cache('Model');
			foreach ($model as $key => $rs) {
				if ($rs['type'] != 0) {
					unset($model[$key]);
				}
			}
			$this->assign("model_list", $model);
			$this->assign("config", $this->config);
			$this->display();
		}
	}

	//搜索关键词记录
	public function searchot() {
		$db = M("SearchKeyword");
		if (IS_POST) {
			$keyword = $_POST['keyword'];
			if (empty($keyword)) {
				$this->error("请选择需要删除的信息！");
			}
			if ($keyword && is_array($keyword)) {
				foreach ($keyword as $k) {
					$db->where(array("keyword" => $k))->delete();
				}
			}
			$this->success("操作成功！");
		} else {
			$count = $db->count();
			$page = $this->page($count, 20);
			$data = $db->limit($page->firstRow . ',' . $page->listRows)->order(array("searchnums" => "DESC"))->select();
			$this->assign("data", $data);
			$this->assign("Page", $page->show('Admin'));
			$this->display();
		}
	}

	//重建索引
	public function create() {
		unset($_GET['_URL_']);
		if (isset($_GET['start'])) {
			//每轮更新数
			$pagesize = I('get.pagesize', 0, 'intval');
			$_GET['pagesize'] = $pagesize = $pagesize > 1 ? $pagesize : 100;
			//模型
			$_GET['modelid'] = $modelid = I('get.modelid', 0, 'intval');
			//第几轮更新
			$page = $_GET['start'] = I('get.start', 0, 'intval');
			//总共几轮
			$pages = I('get.pages', 0, 'intval');
			//信息总数
			$total = I('get.total', 0, 'intval');
			$model = cache('Model');
			foreach ($model as $k => $rs) {
				if ($rs['type']) {
					unset($model[$k]);
				}
			}
			//如果是重建所有模型
			if ($modelid) {
				$table_name = ucwords($model[$modelid]['tablename']);
				if (empty($table_name)) {
					$this->error("该模型不存在！");
				}
				$ContentDb = \Content\Model\ContentModel::getInstance($modelid);
				if (!in_array($modelid, $this->config['modelid'])) {
					$this->error("该模型无需重建！");
				}
				//取得总数
				if (!isset($_GET['total'])) {
					$count = $ContentDb->where(array("status" => 99))->count();
					//信息总数
					$total = $_GET['total'] = $count;
					//总共几轮
					$pages = $_GET['pages'] = ceil($_GET['total'] / $pagesize);
					//初始第一轮更新
					$page = $_GET['start'] = 1;
				}
				$page = max(intval($page), 1);
				$offset = $pagesize * ($page - 1);
				$data = $ContentDb->relation(true)->where(array("status" => 99))->order(array("id" => "ASC"))->limit($offset . "," . $pagesize)->select();
				$ContentDb->dataMerger($data);
				if (!$data) {
					$data = array();
				}
				//数据处理
				foreach ($data as $r) {
					$id = $r['id'];
					$this->db->search_api($id, $r, $modelid);
				}

				if ($pages == $page || $page > $pages) {
					$this->success("更新完成！ ...", U("Search/create"));
					exit;
				}
				if ($pages > $page) {
					$page++;
					$_GET['start'] = $page;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "有 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = U("Search/create", $_GET);
					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
					exit;
				}
			} else {
				//当没有选择模型更新时，进行全部可用模型数据更新
				$modelArr = $this->config['modelid'];
				$autoid = I('get.autoid', 0, 'intval');
				if (!isset($modelArr[$autoid])) {
					$this->success("更新完成！ ...", U("Search/create"));
					exit;
				}
				$modelid = $modelArr[$autoid];
				$table_name = ucwords($model[$modelid]['tablename']);
				if (empty($table_name)) {
					$this->error("该模型不存在！");
				}
				$ContentDb = \Content\Model\ContentModel::getInstance($modelid);
				//取得总数
				if (!isset($_GET['total'])) {
					$count = $ContentDb->where(array("status" => 99))->count();
					//信息总数
					$total = $_GET['total'] = $count;
					//总共几轮
					$pages = $_GET['pages'] = ceil($_GET['total'] / $pagesize);
					//初始第一轮更新
					$page = $_GET['start'] = 1;
				}
				$page = max(intval($page), 1);
				$offset = $pagesize * ($page - 1);

				$data = $ContentDb->relation(true)->where(array("status" => 99))->order(array("id" => "ASC"))->limit($offset . "," . $pagesize)->select();
				$ContentDb->dataMerger($data);
				if (!$data) {
					$data = array();
				}
				//数据处理
				foreach ($data as $r) {
					$id = $r['id'];
					$this->db->search_api($id, $r, $modelid);
				}

				if ($pages == $page || $page > $pages) {
					$autoid++;
					$_GET['autoid'] = $autoid;
					unset($_GET['total']);
					$this->assign("waitSecond", 200);
					$this->success("模型【" . $model[$modelid]['name'] . "】更新完成 ...", U("Search/create", $_GET));
					exit;
				}

				if ($pages > $page) {
					$page++;
					$_GET['start'] = $page;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "【" . $model[$modelid]['name'] . "】有 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = U("Search/create", $_GET);
					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
					exit;
				}
			}
		} else {
			if (IS_POST) {
				//每轮更新数
				$pagesize = I('post.pagesize', 100, 'intval');
				//模型
				$modelid = I('post.modelid', 0, 'intval');
				if ($modelid) {
					//删除旧的搜索数据
					$this->db->where(array("modelid" => $modelid))->delete();
				} else {
					//删除旧的搜索数据
					$this->db->emptyTable();
				}
				$this->success("开始进行索引重建...", U("Search/create", array("start" => 1, "pagesize" => $pagesize, "modelid" => $modelid)));
			} else {
				$model = cache('Model');
				foreach ($model as $k => $rs) {
					if ($rs['type']) {
						unset($model[$k]);
					}
				}
				$this->assign('models', $model)
					->assign('config', $this->config)
					->display();
			}
		}
	}

}
