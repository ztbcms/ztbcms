<?php

// +----------------------------------------------------------------------
// | Vote模型
// +----------------------------------------------------------------------

namespace Vote\Model;

use Common\Model\Model;

class VoteModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('subject', 'require', '投票标题不能为空！', 1, 'regex', 3),
		array('ischeckbox', 'require', '投票类型不能为空！', 1, 'regex', 3),
	);
	//自动完成
	protected $_auto = array(
		//array(填充字段,填充内容,填充条件,附加规则)
		array("addtime", "time", 1, "function"),
	);

	/**
	 * 增加投票
	 * @param type $data 投票数据
	 * @param type $option 投票选项
	 * @return boolean
	 */
	public function VoteAdd($data, $option) {
		if (!is_array($data) || !is_array($option)) {
			return false;
		}
		//添加投票
		$subjectid = $this->add($data);
		if ($subjectid) {
			$this->add_options($option, $subjectid);
			return $subjectid;
		} else {
			return false;
		}
	}

	/**
	 * 删除投票
	 * @param type $subjectid 投票id
	 * @return boolean
	 */
	public function VoteDelete($subjectid) {
		if (!$subjectid) {
			return false;
		}
		//删除投票
		$result = $this->where(array("subjectid" => $subjectid))->delete();
		if ($result) {
			//删除对应投票的选项
			D("Vote/Vote_option")->del_options((int) $subjectid);
			//删除投票数据
			M('VoteData')->where(array("subjectid" => $subjectid))->delete();
			//删除生成的JS
			@unlink(SITE_PATH . "/d/vote_js/vote_" . $subjectid . ".js");
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 说明:添加投票选项操作
	 * @param $data 选项数组
	 * @param $subjectid 投票标题ID
	 */
	public function add_options($data, $subjectid) {
		$db = D('Vote/Vote_option');
		//判断传递的数据类型是否正确
		if (!is_array($data)) {
			return false;
		}

		if (!$subjectid) {
			return false;
		}

		C('TOKEN_ON', false);
		foreach ($data as $key => $val) {
			if (trim($val) == '') {
				continue;
			}

			$newoption = array(
				'subjectid' => $subjectid,
				'option' => $val,
				'image' => '',
				'listorder' => 0,
			);
			$data = $db->create($newoption);
			if ($data) {
				$optiondata[] = $data;
			}
		}
		C('TOKEN_ON', true);
		if ($optiondata && is_array($optiondata)) {
			if (count($optiondata) > 1) {
				return $db->addAll($optiondata);
			} else {
				return $db->add($optiondata[0]);
			}
		} else {
			return false;
		}
	}

	//清除投票记录
	public function ClearStatistics($subjectid) {
		if (!$subjectid) {
			return false;
		}
		//删除统计记录
		M('VoteData')->where(array("subjectid" => $subjectid))->delete();
		//清空投票记录
		$result = M('Vote')->where(array("subjectid" => $subjectid))->save(array("votenumber" => 0));
		if ($result !== false) {
			M('VoteOption')->where(array("subjectid" => $subjectid))->save(array("stat" => 0));
			return true;
		} else {
			return false;
		}
	}

}
