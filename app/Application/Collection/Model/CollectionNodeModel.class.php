<?php

// +----------------------------------------------------------------------
// | 采集模型
// +----------------------------------------------------------------------

namespace Collection\Model;

use Common\Model\Model;

class CollectionNodeModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('name', 'require', '该采集项目名称不能为空！'),
		array('name', '', '该采集项目名称已经存在！', 0, 'unique', 1),
		array('urlpage', 'require', '采集网址不能为空！'),
	);

	/**
	 * 删除节点
	 * @param type $nodeid
	 * @return boolean
	 */
	public function nodeDelete($nodeid) {
		$content_db = M("CollectionContent");
		$program_db = M("CollectionProgram");
		$history_db = M("CollectionHistory");
		if (is_array($nodeid)) {
			foreach ($nodeid as $id) {
				if (intval($v)) {
					$nodeid[$k] = intval($v);
				} else {
					unset($nodeid[$k]);
				}
			}
			if ($this->where(array("nodeid" => array("in", $nodeid)))->delete() !== false) {
				$content_db->where(array("nodeid" => array("in", $nodeid)))->delete();
				$program_db->where(array("nodeid" => array("in", $nodeid)))->delete();
				$history_db->where(array("nodeid" => array("in", $nodeid)))->delete();
				return true;
			} else {
				return false;
			}
		} else {
			if ($nodeid < 1) {
				return false;
			}
			if ($this->where(array("nodeid" => $nodeid))->delete() !== false) {
				$content_db->where(array("nodeid" => $nodeid))->delete();
				$program_db->where(array("nodeid" => $nodeid))->delete();
				$history_db->where(array("nodeid" => $nodeid))->delete();
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

}
