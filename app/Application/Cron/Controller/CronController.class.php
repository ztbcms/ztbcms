<?php

// +----------------------------------------------------------------------
// | 计划任务
// +----------------------------------------------------------------------

namespace Cron\Controller;

use Common\Controller\AdminBase;

class CronController extends AdminBase {

	private $db;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D("Cron/Cron");
	}

	public function index() {
		$count = $this->db->count();
		$page = $this->page($count, 20);
		$data = $this->db->limit($page->firstRow . ',' . $page->listRows)->order(array("cron_id" => "DESC"))->select();
		//created_time 上次执行时间
		//next_time 下次执行时间
		foreach ($data AS $key => &$cron) {
			$cron['type'] = $this->db->_getLoopType($cron['loop_type']);
			list($day, $hour, $minute) = explode('-', $cron['loop_daytime']);
			if ($cron['loop_type'] == 'week') {
				$cron['type'] .= '星期' . $this->db->_capitalWeek($day);
			} elseif ($day == 99) {
				$cron['type'] .= '最后一天';
			} else {
				$cron['type'] .= $day ? $day . '日' : '';
			}
			if ($cron['loop_type'] == 'week' || $cron['loop_type'] == 'month') {
				$cron['type'] .= $hour . '时';
			} else {
				$cron['type'] .= $hour ? $hour . '时' : '';
			}

			$cron['type'] .= $minute ? $minute . '分' : '00分';
		}

		$this->assign("data", $data);
		$this->assign("Page", $page->show());
		$this->display();
	}

	//添加计划任务
	public function add() {
		if (IS_POST) {
			if ($this->db->CronAdd($_POST)) {
				$this->success("计划任务添加成功！", U("Cron/index"));
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$tree = new \Tree();
			//栏目列表 可以用缓存的方式
			$array = cache("Category");
			foreach ($array as $catid => $r) {
				$r = getCategory($r['catid']);
				if ($r['type'] == 2) {
					continue;
				}

				if (!$r['sethtml']) {
					unset($array[$catid]);
					continue;
				}
				$array[$catid] = $r;
			}
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$tree->init($array);
			$catidList = '<select name="catid[]" size="16" multiple="" style="width:350px;" id="catid">' . $tree->get_tree(0, $str, $parentid) . '</select>';
			$db = M('Customtemp');
			$array = $db->order(array("tempid" => "DESC"))->getField("tempid,name");
			$customtempList = \Form::select($array, '', 'name="tempid[]" size="16" multiple="" style="width:350px;" id="catid"');
			$this->assign("fileList", $this->db->_getCronFileList());
			$this->assign("catidList", $catidList);
			$this->assign("customtempList", $customtempList);
			$this->display();
		}
	}

	//编辑
	public function edit() {
		if (IS_POST) {
			if ($this->db->CronEdit($_POST)) {
				$this->success("修改成功！", U("Cron/index"));
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$cron_id = I('get.cron_id', 0, 'intval');
			$info = $this->db->where(array("cron_id" => $cron_id))->find();
			if (!$info) {
				$this->error("该计划任务不存在！");
			}
			list($info['day'], $info['hour'], $info['minute']) = explode('-', $info['loop_daytime']);
			$tree = new \Tree();
			//栏目列表 可以用缓存的方式
			$array = cache("Category");
			$catidarray = explode(",", $info['data']);
			foreach ($array as $catid => $r) {
				$r = getCategory($r['catid']);
				if ($r['type'] == 2) {
					continue;
				}

				if (!$r['sethtml']) {
					unset($array[$catid]);
					continue;
				}
				if (in_array($catid, $catidarray)) {
					$r['selected'] = 'selected';
				}
				$array[$catid] = $r;
			}
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$tree->init($array);
			$catidList = '<select name="catid[]" size="16" multiple="" style="width:350px;" id="catid">' . $tree->get_tree(0, $str, $parentid) . '</select>';
			$db = M('Customtemp');
			$array = $db->order(array("tempid" => "DESC"))->getField("tempid,name");
			$customtempList = \Form::select($array, $info['data'], 'name="tempid[]" size="16" multiple="" style="width:350px;" id="catid"');

			$this->assign($info);
			$this->assign("loopType", $this->db->_getLoopType());
			$this->assign("fileList", $this->db->_getCronFileList());
			$this->assign("catidList", $catidList);
			$this->assign("customtempList", $customtempList);
			$this->display();
		}
	}

	//删除
	public function delete() {
		$cron_id = I('get.cron_id', 0, 'intval');
		$info = $this->db->where(array("cron_id" => $cron_id))->delete();
		if ($info !== false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}

    //立即执行计划任务
    public function runAction(){
        $IndexController = new \Cron\Controller\IndexController();
        $cron_id = I('get.cron_id');
        $cron_file = I('get.cron_file');

        $res = $IndexController->runAction($cron_file, $cron_id);
    }

}
