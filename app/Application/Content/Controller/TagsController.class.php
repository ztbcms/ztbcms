<?php

// +----------------------------------------------------------------------
// | Tags管理
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\AdminBase;

class TagsController extends AdminBase {

	//tags列表
	public function index() {
		if (IS_POST) {
			$listorder = $_POST['listorder'];
			if (is_array($listorder)) {
				foreach ($listorder as $tagid => $v) {
					D('Content/Tags')->where(array('tagid' => $tagid))->save(array('listorder' => (int) $v));
				}
				$this->success('排序更新成功！', U('index'));
			} else {
				$this->error('排序更新失败！', U('index'));
			}
		} else {
			$this->basePage('Content/Tags', '', array('listorder' => 'DESC', 'tagid' => 'DESC'));
		}
	}

	//修改tags
	public function edit() {
		if (IS_POST) {
			$model = D('Content/Tags');
			$oldTagsName = I('post.oldtagsname', '', 'trim');
			$tag = I('post.tag', '', 'trim');
			if ($model->create() && $oldTagsName && $model->save() !== false) {
				if ($oldTagsName != $tag) {
					M('TagsContent')->where(array('tag' => $oldTagsName))->save(array('tag' => $tag));
				}
				$this->success('修改成功！', U('index'));
			} else {
				$error = $model->getError();
				$this->error($error ?: '修改失败！');
			}
		} else {
			$this->baseEdit('Content/Tags', 'index');
		}
	}

	//删除tags
	public function delete() {
		$db = D('Content/Tags');
		if (IS_POST) {
			$tagid = $_POST['tagid'];
			if (is_array($tagid)) {
				foreach ($tagid as $tid) {
					$info = $db->where(array('tagid' => $tid))->find();
					if (!empty($info)) {
						if ($db->delete() !== false) {
							M('TagsContent')->where(array('tag' => $info['tag']))->delete();
						}
					}
				}
				$this->success("删除成功！");
			} else {
				$this->error("参数错误！");
			}
		} else {
			$tagid = I('get.tagid', 0, 'intval');
			$info = $db->where(array('tagid' => $tagid))->find();
			if (empty($info)) {
				$this->error("该Tags不存在！");
			}
			if ($db->delete() !== false) {
				M('TagsContent')->where(array('tag' => $info['tag']))->delete();
				$this->success('删除成功！');
			} else {
				$this->error('删除失败！');
			}
		}
	}

