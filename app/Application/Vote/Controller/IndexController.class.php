<?php

// +----------------------------------------------------------------------
// | 投票
// +----------------------------------------------------------------------

namespace Vote\Controller;

use Common\Controller\Base;

class IndexController extends Base {

	private $userid, $username, $groupid;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->userid = service("Passport")->userid;
		$this->username = service("Passport")->username;
		$this->groupid = service("Passport")->groupid;
	}

	/**
	 * 投票列表页
	 */
	public function index() {
		$this->basePage(M("Vote"), array(), array("subjectid" => "DESC"));
	}

	/**
	 * 投票显示页
	 */
	public function show() {
		$type = intval($_GET['type']); //调用方式ID
		$subjectid = abs(intval($_GET['subjectid']));
		if (!$subjectid) {
			$this->error("缺少ID");
		}
		//取出投票标题
		$subject_arr = $this->get_subject($subjectid);
		$subject_arr['setting'] = unserialize($subject_arr['setting']);
		//增加判断，防止模板调用不存在投票时js报错 wangtiecheng
		if (!is_array($subject_arr)) {
			if (isset($_GET['action']) && $_GET['action'] == 'js') {
				exit;
			} else {
				$this->error("投票不存在");
			}
		}
		extract($subject_arr);
		//显示模版
		$template = $template ? $template : 'vote_tp';
		//排序
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
		//获取投票选项
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

		//取出投票结束时间，如果小于当前时间，则选项变灰不可选
		if (date("Y-m-d", time()) > $todate) {
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
		//JS调用
		if ($_GET['action'] == 'js') {
			//$template = 'submit';
			$template = $subject_arr['template'];
			//根据TYPE值，判断调用模版
			switch ($type) {
				case 3: //首页、栏目页调用
					$true_template = 'vote_tp_3';
					break;
				case 2: //内容页调用
					$true_template = 'vote_tp_2';
					break;
				default:
					$true_template = $template;
			}
			//获取模板路径
			$filepath = TEMPLATE_PATH . (empty(AppframeAction::$Cache["Config"]['theme']) ? "Default" : AppframeAction::$Cache["Config"]['theme']) . "/Vote/";
			$html = $this->fetch($filepath . "Index/" . $true_template . C("TMPL_TEMPLATE_SUFFIX"));
			$dojs = $this->format_js($html); //输出js
			exit($dojs);
		}
		$this->display($template);
	}

	/**
	 *
	 * 投票前检测
	 * @param $subjectid 投票ID
	 * @return 返回值 (1:可投票  0: 多投,时间段内不可投票  -1:单投,已投票,不可重复投票)
	 */
	public function check($subjectid) {
		//查询本投票配置
		$subject_arr = $this->get_subject($subjectid);
		if ($subject_arr['enabled'] == 0) {
			$this->error("投票锁定中!暂不能投票");
		}

		//投票是否开始
		if (date("Y-m-d", time()) < $subject_arr['fromdate']) {
			$this->error("投票还没开始！");
		}
		//投票是否过期
		if (date("Y-m-d", time()) > $subject_arr['todate']) {
			$this->error("投票已过期！");
		}
		//游客是否可以投票
		if ($subject_arr['allowguest'] == 0) {
			if (!$this->username) {
				$this->error("对不起,不允许游客投票！");
			}
		}

		//是否有投票记录
		if ($this->username) {
			$where['username'] = $this->username;
		}
		$where['subjectid'] = $subjectid;
		$where['ip'] = $this->ip();
		$user_info = M('VoteData')->where($where)->order(array("time" => "DESC"))->find();
		if (!is_array($user_info)) {
			return 1;
		} else {
			if ($subject_arr['interval'] == 0) {
				return -1;
			}
			if ($subject_arr['interval'] > 0) {
				if ((time() - $user_info['time']) / 60 > $subject_arr['interval']) {
					return 1; //如果投票时间间隔满足
				} else {
					return -1; //如果不满足
				}
			}
		}
	}

	/**
	 * 处理投票
	 */
	public function post() {
		$subjectid = intval($_POST['subjectid']);
		if (!$subjectid) {
			$this->error("投票不存在！");
		}

		//查询投票信息
		$vote_arr = M("Vote")->where(array("subjectid" => $subjectid))->find();
		if (!$vote_arr) {
			$this->error("投票不存在！");
		}

		//判断是否已投过票,或者尚未到第二次投票期
		$return = $this->check($subjectid);
		switch ($return) {
			case 0:
				$this->error("你已经投过票!");
				break;
			case -1:
				$this->error("你已经投过票!");
				break;
		}
		if (!is_array($_POST['radio'])) {
			$this->error("没有选择投票选项!");
		}
		//检查投票项总数
		if ($vote_arr['ischeckbox']) {
			//最少选项
			$minval = (int) $vote_arr['minval'];
			//最多选项
			$maxval = (int) $vote_arr['maxval'];
			$count = count($_POST['radio']);
			if ($count < $minval) {
				$this->error("至少需要选择[<font color=\"#FF0000\">{$minval}</font>]项进行投票！");
			}
			if ($count > $maxval) {
				$this->error("至多只能选择[<font color=\"#FF0000\">{$maxval}</font>]项进行投票！");
			}
		}
		$time = time();
		$data_arr = array();
		foreach ($_POST['radio'] as $radio) {
			$data_arr[$radio] = '1';
		}

		$new_data = $this->array2string($data_arr); //转成字符串存入数据库中
		//exit($new_data);
		//添加到数据库
		if ($this->userid) {
			$data["userid"] = $this->userid;
			$data["username"] = $this->username;
		}
		$data["subjectid"] = $subjectid;
		$data["time"] = time();
		$data["ip"] = $this->ip();
		$data["data"] = $new_data;
		M('VoteData')->add($data);
		foreach ($data_arr as $id => $v) {
			M("VoteOption")->where(array("optionid" => $id, "subjectid" => $subjectid))->setInc('stat');
		}

		//积分操作
		if ((int) $vote_arr['credit']) {
			service("Passport")->user_integral($this->userid, (int) $vote_arr['credit']);
		}
		M('Vote')->where(array("subjectid" => $subjectid))->setInc('votenumber', 1);
		$this->success("投票成功,正在返回！", U("Vote/index/result", array("subjectid" => $subjectid)));
	}

	/**
	 *
	 * 投票结果显示
	 */
	public function result() {
		$subjectid = abs(intval($_GET['subjectid']));
		if (!$subjectid) {
			$this->error("投票不存在!");
		}

		//取出投票标题
		$subject_arr = $this->get_subject($subjectid);
		$subject_arr['setting'] = unserialize($subject_arr['setting']);
		if (!is_array($subject_arr)) {
			$this->error("投票不存在!");
		}

		if ($subject_arr['allowview'] == 0) {
			$this->error("投票结果还未公布!");
		}

		extract($subject_arr);
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
		//获取投票选项
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
		$this->assign("subjectid", $subjectid);
		$this->assign("subject_arr", $subject_arr);
		$this->assign("options", $options);
		$this->assign("infos", $infos);
		$this->assign("vote_data", $vote_data);
		$this->display($true_template);
	}

	/**
	 * 说明:获取用户的Ip
	 */
	function ip() {
		return get_client_ip();
	}

	/**
	 * 将文本格式成适合js输出的字符串
	 * @param string $string 需要处理的字符串
	 * @param intval $isjs 是否执行字符串格式化，默认为执行
	 * @return string 处理后的字符串
	 */
	function format_js($string, $isjs = 1) {
		$string = addslashes(str_replace(array("\r", "\n", "\t"), array('', '', ''), $string));
		return $isjs ? 'document.write("' . $string . '");' : $string;
	}

	/**
	 * 说明: 取得投票信息, 返回数组
	 * @param $subjectid 投票ID
	 */
	function get_subject($subjectid) {
		if (!$subjectid) {
			return FALSE;
		}

		$data = M("Vote")->where(array("subjectid" => $subjectid))->find();
		return $data;
	}

	/**
	 * 说明: 查询 该投票的 选项
	 * @param $subjectid 投票ID
	 */
	function get_options($subjectid, $order = array("optionid" => "ASC")) {
		if (!$subjectid) {
			return FALSE;
		}

		$db = M("VoteOption");
		$data = $db->where(array("subjectid" => $subjectid))->order($order)->select();
		return $data;
	}

	/**
	 * 将数组转换为字符串
	 *
	 * @param	array	$data		数组
	 * @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
	 * @return	string	返回字符串，如果，data为空，则返回空
	 */
	function array2string($data, $isformdata = 1) {
		if ($data == '') {
			return '';
		}

		return addslashes(var_export($data, TRUE));
	}

}
