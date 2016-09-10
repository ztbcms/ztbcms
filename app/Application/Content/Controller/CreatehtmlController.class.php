<?php

// +----------------------------------------------------------------------
// | 静态文件生成管理
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\AdminBase;
use Content\Model\ContentModel;

class CreatehtmlController extends AdminBase {

	public $model = array();
	public $categorys = array();

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$model = cache('Model');
		foreach ($model as $rs) {
			if ($rs['type'] == 0) {
				$this->model[$rs['modelid']] = $rs;
			}
		}
		$this->categorys = cache('Category');
		//合并get到post
		foreach ($_GET as $k => $v) {
			$_POST[$k] = $v;
		}
	}

	//更新首页
	public function index() {
		if (IS_POST) {
			$config = cache('Config');
			if (!$config['generate']) {
				$this->error('系统关闭了首页生成静态！');
			}
			$this->Html->index();
			$this->success('首页更新成功！', U('Content/Createhtml/index'));
		} else {
			$this->display();
		}
	}

	//批量更新栏目页 $pagesize  每轮更新数 $modelid 需要更新的模型id
	public function category() {
		if (isset($_POST['dosubmit'])) {
			extract($_POST, EXTR_SKIP);
			//存在 进行url编码
			$referer = isset($referer) ? urlencode($referer) : '';
			//模型id
			$modelid = intval($_POST['modelid']);
			if (!isset($set_catid)) {
				if ($catids[0] != 0) {
//指定栏目
					$update_url_catids = $catids;
				} else {
					//栏目不限
					foreach ($this->categorys as $catid => $cat) {
						$cat = getCategory($cat['catid']);
						if ($cat['type'] == 2 || !$cat['sethtml']) {
							continue;
						}

						//如果限制模型，进行模型判断
						if ($modelid && ($modelid != $cat['modelid'])) {
							continue;
						}

						$update_url_catids[] = $catid;
					}
				}
				//缓存需要生成的栏目ID集合
				F('update_html_catid' . \Admin\Service\User::getInstance()->id, $update_url_catids);
				$message = "开始更新栏目页 ...";
				$forward = U('Content/Createhtml/category', "set_catid=1&pagesize={$pagesize}&dosubmit=1&modelid={$modelid}&referer={$referer}");
				$this->assign("waitSecond", 200);
				$this->success($message, $forward);
				exit;
			}
			//从缓存中取得需要生成的栏目ID集合
			$catid_arr = F('update_html_catid' . \Admin\Service\User::getInstance()->id);
			//当前更新到的栏目
			$autoid = $autoid ? intval($autoid) : 0;
			//判断是否更新结束
			if (!isset($catid_arr[$autoid])) {
				if (!empty($referer) && getCategory($catid_arr[0], 'type') != 1) {
					$referer = urldecode($referer);
					$this->success('更新完成！', $referer);
					exit;
				} else {
					$this->success('更新完成！', U('Content/Createhtml/category'));
					exit;
				}
			}
			//根据$autoid取得缓存$catid_arr中的栏目id
			$catid = $catid_arr[$autoid];
			//每个栏目中更新第几页
			$page = $page ? $page : 1;
			$j = 1;
			//开始生成列表
			do {
				$this->Html->category($catid, $page);
				$page++;
				$j++;
				//如果GET有total_number参数则直接使用GET的，如果没有则根据$GLOBALS["Total_Pages"]获取分页总数
				$total_number = isset($_GET['total_number']) ? (int) $_GET['total_number'] : $GLOBALS["Total_Pages"];
			} while ($page <= $total_number && $j <= $pagesize);

			if ($page <= $total_number) {
				$endpage = intval($page + $pagesize);
				$message = "正在更新" . getCategory($catid, 'catname') . " 第<font color=\"red\">{$page}</font>页 - 当前进度：" . (round($page / $total_number, 2) * 100) . "% - 总共" . ceil($total_number / $pagesize) . "轮";
				$forward = U('Content/Createhtml/category', "set_catid=1&pagesize={$pagesize}&dosubmit=1&autoid={$autoid}&page={$page}&total_number={$total_number}&modelid={$modelid}&referer={$referer}");
				$this->assign('waitSecond', 200);
				$this->success($message, $forward);
				exit;
			} else {
				$autoid++;
				$message = getCategory($catid, 'catname') . "更新完成！ ...";
				$forward = U('Content/Createhtml/category', "set_catid=1&pagesize={$pagesize}&dosubmit=1&autoid={$autoid}&modelid={$modelid}&referer={$referer}");
				$this->assign('waitSecond', 200);
				$this->success($message, $forward);
				exit;
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$this->Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$this->Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array();
			if (!empty($this->categorys)) {
				foreach ($this->categorys as $catid => $r) {
					$r = getCategory($r['catid']);
					if ($r['type'] == 2 && $r['child'] == 0) {
						continue;
					}

					if ($modelid && $modelid != $r['modelid']) {
						continue;
					}

					if ($r['child'] == 0) {
						if (!$r['sethtml']) {
							continue;
						}

					}
					$categorys[$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected>\$spacer \$catname</option>";

			$this->Tree->init($categorys);
			$string .= $this->Tree->get_tree(0, $str);
			$this->assign('models', $this->model)
				->assign('string', $string)
				->assign('modelid', $modelid)
				->display();
		}
	}

	//生成单个栏目列表
	public function categoryhtml() {
		$catid = I('get.catid', 0, 'intval');
		if ($catid) {
			$setting = getCategory($catid, 'setting');
			if (!$setting['ishtml']) {
				$this->error('该栏目无须生成！');
			}
			$this->Html->category($catid);
			$this->success('栏目列表生成成功！');
		} else {
			$this->error('请选择需要生成的栏目！');
		}
	}

	//批量更新URL
	public function update_urls() {
		if (isset($_POST['dosubmit'])) {
			extract($_POST, EXTR_SKIP);
			//模型ID
			$modelid = intval($_POST['modelid']);
			if ($modelid) {
				$model = ContentModel::getInstance($modelid);
				if ($type == 'lastinput') {
					$offset = 0;
				} else {
					$page = max(intval($page), 1);
					$offset = $pagesize * ($page - 1);
				}
				$where = array();
				$order = 'ASC';
				//设置状态
				$where["status"] = array("EQ", 99);
				if (!isset($first) && is_array($catids) && $catids[0] > 0) {
					//把选择需要生成的栏目id写入缓存
					F("url_show_" . \Admin\Service\User::getInstance()->id, $catids);
					$catids = implode(',', $catids);
					$where['catid'] = array("IN", $catids);
					$first = 1;
				} elseif ($first) {
					//获取缓存
					$catids = F("url_show_" . \Admin\Service\User::getInstance()->id);
					$catids = implode(',', $catids);
					$where['catid'] = array("IN", $catids);
				} else {
					$first = 0;
				}
				//更新最新发布的
				if ($type == 'lastinput' && $number) {
					$offset = 0;
					//获取更新最新发布的多少条
					$pagesize = $number;
					$order = 'DESC';
				} elseif ($type == 'date') {
//以时间段更新
					if ($fromdate) {
						$fromtime = strtotime($fromdate . ' 00:00:00');
						$where["inputtime"] = array("EGT", $fromtime);
					}
					if ($fromtime && $todate) {
						$totime = strtotime($todate . ' 23:59:59');
						$where["inputtime"] = array(array("EGT", $fromtime), array("ELT", $fromtime), 'and');
					}
				} elseif ($type == 'id') {
//以id段更新
					//起始id
					$fromid = intval($fromid);
					//结束id
					$toid = intval($toid);
					if ($fromid) {
						$where["id"] = array("EGT", $fromid);
					}
					if ($fromid && $toid) {
						$where["id"] = array(array("EGT", $fromid), array("ELT", $toid), 'and');
					}
				}
				if (!isset($total) && $type != 'lastinput') {
					//统计总数
					$rs = $model->where($where)->count();
					$total = $rs;
					$pages = ceil($total / $pagesize);
					$start = 1;
				}
				$data = $model->where($where)->order(array("id" => $order))->limit($offset . "," . $pagesize)->select();
				foreach ($data as $r) {
					if ($r['islink']) {
						continue;
					}

					//更新URL链接
					$this->urls($r);
				}
				if ($pages > $page) {
					$page++;
					$http_url = __SELF__;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "共需更新 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = $start ? U("Content/Createhtml/update_urls", "type={$type}&dosubmit=1&first={$first}&fromid={$fromid}&toid={$toid}&fromdate={$fromdate}&todate={$todate}&pagesize={$pagesize}&page={$page}&pages={$pages}&total={$total}&modelid={$modelid}") : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page={$page}&pages={$pages}&total={$total}", $http_url);

					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
				} else {
					//删除缓存
					F("url_show_" . \Admin\Service\User::getInstance()->id, NULL);
					$this->success("更新完成！ ...", U("Content/Createhtml/update_urls"));
				}
			} else {
				//当没有选择模型时，需要按照栏目来更新
				if (!isset($set_catid)) {
					if ($catids[0] != 0) {
						$update_url_catids = $catids;
					} else {
						foreach ($this->categorys as $catid => $cat) {
							$cat = getCategory($cat['catid']);
							if ($cat['child'] || $cat['type'] != 0) {
								continue;
							}

							$update_url_catids[] = $catid;
						}
					}

					//生成需要更新生成的栏目ID缓存
					F("update_url_catid" . \Admin\Service\User::getInstance()->id, $update_url_catids);
					$this->assign("waitSecond", 200);
					$this->success("开始更新 ...", U("Content/Createhtml/update_urls", "set_catid=1&pagesize={$pagesize}&dosubmit=1"));
					exit;
				}
				$catid_arr = F("update_url_catid" . \Admin\Service\User::getInstance()->id);
				$autoid = $autoid ? intval($autoid) : 0;
				if (!isset($catid_arr[$autoid])) {
					$this->success("更新完成！ ...", U("Content/Createhtml/update_urls"));
					exit;
				}

				$catid = $catid_arr[$autoid];
				$modelid = getCategory($catid, 'modelid');
				$model = ContentModel::getInstance($modelid);

				$page = max(intval($page), 1);
				$offset = $pagesize * ($page - 1);
				$where = array();
				$where['status'] = array("EQ", 99);
				$where['catid'] = array("EQ", $catid);
				$order = 'ASC';

				if (!isset($total)) {
					//统计总数
					$rs = $model->where($where)->count();
					$total = $rs;
					$pages = ceil($total / $pagesize);
					$start = 1;
				}

				$data = $model->where($where)->order(array("id" => $order))->limit($offset . "," . $pagesize)->select();
				foreach ($data as $r) {
					if ($r['islink']) {
						continue;
					}

					//更新URL链接
					$this->urls($r);
				}

				if ($pages > $page) {
					$page++;
					$http_url = __SELF__;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "【" . getCategory($catid, 'catname') . "】 有 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = $start ? U("Content/Createhtml/update_urls", "type={$type}&dosubmit=1&first={$first}&fromid={$fromid}&toid={$toid}&fromdate={$fromdate}&todate={$todate}&pagesize={$pagesize}&page={$page}&pages={$pages}&total={$total}&autoid={$autoid}&set_catid=1") : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page={$page}&pages={$pages}&total={$total}", $http_url);
					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
				} else {
					$autoid++;
					$forward = U("Content/Createhtml/update_urls", "set_catid=1&pagesize={$pagesize}&dosubmit=1&autoid={$autoid}");
					$this->assign("waitSecond", 200);
					$this->success("开始更新 .." . getCategory($catid, 'catname') . " ...", $forward);
				}
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$this->Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$this->Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array();
			if (!empty($this->categorys)) {
				foreach ($this->categorys as $catid => $r) {
					$r = getCategory($r['catid']);
					if ($r['type'] != 0 && $r['child'] == 0) {
						continue;
					}

					if ($modelid && $modelid != $r['modelid']) {
						continue;
					}

					$r['disabled'] = $r['child'] ? 'disabled' : '';
					$categorys[$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$this->Tree->init($categorys);
			$string .= $this->Tree->get_tree(0, $str);
			$this->assign("models", $this->model);
			$this->assign("string", $string);
			$this->assign("modelid", $modelid);
			$this->display();
		}
	}

	//根据栏目/模型批量更新内容页
	public function update_show() {
		if (isset($_POST['dosubmit'])) {
			extract($_POST, EXTR_SKIP);
			//模型ID
			$modelid = intval($_POST['modelid']);
			if ($modelid) {
				$model = ContentModel::getInstance($modelid);
				//更新最新发布的X条信息
				if ($type == 'lastinput') {
					$offset = 0;
				} else {
					$page = max(intval($page), 1);
					$offset = $pagesize * ($page - 1);
				}
				$where = array();
				$order = 'ASC';
				//设置状态
				$where["status"] = array("EQ", 99);

				if (!isset($first) && is_array($catids) && $catids[0] > 0) {
					//把选择需要生成的栏目id写入缓存
					F("html_show_" . \Admin\Service\User::getInstance()->id, $catids);
					$catids = implode(',', $catids);
					$where['catid'] = array("IN", $catids);
					$first = 1;
				} elseif (count($catids) == 1 && $catids[0] == 0) {
//不限制栏目
					$catids = array();
					foreach ($this->categorys as $catid => $cat) {
						$cat = getCategory($cat['catid']);
						if ($cat['child'] || $cat['type'] != 0) {
							continue;
						}

						$setting = $cat['setting'];
						if (!$setting['content_ishtml']) {
							continue;
						}

						$catids[] = $catid;
					}
					//把选择需要生成的栏目id写入缓存
					F("html_show_" . \Admin\Service\User::getInstance()->id, $catids);
					$catids = implode(',', $catids);
					$where['catid'] = array("IN", $catids);
					$first = 1;
				} elseif ($first) {
					//获取缓存
					$catids = F("html_show_" . \Admin\Service\User::getInstance()->id);
					$catids = implode(',', $catids);
					$where['catid'] = array("IN", $catids);
				} else {
					$first = 0;
				}

				if (count($catids) == 1 && $catids[0] == 0) {
					$this->assign("waitSecond", 200);
					$this->success("更新完成...", U("Content/Createhtml/update_show"));
					exit;
				}

				//更新最新发布的
				if ($type == 'lastinput' && $number) {
					$offset = 0;
					//获取更新最新发布的多少条
					$pagesize = $number;
					$order = 'DESC';
				} elseif ($type == 'date') {
//以时间段更新
					if ($fromdate) {
						$fromtime = strtotime($fromdate . ' 00:00:00');
						$where["inputtime"] = array("EGT", $fromtime);
					}
					if ($fromdate && $todate) {
						$totime = strtotime($todate . ' 23:59:59');
						$where["inputtime"] = array(array("EGT", $fromtime), array("ELT", $fromtime), 'and');
					}
				} elseif ($type == 'id') {
//以id段更新
					//起始id
					$fromid = intval($fromid);
					//结束id
					$toid = intval($toid);
					if ($fromid) {
						$where["id"] = array("EGT", $fromid);
					}
					if ($fromid && $toid) {
						$where["id"] = array(array("EGT", $fromid), array("ELT", $toid), 'and');
					}
				}

				if (!isset($total) && $type != 'lastinput') {
					//统计总数
					$rs = $model->where($where)->count();
					$total = $rs;
					$pages = ceil($total / $pagesize);
					$start = 1;
				}

				$data = $model->relation(true)->where($where)->order(array("id" => $order))->limit($offset . "," . $pagesize)->select();

				foreach ($data as $r) {
					//转向地址信息无需生成
					if ($r['islink']) {
						continue;
					}
					$model->dataMerger($r);
					$this->Html->show($r);
				}

				if ($pages > $page) {
					$page++;
					$http_url = __SELF__;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "共需更新 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = $start ? U("Content/Createhtml/update_show", "type={$type}&dosubmit=1&first={$first}&fromid={$fromid}&toid={$toid}&fromdate={$fromdate}&todate={$todate}&pagesize={$pagesize}&page={$page}&pages={$pages}&total={$total}&modelid={$modelid}") : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page={$page}&pages={$pages}&total={$total}", $http_url);
					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
				} else {
					//删除缓存
					F("html_show_" . \Admin\Service\User::getInstance()->id, NULL);
					$this->success("更新完成！ ...", U("Content/Createhtml/update_show"));
				}
			} else {
				//当没有选择模型时，需要按照栏目来更新
				if (!isset($set_catid)) {
					if ($catids[0] != 0) {
						$update_url_catids = $catids;
					} else {
						foreach ($this->categorys as $catid => $cat) {
							$cat = getCategory($cat['catid']);
							if ($cat['child'] || $cat['type'] != 0) {
								continue;
							}

							$setting = $cat['setting'];
							if (!$setting['content_ishtml']) {
								continue;
							}

							$update_url_catids[] = $catid;
						}
					}

					//生成需要更新生成的栏目ID缓存
					F("update_html_catid" . \Admin\Service\User::getInstance()->id, $update_url_catids);
					$this->assign("waitSecond", 200);
					$this->success("开始更新 ...", U("Content/Createhtml/update_show", "set_catid=1&pagesize={$pagesize}&dosubmit=1"));
					exit;
				}

				if (count($catids) == 1 && $catids[0] == 0) {
					$this->success("更新完成！ ...", U("Content/Createhtml/update_show"));
					exit;
				}

				$catid_arr = F("update_html_catid" . \Admin\Service\User::getInstance()->id);

				$autoid = $autoid ? intval($autoid) : 0;
				if (!isset($catid_arr[$autoid])) {
					$this->success("更新完成！ ...", U("Content/Createhtml/update_show"));
					exit;
				}

				$catid = $catid_arr[$autoid];
				$modelid = getCategory($catid, 'modelid');
				$model = ContentModel::getInstance($modelid);

				$page = max(intval($page), 1);
				$offset = $pagesize * ($page - 1);
				$where = array();
				$where['status'] = array("EQ", 99);
				$where['catid'] = array("EQ", $catid);
				$order = 'ASC';

				if (!isset($total)) {
					//统计总数
					$rs = $model->where($where)->count();
					$total = $rs;
					$pages = ceil($total / $pagesize);
					$start = 1;
				}

				$data = $model->relation(true)->where($where)->order(array("id" => $order))->limit($offset . "," . $pagesize)->select();
				foreach ($data as $r) {
					if ($r['islink']) {
						continue;
					}

					$model->dataMerger($r);
					$this->Html->show($r);
				}

				if ($pages > $page) {
					$page++;
					$http_url = __SELF__;
					$creatednum = $offset + count($data);
					$percent = round($creatednum / $total, 2) * 100;
					$message = "【" . getCategory($catid, 'catname') . "】 有 <font color=\"red\">{$total}</font> 条信息 - 已完成 <font color=\"red\">{$creatednum}</font> 条（<font color=\"red\">{$percent}%</font>）";
					$forward = $start ? U("Content/Createhtml/update_show", "type={$type}&dosubmit=1&first={$first}&fromid={$fromid}&toid={$toid}&fromdate={$fromdate}&todate={$todate}&pagesize={$pagesize}&page={$page}&pages={$pages}&total={$total}&autoid={$autoid}&set_catid=1") : preg_replace("/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page={$page}&pages={$pages}&total={$total}", $http_url);
					$this->assign("waitSecond", 200);
					$this->success($message, $forward);
				} else {
					$autoid++;
					$forward = U("Content/Createhtml/update_show", "set_catid=1&pagesize={$pagesize}&dosubmit=1&autoid={$autoid}");
					$this->assign("waitSecond", 200);
					$this->success("开始更新 .." . getCategory($catid, 'catname') . " ...", $forward);
				}
			}
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$this->Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$this->Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array();
			if (!empty($this->categorys)) {
				foreach ($this->categorys as $catid => $r) {
					$r = getCategory($r['catid']);
					if ($r['type'] != 0 && $r['child'] == 0) {
						continue;
					}

					if ($modelid && $modelid != $r['modelid']) {
						continue;
					}

					if ($r['child'] == 0) {
						$setting = $r['setting'];
						if (!$setting['content_ishtml']) {
							continue;
						}

					}
					$r['disabled'] = $r['child'] ? 'disabled' : '';
					$categorys[$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$this->Tree->init($categorys);
			$string .= $this->Tree->get_tree(0, $str);
			$this->assign("models", $this->model);
			$this->assign("string", $string);
			$this->assign("modelid", $modelid);
			$this->display();
		}
	}

	//根据id批量生成内容页
	public function batch_show() {
		if (IS_POST) {
			$catid = intval($_GET['catid']);
			if (!$catid) {
				$this->error("栏目ID不能为空！");
			}
			$modelid = getCategory($catid, 'modelid');
			$setting = getCategory($catid, 'setting');
			$content_ishtml = $setting['content_ishtml'];
			if ($content_ishtml) {
				$model = ContentModel::getInstance($modelid);
				if (empty($_POST['ids'])) {
					$this->error("您没有勾选信息！");
				}
				$ids = implode(',', $_POST['ids']);
				$where = array();
				$where['catid'] = array("EQ", $catid);
				$where['id'] = array("IN", $ids);
				$where['status'] = array("EQ", 99);
				$rs = $model->relation(true)->where($where)->select();
				foreach ($rs as $r) {
					$model->dataMerger($r);
					if ($r['islink']) {
						continue;
					}

					if ($r['status'] != 99) {
						continue;
					}

					$this->Html->show($r);
				}
				$this->success("生成成功！");
			} else {
				$this->error("该栏目无需生成！");
			}
		} else {
			$catid = intval($_GET['catid']);
			if (!$catid) {
				$this->error("栏目ID不能为空！");
			}
			$modelid = getCategory($catid, 'modelid');
			$setting = getCategory($catid, 'setting');
			$content_ishtml = $setting['content_ishtml'];
			if ($content_ishtml) {
				$model = ContentModel::getInstance($modelid);
				if (empty($_GET['ids'])) {
					$this->error("您没有勾选信息！");
				}
				$ids = (int) $_GET['ids'];
				$where = array();
				$where['catid'] = array("EQ", $catid);
				$where['id'] = array("EQ", $ids);
				$where['status'] = array("EQ", 99);
				$r = $model->relation(true)->where($where)->find();
				//数据处理，把关联查询的结果集合并
				$model->dataMerger($r);
				if ($r['status'] != 99) {
					$this->error("该信息未审核！无需生成");
				}
				if ($r['islink']) {
					$this->error("链接文章无需生成！");
				} else {
					$this->Html->show($r);
				}
				$this->success("生成成功！");
			} else {
				$this->error("该栏目无需生成！");
			}
		}
	}

	/**
	 * 执行URL更新操作
	 * @param type $data 数据
	 * @return boolean
	 */
	private function urls($data) {
		if (!$data['inputtime'] || !$data['id'] || !$data['catid']) {
			return false;
		}
		$urls = $this->Url->show($data);
		ContentModel::getInstance(getCategory($data['catid'], 'modelid'))->where(array('id' => $data['id']))->save(array('url' => $urls['url']));
		return $urls['url'];
	}

}
