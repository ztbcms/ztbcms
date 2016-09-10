<?php

// +----------------------------------------------------------------------
// | Vote模型 投票选项
// +----------------------------------------------------------------------

namespace Vote\Model;

use Common\Model\Model;

class Vote_optionModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('subjectid', 'require', '所属投票ID不能为空！', 1, 'regex', 3),
		array('option', 'require', '投票选项名称不能为空！', 1, 'regex', 3),
	);

	/**
	 * 说明:删除指定 投票ID对应的选项
	 * @param $data
	 * @param $subjectid
	 */
	public function del_options($subjectid) {
		if (!$subjectid) {
			return FALSE;
		}

		return $this->where(array("subjectid" => $subjectid))->delete();
	}

	/**
	 * 说明:更新选项
	 * @param $data 数组  Array ( [44] => 443 [43(optionid)] => 334(option 值) )
	 * @param $subjectid
	 */
	public function update_options($data) {
		//判断传递的数据类型是否正确
		if (!is_array($data)) {
			return FALSE;
		}

		foreach ($data as $key => $val) {
			if (trim($val) == '') {
				continue;
			}

			$newoption = array(
				'option' => $val,
			);
			$this->where("optionid=$key")->save($newoption);
		}
		return TRUE;
	}

}
