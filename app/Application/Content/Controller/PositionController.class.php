<?php

// +----------------------------------------------------------------------
// | 推荐位管理
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\AdminBase;

class PositionController extends AdminBase {

	//推荐位列表
	public function index() {
		$data = M('Position')->order(array('listorder' => 'ASC', 'posid' => 'DESC'))->select();
		$this->assign('data', $data)
			->display();
	}

	//数据重建
	public function rebuilding() {
		$action = I('get.action', 0, 'intval');
		$posid = I('get.posid', 0, 'intval');
		if (empty($posid)) {
			$this->error('请指定需要进行重建数据的推荐位！');
		}
		$model = M('PositionData');
		if ($action == 0) {
            //初始化
			$count = $model->where(array('posid' => $posid))->count();
			$lun = ceil($count / 20);
			$_GET['lun'] = $lun;
			$_GET['count'] = $count;
			$_GET['action'] = 1;
			$_GET['i'] = 0;
			$this->assign('waitSecond', 100);
			$this->success('开始准备进行数据重建工作！', U('rebuilding', $_GET));
			exit;
		} else if ($action == 1) {
			$lun = I('get.lun', 1, 'intval');
			$i = I('get.i', 0, 'intval');
			$cid = I('get.cid', 0, 'intval');
			if ($i > $lun) {
				$this->success('数据重建完毕！', U('index'));
				exit;
			}
			$dataList = $model->where(array('posid' => $posid, 'id' => array('GT', $cid)))->order(array('id' => 'ASC'))->limit(20)->select();
			if (empty($dataList)) {
				$this->success('数据重建完毕！', U('index'));
				exit;
			}
			$modelList = cache('Model');
			$modelField = cache('ModelField');
			foreach ($dataList as $rs) {
				$modelid = $rs['modelid'];
				$data = \Content\Model\ContentModel::getInstance($modelid)->relation(true)->where(array('id' => $rs['id']))->find();
				if (!$modelList[$modelid] || !$modelField[$modelid]) {
					continue;
				}
				\Content\Model\ContentModel::getInstance($modelid)->dataMerger($data);
				$textcontent = array();
				foreach ($modelField[$modelid] as $fieldInfo) {
					$field = $fieldInfo['field'];
					if ($fieldInfo['isposition']) {
						$textcontent[$field] = $data[$field];
					}
				}
				$textcontent['style'] = $data['style'] ?: '';
				$newsData = array(
					'id' => $rs['id'],
					'catid' => $rs['catid'],
					'modelid' => $modelid,
					'thumb' => $textcontent['thumb'] ? 1 : 0,
					'data' => serialize($textcontent),
                    'updatetime' => time()
				);
				$model->where(array('posid' => $posid, 'id' => $rs['id'], 'catid' => $rs['catid'], 'modelid' => $modelid))->save($newsData);
				$cid = $rs['id'];
			}
			$i++;
			$_GET['lun'] = $lun;
			$_GET['i'] = $i;
			$_GET['cid'] = $cid;
			$jindu = round(($i / $lun) * 100, 3);
			$this->assign('waitSecond', 100);
			$this->success("数据重建中..总共<font color=\"#FF0000\">{$lun}</font>轮，当前第<font color=\"#FF0000\">{$i}</font>轮，进度：<font color=\"#FF0000\">{$jindu}%</font>", U('rebuilding', $_GET));
		}
	}

	//添加推荐位
	public function add() {
		if (IS_POST) {
			$db = D('Content/Position');
			$_POST['info'] = array_merge($_POST['info'], array(C("TOKEN_NAME") => $_POST[C("TOKEN_NAME")]));
			if ($db->positionAdd($_POST['info'])) {
				$this->success("添加成功！<font color=\"#FF0000\">请更新缓存！</font>", U("Content/Position/index"));
			} else {
				$this->error($db->getError());
			}
		} else {
			$Model = cache('Model');
			foreach ($Model as $k => $v) {
				if ($v['type'] == 0) {
					$modelinfo[$v['modelid']] = $v['name'];
				}
			}
			$this->assign('modelinfo', $modelinfo);
			$this->display();
		}
	}

