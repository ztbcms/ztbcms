<?php

// +----------------------------------------------------------------------
// | 投票管理
// +----------------------------------------------------------------------

namespace Vote\Controller;

use Common\Controller\AdminBase;

class VoteController extends AdminBase {

	protected $filepath, $tp_Vote;

	function _initialize() {
		parent::_initialize();
		$this->filepath = TEMPLATE_PATH . "Default/Vote/";
		$this->tp_Vote = str_replace($this->filepath, "", glob($this->filepath . "Index" . DIRECTORY_SEPARATOR . 'vote*'));
		$this->tp_Vote = str_replace("Index" . DIRECTORY_SEPARATOR, "", $this->tp_Vote);
		C('HTML_FILE_SUFFIX', "");
	}

	public function index() {
		$db = M("Vote");
		$count = $db->where($where)->count();
		$page = $this->page($count, 20);
		$data = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("subjectid" => "DESC"))->select();
		$this->assign("Page", $page->show());
		$this->assign("menuid", I('get.menuid'));
		$this->assign("data", $data);
		$this->display();
	}

	/**
	 * 添加
	 */
	public function add() {
		if (IS_POST) {
			//记录选项条数 optionnumber
			$_POST['subject']['optionnumber'] = count($_POST['option']);
			$_POST['subject']['template'] = $_POST['vote_subject']['vote_tp_template'];
			$_POST['subject']['template'] = str_replace(C("TMPL_TEMPLATE_SUFFIX"), "", $_POST['subject']['template']);
			$_POST['subject'][C("TOKEN_NAME")] = $_POST[C("TOKEN_NAME")];
			//配置
			$_POST['subject']['setting'] = serialize($_POST['setting']);
			$db = D("Vote/Vote");
			$data = $db->create($_POST['subject']);
			if ($data) {
				$subjectid = $db->VoteAdd($data, $_POST['option']);
				if (!$subjectid) {
					$this->error("添加失败！");
				}
				$this->success("添加成功！", U("Vote/index"));
			} else {
				$this->error($db->getError());
			}
		} else {
			//模版
			$template_list = $this->tp_Vote;
			$template_list = str_replace(C("TMPL_TEMPLATE_SUFFIX"), "", $template_list);
			$this->assign("template_list", $template_list);
			$this->display();
		}
	}

	/**
	 * 说明: 查询 该投票的 选项
	 * @param $subjectid 投票ID
	 */
	protected function get_options($subjectid, $order = array("optionid" => "ASC")) {
		if (!$subjectid) {
			return FALSE;
		}

		$db = M("VoteOption");
		$data = $db->where(array("subjectid" => $subjectid))->order($order)->select();
		return $data;
	}

	/**
	 * 说明:删除单条对应ID的投票选项记录
	 * @param $optionid 投票选项ID
	 */
	public function public_ajax_option($optionid) {
		if (!$optionid) {
			$optionid = I('get.optionid');
		}
		if (!$optionid) {
			exit('参数不正确！');
		}

		if (M("VoteOption")->where(array("optionid" => $optionid))->delete()) {
			exit('1');
		} else {
			exit('删除失败！');
		}
	}

	/**
	 * 投票结果统计
	 */
	public function statistics() {
		$subjectid = intval($_GET['subjectid']);
		if (!$subjectid) {
			$this->error("投票不存在！");
		}
		//取出投票标题
		$subject_arr = $this->get_subject($subjectid);
		$subject_arr['setting'] = unserialize($subject_arr['setting']);
		$show_validator = $show_scroll = $show_header = true;
		$order = array();
		switch ((int) $subject_arr['setting']['order']) {
			case 0:
				$order = array("optionid" => "ASC");
				break;
			case 1:
				$order = array("stat" => "ASC");
				break;
			case 2:
				$order = array("stat" => "DESC");
				break;
			default:
				$order = array("optionid" => "ASC");
				break;
		}
		//取投票选项
		$options = $this->get_options($subjectid, $order);
		//获取投票人数
		$count = M('VoteData')->where(array("subjectid" => $subjectid))->count();
		//新建一数组用来存新组合数据
		$total = 0;
		$vote_data = array();
		$vote_data['total'] = 0; //所有投票选项总数
		$vote_data['votes'] = $count; //投票人数
		//循环每个会员的投票记录
		foreach ($options as $subjectid_arr) {
			//所以票数相加
			$vote_data['total'] = $vote_data['total'] + $subjectid_arr['stat'];
		}

		//模板变量输出
		$this->assign("vote_data", $vote_data);
		$this->assign("options", $options);
		$this->assign("display", $display);
		$this->assign("check_status", $check_status);
		$this->assign("subjectid", $subjectid);
		$this->assign("subject_arr", $subject_arr);
		$this->display();
	}

	/**
	 * 投票会员统计
	 */
	public function userlist() {
		$subjectid = $_GET['subjectid'];
		if (empty($subjectid)) {
			return false;
		}

		$where["subjectid"] = $subjectid;
		$db = M("VoteData");
		$count = $db->where($where)->count();
		$page = $this->page($count, 10);
		$data = $db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("time" => "DESC"))->select();
		$this->assign("Page", $page->show('Admin'));
		$this->assign("data", $data);
		$this->assign("subjectid", $subjectid);
		$this->display();
	}

	/**
	 * 说明: 取得投票信息, 返回数组
	 * @param $subjectid 投票ID
	 */
	protected function get_subject($subjectid) {
		if (!$subjectid) {
			return FALSE;
		}

		M("Vote")->where("subjectid=$subjectid")->find();
		return M("Vote")->where("subjectid=$subjectid")->find();
	}

	/**
	 * 说明:生成JS投票代码
	 * @param $subjectid 投票ID
	 */
	protected function update_votejs($subjectid) {
		if (!isset($subjectid) || intval($subjectid) < 1) {
			return false;
		}

		//解出投票内容
		$info = $this->get_subject($subjectid);
		if (!$info) {
			$this->error("没有此投票");
		}

		extract($info);
		//取投票选项
		$options = $this->get_options($subjectid);
		//获取投票人数
		$count = M('VoteData')->where(array("subjectid" => $subjectid))->count();
		//新建一数组用来存新组合数据
		$total = 0;
		$vote_data = array();
		$vote_data['total'] = 0; //所有投票选项总数
		$vote_data['votes'] = $count; //投票人数
		//循环每个会员的投票记录
		foreach ($options as $subjectid_arr) {
			//所以票数相加
			$vote_data['total'] = $vote_data['total'] + $subjectid_arr['stat'];
		}

		//取出投票结束时间，如果小于当前时间，则选项变灰不可选
		if (date("Y-m-d", time()) > $info['todate']) {
			$check_status = 'disabled';
			$display = 'display:none;';
		} else {
			$check_status = '';
		}

		//模板变量输出
		$this->assign("vote_data", $vote_data);
		$this->assign("subject", $subject);
		$this->assign("options", $options);
		$this->assign("display", $display);
		$this->assign("check_status", $check_status);
		$this->assign("subjectid", $subjectid);
		$this->assign("subject_arr", $subject_arr);
		//默认模板
		if (empty($info["template"])) {
			$info["template"] = 'vote_tp';
		}
		///++++++++++++++++++
		$content = $this->fetch($this->filepath . "Index/" . $info["template"] . C("TMPL_TEMPLATE_SUFFIX"));
		//字符处理
		$content = $this->format_js($content);
		//生成路径
		$htmlpath = SITE_PATH . "/d/vote_js/";
		//生成文件名
		$htmlfile = $htmlpath . 'vote_' . $subjectid . '.js';
		if (!is_dir(dirname($htmlfile)))
		// 如果静态目录不存在 则创建
		{
			mkdir(dirname($htmlfile));
		}

		if (false === file_put_contents($htmlfile, $content)) {
			throw_exception(L('_CACHE_WRITE_ERROR_') . ':' . $htmlfile);
		}

	}

	/**
	 * 更新js
	 */
	public function vot_js() {
		$infos = M('Vote')->select();
		if (is_array($infos)) {
			foreach ($infos as $subjectid_arr) {
				$this->update_votejs($subjectid_arr['subjectid']);
			}
		}
		$this->success("操作成功！", U("Vote/index"));
	}

	/**
	 * 说明:对字符串进行处理
	 * @param $string 待处理的字符串
	 * @param $isjs 是否生成JS代码
	 */
	protected function format_js($string, $isjs = 1) {
		$string = addslashes(str_replace(array("\r", "\n"), array('', ''), $string));
		return $isjs ? 'document.write("' . $string . '");' : $string;
	}

	/**
	 * 投票调用代码
	 */
	public function public_call() {
		$subjectid = I('get.subjectid', 0, 'intval');
		if (!$subjectid) {
			$this->error("没有此投票");
		}

		$r = M('Vote')->where("subjectid=$subjectid")->find();
		$this->assign("r", $r);
		$this->assign("subjectid", $subjectid);
		$this->display('call');
	}

	/**
	 * 信息选择投票接口
	 */
	public function public_get_votelist() {
		$db = M("Vote");
		$count = $db->count();
		$page = $this->page($count, 10);
		$infos = $db->limit($page->firstRow . ',' . $page->listRows)->order(array("subjectid" => "DESC"))->select();
		$this->assign("Page", $page->show('Admin'));
		$this->assign("infos", $infos);
		$this->assign("subjectid", $subjectid);
		$this->display("get_votelist");
	}

	/**
	 * 编辑投票
	 */
	public function edit() {
		$subjectid = $_GET['subjectid'];
		if (IS_POST) {
			//验证数据正确性
			if ($subjectid < 1) {
				$this->error("参数不正确！");
			}

			if (!is_array($_POST['subject']) || empty($_POST['subject'])) {
				$this->error("请填写投票标题！");
			}

			if ((!$_POST['subject']['subject']) || empty($_POST['subject']['subject'])) {
				$this->error("请填写投票标题！");
			}

			//先更新已有 投票选项,再添加新增加投票选项
			D("Vote/Vote_option")->update_options($_POST['option']);

			if (is_array($_POST['newoption']) && !empty($_POST['newoption'])) {
				D("Vote/Vote")->add_options($_POST['newoption'], $subjectid);
			}
			//模版
			$_POST['subject']['template'] = $_POST['vote_subject']['vote_tp_template'];
			$_POST['subject']['template'] = str_replace(C("TMPL_TEMPLATE_SUFFIX"), "", $_POST['subject']['template']);
			$_POST['subject']['optionnumber'] = count($_POST['option']) + count($_POST['newoption']);
			//配置
			$_POST['subject']['setting'] = serialize($_POST['setting']);

			D('Vote/Vote')->where(array("subjectid" => $subjectid))->save($_POST['subject']); //更新投票选项总数
			$this->update_votejs($subjectid); //生成JS文件
			$this->success("修改成功！", U("Vote/index"));
		} else {
			if ($subjectid < 1) {
				return false;
			}

			//解出投票内容
			$info = $this->get_subject($subjectid);
			$info['setting'] = unserialize($info['setting']);
			if (!$info) {
				$this->error("没有此投票");
			}

			extract($info);

			//解出投票选项
			$options = $this->get_options($subjectid);

			//模版
			$template_list = $this->tp_Vote;
			$template_list = str_replace(C("TMPL_TEMPLATE_SUFFIX"), "", $template_list);
			$this->assign("subjectid", $subjectid);
			$this->assign("options", $options);
			$this->assign("template_list", $template_list);
			$this->assign("info", $info);
			$this->display();
		}
	}

	/**
	 * 删除投票
	 * @param	intval	$sid	投票的ID，递归删除
	 */
	public function delete() {
		if ((!isset($_GET['subjectid']) || empty($_GET['subjectid'])) && (!isset($_POST['subjectid']) || empty($_POST['subjectid']))) {
			if (!$info) {
				$this->error("没有此投票");
			}

		} else {
			if (is_array($_POST['subjectid'])) {
				foreach ($_POST['subjectid'] as $subjectid_arr) {
					D("Vote/Vote")->VoteDelete((int) $subjectid_arr);
				}
				$this->success("删除成功！", U("Vote/index"));
			} else {
				$subjectid = intval($_GET['subjectid']);
				if ($subjectid < 1) {
					return false;
				}

				if (D("Vote/Vote")->VoteDelete((int) $subjectid)) {
					$this->success("删除成功！", U("Vote/index"));
				} else {
					$this->error("删除失败!");
				}
			}
		}
	}

	/**
	 * 清除投票数据
	 * @param	intval	$subjectid
	 */
	public function clearvote() {
		$subjectid = I('get.subjectid', 0, 'intval');
		if ($subjectid < 1) {
			$this->error("请指定需要清除投票数据的投票ID!");
		}

		if (D("Vote/Vote")->ClearStatistics($subjectid)) {
			$this->success("清除成功！", U("Vote/index", array("menuid" => I('get.menuid', 0, 'intval'))));
		} else {
			$this->error("清除失败!");
		}
	}

}