	//tags数据重建
	public function create() {
		if (IS_POST || isset($_GET['modelid'])) {
			$modelid = I('request.modelid', 0, 'intval');
			$_GET['modelid'] = $modelid;
			$lun = I('get.lun', 0, 'intval'); //第几轮 0=>1
			if ($lun > (int) $_GET['zlun'] - 1) {
				$lun = (int) $_GET['zlun'] - 1;
			}
			$lun = $lun < 0 ? 0 : $lun;
			$mlun = 100;
			$firstRow = $mlun * ($lun < 0 ? 0 : $lun);
			$db = M('TagsContent');
			$tagdb = M('Tags');
			if (isset($_GET['delete'])) {
				//清空
				$db->execute('TRUNCATE TABLE  `' . C("DB_PREFIX") . 'tags_content`');
				$tagdb->execute('TRUNCATE TABLE  `' . C("DB_PREFIX") . 'tags`');
			}
			unset($_GET['delete']);
			$model = cache('Model');
			if ((int) $_GET['mo'] == 1) {

			} else {
				//模型总数
				$_GET['mocount'] = 1;
			}
			//当全部模型重建时处理
			if (!$modelid) {
				$modelCONUT = M("Model")->count();
				$modelDATA = M("Model")->where(array('type' => 0))->order(array('modelid' => 'ASC'))->find();
				$modelid = $modelDATA['modelid'];
				$_GET['mo'] = 1;
				$_GET['mocount'] = $modelCONUT;
				$_GET['modelid'] = $modelid;
			}
			$models_v = $model[$modelid];
			if (!is_array($models_v)) {
				$this->error("该模型不存在！");
				exit;
			}
			$count = \Content\Model\ContentModel::getInstance($modelid)->count();
			if ($count == 0) {
				if (isset($_GET['mo'])) {
					$where = array();
					$where['type'] = 0;
					$where['modelid'] = array('GT', $modelid);
					$modelDATA = M('Model')->where($where)->order(array('modelid' => 'ASC'))->find();
					if (!$modelDATA) {
						$this->success('Tags重建结束！', U('index'));
						exit;
					}
					unset($_GET['zlun']);
					unset($_GET['lun']);
					$modelid = $modelDATA['modelid'];
					$_GET['modelid'] = $modelid;
					$this->assign("waitSecond", 200);
					$this->success("模型：{$models_v['name']}，第 " . ($lun + 1) . "/{$zlun} 轮更新成功，进入下一轮更新中...", U('create', $_GET));
					exit;
				} else {
					$this->error('该模型下没有信息！');
					exit;
				}
			}
			//总轮数
			$zlun = ceil($count / $mlun);
			$_GET['zlun'] = $zlun;
			$this->createUP($models_v, $firstRow, $mlun);
			if ($lun == (int) $_GET['zlun'] - 1) {
				if (isset($_GET['mo'])) {
					$where = array();
					$where['type'] = 0;
					$where['modelid'] = array('GT', $modelid);
					$modelDATA = M('Model')->where($where)->order(array('modelid' => 'ASC'))->find();
					if (!$modelDATA) {
						$this->success("Tags重建结束！", U('index'));
						exit;
					}
					unset($_GET['zlun']);
					unset($_GET['lun']);
					$modelid = $modelDATA['modelid'];
					$_GET['modelid'] = $modelid;
				} else {
					$this->success("Tags重建结束！", U('index'));
					exit;
				}
			} else {
				$_GET['lun'] = $lun + 1;
			}
			$this->assign('waitSecond', 200);
			$this->success("模型：" . $models_v['name'] . "，第 " . ($lun + 1) . "/$zlun 轮更新成功，进入下一轮更新中...", U('create', $_GET));
			exit;
		} else {
			$model = cache('Model');
			$mo = array();
			foreach ($model as $k => $v) {
				if ($v['type'] == 0) {
					$mo[$k] = $v['name'];
				}
			}
			$this->assign('Model', $mo)
				->display();
		}
	}

	//数据重建
	protected function createUP($models_v, $firstRow, $mlun) {
		$db = M('TagsContent');
		$tagdb = M('Tags');
		$keywords = M(ucwords($models_v['tablename']))->where(array("status" => 99))->order(array("id" => "ASC"))->limit("{$firstRow},{$mlun}")->getField("id,id,keywords,tags,url,title,catid,updatetime");
		foreach ($keywords as $keyword) {
			$data = array();
			$time = time();
			$key = strpos($keyword['tags'], ',') !== false ? explode(',', $keyword['tags']) : explode(' ', $keyword['tags']);
			foreach ($key as $key_v) {
				if (empty($key_v) || $key_v == "") {
					continue;
				}
				$key_v = trim($key_v);
				if ($tagdb->where(array('tag' => $key_v))->getField('tagid')) {
					$tagdb->where(array('tag' => $key_v))->setInc('usetimes');
				} else {
					$tagdb->add(array(
						"tag" => $key_v,
						"usetimes" => 1,
						"lastusetime" => $time,
						"lasthittime" => $time,
					));
				}
				$data = array(
					'tag' => $key_v,
					"url" => $keyword['url'],
					"title" => $keyword['title'],
					"modelid" => $models_v['modelid'],
					"contentid" => $keyword['id'],
					"catid" => $keyword['catid'],
					"updatetime" => $time,
				);
				$db->add($data);
			}
		}
		return true;
	}

}