	//编辑推荐位
	public function edit() {
		$db = D('Content/Position');
		if (IS_POST) {
			$_POST['info'] = array_merge($_POST['info'], array(C("TOKEN_NAME") => $_POST[C("TOKEN_NAME")]));
			if ($db->positionSave($_POST['info'])) {
				$this->success('更新成功！<font color=\"#FF0000\">请更新缓存！</font>', U('index'));
			} else {
				$this->error($db->getError() ?: '修改失败！');
			}
		} else {
			$posid = I('get.posid', 0, 'intval');
			$data = $db->where(array('posid' => $posid))->find();
			if (!$data) {
				$this->error('该推荐位不存在！');
			}
			$Model = cache('Model');
			foreach ($Model as $k => $v) {
				if ($v['type'] == 0) {
					$modelinfo[$v['modelid']] = $v['name'];
				}
			}
			$this->assign($data);
			$this->assign('modelinfo', $modelinfo);
			$this->display();
		}
	}

	//删除 推荐位
	public function delete() {
		$posid = I('get.posid', 0, 'intval');
		$db = D('Content/Position');
		if ($db->positionDel($posid)) {
			$this->success('删除成功！<font color=\"#FF0000\">请更新缓存！</font>', U('Content/Position/index'));
		} else {
			$this->error($db->getError() ?: '删除失败');
		}
	}

	//信息管理
	public function item() {
		if (IS_POST) {
			$items = count($_POST['items']) > 0 ? $_POST['items'] : $this->error("没有信息被选择！");
			$db = D('Content/PositionData');
			if (is_array($items)) {
				foreach ($items as $item) {
					$_v = explode('-', $item);
					$db->deleteItem((int) $_POST['posid'], (int) $_v[0], (int) $_v[1]);
				}
			}
			$this->success("移除成功！");
		} else {
			$posid = I('get.posid', 0, 'intval');
			$db = M('PositionData');
			$where = array();
			$where['posid'] = $posid;
			$count = $db->where($where)->count();
			$page = $this->page($count, 20);
			$data = $db->where($where)->order(array("listorder" => "DESC", "id" => "DESC"))->limit($page->firstRow . ',' . $page->listRows)->select();
			foreach ($data as $k => $v) {
				$data[$k]['data'] = unserialize($v['data']);
				$tab = ucwords(getModel(getCategory($v['catid'], 'modelid'), 'tablename'));
				$data[$k]['data']['url'] = M($tab)->where(array("id" => $v['id']))->getField("url");
			}
			$this->assign("Page", $page->show())
				->assign("data", $data)
				->assign("posid", $posid)
				->assign('menuReturn', array('url' => U('index'), 'name' => '返回推荐位管理'))
				->display();
		}
	}

	//排序
	public function public_item_listorder() {
		if (IS_POST) {
			$db = M('PositionData');
			foreach ($_POST['listorders'] as $_k => $listorder) {
				$pos = array();
				$pos = explode('-', $_k);
				$db->where(array('id' => $pos[1], 'catid' => $pos[0], 'posid' => $_POST['posid']))->data(array('listorder' => $listorder))->save();
			}
			$this->success("排序更新成功！");
		} else {
			$this->error("请使用POST方式提交！");
		}
	}

	//信息管理编辑
	public function item_manage() {
		$db = D('Content/PositionData');
		if (IS_POST) {
			if ($_POST['thumb']) {
				$_POST['data']['thumb'] = $_POST['thumb'];
				$_POST['thumb'] = 1;
			} else {
				$_POST['thumb'] = 0;
			}
			if ($db->positionEdit($_POST)) {
				$this->success("更新成功！");
			} else {
				$this->error("更新失败！");
			}
		} else {
			$id = I('get.id', 0, 'intval');
			$modelid = I('get.modelid', 0, 'intval');
			$posid = I('get.posid', 0, 'intval');
			$data = $db->where(array("id" => $id, "modelid" => $modelid, 'posid' => $posid))->find();
			if (empty($data)) {
				$this->error("该信息不存在！");
			}
			$data['data'] = unserialize($data['data']);
			$this->assign($data);
			$this->display();
		}
	}

	///推荐位添加栏目加载
	public function public_category_load() {
		$modelid = I('get.modelid', '', '');
		$modelidList = explode(',', $modelid);
		$result = cache('Category');
		if (is_array($result)) {
			$categorys = array();
			foreach ($result as $r) {
				$r = getCategory($r['catid']);
				//过滤非普通栏目信息
				if ($r['type'] != 0) {
					continue;
				}
				$categorys[$r['catid']] = $r['catname'];
				if ($r['child'] != 0) {
					unset($categorys[$r['catid']]);
				}
				if (!empty($modelid) && !in_array($r['modelid'], $modelidList)) {
					unset($categorys[$r['catid']]);
				}
			}
		}
		echo \Form::checkbox($categorys, I('get.catid', 0, ''), 'name="info[catid][]"', '', 0);
	}

}
