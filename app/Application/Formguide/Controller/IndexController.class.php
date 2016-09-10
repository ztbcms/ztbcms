<?php

// +----------------------------------------------------------------------
// | 表单前台
// +----------------------------------------------------------------------

namespace Formguide\Controller;

use Common\Controller\Base;

class IndexController extends Base {

	//表单模型缓存
	protected $Model_form;
	//数据模型
	protected $db = NULL, $formguide;
	//当前表单ID
	public $formid;
	//配置
	protected $setting = array();
	//模型信息
	protected $modelInfo = array();
	//输出类型
	protected $showType = NULL;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->showType = I('get.action');
		$this->formguide = D("Formguide/Formguide");
		$this->Model_form = cache("Model_form");
		$this->formid = I('request.formid', 0, 'intval');
		if (!empty($this->formid)) {
			$this->db = \Content\Model\ContentModel::getInstance($this->formid);
		}
		//模型
		$this->modelInfo = $this->Model_form[$this->formid];
		if (empty($this->modelInfo)) {
			if ($this->showType == "js") {
				exit($this->format_js('该表单不存在或者已经关闭！'));
			}
			$this->error('该表单不存在或者已经关闭！');
		}
		//配置
		$this->modelInfo['setting'] = $this->setting = unserialize($this->modelInfo['setting']);
		$this->assign('formid', $this->formid);
	}

	//显示表单
	public function index() {
		if (empty($this->formid)) {
			if ($this->showType == "js") {
				exit($this->format_js('该表单不存在或者已经关闭！'));
			}
			$this->error('该表单不存在或者已经关闭！');
		}
		$r = $this->formguide->where(array("modelid" => $this->formid))->find();
		if (empty($r)) {
			if ($this->showType == "js") {
				exit($this->format_js('该表单不存在或者已经关闭！'));
			}
			$this->error('该表单不存在或者已经关闭！');
		}
		//验证权限
		$this->competence();
		//模板
		$show_template = $this->setting['show_template'] ? $this->setting['show_template'] : "show";
		//js模板
		$show_js_template = $this->setting['show_js_template'] ? $this->setting['show_js_template'] : "js";
		//实例化表单类 传入 模型ID 栏目ID 栏目数组
		$content_form = new \content_form($this->formid);
		//生成对应字段的输入表单
		$forminfos = $content_form->get();
		$forminfos = $forminfos['senior'];
		//生成对应的JS提示等
		$formValidator = $content_form->formValidator;
		$this->assign("forminfos", $forminfos);
		$this->assign("formValidator", $formValidator);
		$this->assign($this->modelInfo);
		$this->assign("modelid", $this->formid);
		if ($this->showType == 'js') {
			$html = $this->fetch(parseTemplateFile("Show/{$show_js_template}"));
			//输出js
			exit($this->format_js($html));
		}
		$this->display("Show/{$show_template}");
	}

	//表单提交
	public function post() {
		//验证权限
		$this->competence();
		//提交间隔
		if ($this->setting['interval']) {
			$formguide = cookie('formguide_' . $this->formid);
			if ($formguide) {
				$this->error("操作过快，请歇息后再次提交！");
			}
		}
		//开启验证码
		if ($this->setting['isverify']) {
			$verify = I('post.verify');
			if (empty($verify)) {
				$this->error('请输入验证码！');
			}
			if (false == $this->verify($verify, 'formguide')) {
				$this->error('验证码错误，请重新输入！');
			}
		}
		//表单提交数据
		$info = array_merge($_POST['info'], array(C("TOKEN_NAME") => $_POST[C("TOKEN_NAME")]));
		//增加一些系统必要字段
		$uid = service("Passport")->userid ?: 0;
		$username = service("Passport")->username ?: '';
		$info['userid'] = $uid;
		$info['username'] = $username ?: "游客";
		$info['datetime'] = time();
		$info['ip'] = get_client_ip();
		$content_input = new \content_input($this->formid);
		$inputinfo = $content_input->get($info);
		if (false == $inputinfo) {
			$this->error($content_input->getError() ? $content_input->getError() : '出现错误！');
		}
		$inputinfo = $this->db->create($inputinfo, 1);
		if (false == $inputinfo) {
			$this->error($this->db->getError() ? $this->db->getError() : '出现错误！');
		}
		if (!empty($inputinfo)) {
			$id = $this->db->relation(false)->add($inputinfo);
			if ($id) {
				//信息量+1
				M("Model")->where(array("modelid" => $this->formid))->setInc("items");
				//跳转地址
				$forward = $this->setting['forward'] ?: cache('Config.siteurl');
				//发送邮件
				if ($this->setting['sendmail'] && $this->setting['mails']) {
					$mails = explode(",", $this->setting['mails']);
					$title = $info['username'] . " 在《" . $this->modelInfo['name'] . "》提交了新的信息！";
					$message = "刚刚有人在《" . $this->modelInfo['name'] . "》中提交了新的信息，请进入后台查看！";
					SendMail($mails, $title, $message);
				}
				if ($this->setting['interval']) {
					cookie('formguide_' . $this->formid, 1, $this->setting['interval']);
				}
				$this->success("提交成功！", $forward);
			} else {
				$this->error("提交失败！");
			}
		} else {
			$this->error('系统处理错误！');
		}
	}

	/**
	 * 将文本格式成适合js输出的字符串
	 * @param string $string 需要处理的字符串
	 * @param intval $isjs 是否执行字符串格式化，默认为执行
	 * @return string 处理后的字符串
	 */
	protected function format_js($str, $isjs = 1) {
		preg_match_all("/[\xc2-\xdf][\x80-\xbf]+|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}|[\x01-\x7f]+/e", $str, $r);
		//匹配utf-8字符，
		$str = $r[0];
		$l = count($str);
		for ($i = 0; $i < $l; $i++) {
			$value = ord($str[$i][0]);
			if ($value < 223) {
				$str[$i] = rawurlencode(utf8_decode($str[$i]));
				//先将utf8编码转换为ISO-8859-1编码的单字节字符，urlencode单字节字符.
				//utf8_decode()的作用相当于iconv("UTF-8","CP1252",$v)。
			} else {
				$str[$i] = "%u" . strtoupper(bin2hex(iconv("UTF-8", "UCS-2", $str[$i])));
			}
		}
		$reString = join("", $str);
		return $isjs ? 'document.write(unescape("' . $reString . '"));' : $reString;
	}

	//验证提交权限
	protected function competence() {
		$time = time();
		//表单有有效期时间限制 判断
		if (!empty($this->setting['enabletime'])) {
			//开始时间
			if ($this->setting['starttime']) {
				if ($time < (int) $this->setting['starttime']) {
					if ($this->showType == "js") {
						exit($this->format_js('该表单还没有开始！'));
					}
					$this->error('该表单还没有开始！');
				}
			}
			//结束时间
			if ($this->setting['endtime']) {
				if ($time > (int) $this->setting['endtime']) {
					if ($this->showType == "js") {
						exit($this->format_js('该表单已经结束！'));
					}
					$this->error('该表单已经结束！');
				}
			}
		}
		//是否允许游客提交
		if ((int) $this->setting['allowunreg'] == 0) {
			//判断是否登录
			if (!service("Passport")->userid) {
				if ($this->showType == "js") {
					exit($this->format_js('该表单不允许游客提交，请登录后操作！'));
				}
				$this->error('该表单不允许游客提交，请登录后操作！', U('Member/Index/login'));
			}
		}
		//是否允许同一IP多次提交
		if ((int) $this->setting['allowmultisubmit'] == 0) {
			$ip = get_client_ip();
			$count = $this->db->where(array("ip" => $ip))->count();
			if ($count) {
				if ($this->showType == "js") {
					exit($this->format_js('你已经提交过了！'));
				}
				$this->error('你已经提交过了！');
			}
		}
		//不允许提交IP
		if (!empty($this->setting['noip'])) {
			$noip = explode("\n", $this->setting['noip']);
			//转换成正则
			foreach ($noip as $k => $v) {
				$ipaddres = $this->makePregIP($v);
				$ip = str_ireplace(".", "\.", $ipaddres);
				$ip = str_replace("*", "[0-9]{1,3}", $ip);
				$ipaddres = "/" . $ip . "/";
				$ip_list[] = $ipaddres;
			}
			//用户IP
			$ip = get_client_ip();
			if ($ip_list) {
				foreach ($ip_list as $value) {
					if (preg_match("{$value}", $ip)) {
						if ($this->showType == "js") {
							exit($this->format_js('您的IP在禁止提交列表中！'));
						}
						$this->error('您的IP在禁止提交列表中！');
						break;
					}
				}
			}
		}
	}

	//ip进行处理
	private function makePregIP($str) {
		if (strstr($str, "-")) {
			$aIP = explode(".", $str);
			foreach ($aIP as $k => $v) {
				if (!strstr($v, "-")) {
					$preg_limit .= $this->makePregIP($v);
				} else {
					$aipNum = explode("-", $v);
					for ($i = $aipNum[0]; $i <= $aipNum[1]; $i++) {
						$preg .= $preg ? "|" . $i : "[" . $i;
					}
					$preg_limit .= strrpos($preg_limit, ".", 1) == (strlen($preg_limit) - 1) ? $preg . "]" : "." . $preg . "]";
				}
			}
		} else {
			$preg_limit .= $str;
		}
		return $preg_limit;
	}

}
