<?php

// +----------------------------------------------------------------------
// | 评论字段
// +----------------------------------------------------------------------

namespace Comments\Model;

use Common\Model\Model;

class CommentsFieldModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('ftype', 'require', '字段类型不能为空！', 1, 'regex', 3),
		array('f', 'require', '字段名不能为空！', 1, 'regex', 1),
		array('f', '', '该字段名已经存在！', 0, 'unique', 3),
		array('issystem', 'require', '存放位置不能为空！', 1, 'regex', 1),
		array('f,issystem', 'checkField', '该字段表中已经存在！', 1, 'callback', 1),
		array('fname', 'require', '字段标识不能为空！', 1, 'regex', 3),
	);

	/**
	 *  检查字段是否存在
	 * @param type $data
	 * @return boolean true 存在，false 不存在
	 */
	public function checkField($data) {
		$field = $data['f'];
		if (!$field) {
			return false;
		}
		if ((int) $data['issystem']) {
			$table = "comments";
		} else {
			$table = "comments_data_1";
		}
		return !$this->field_exists($table, $field);
	}

	//创建一张分表
	public function addfenbiao() {
		//取得表结构
		$CREATE = $this->query("SHOW CREATE TABLE `" . C("DB_PREFIX") . "comments_data_1`;");
		$db = M("CommentsSetting");
		$r = $db->find();
		if (!$r) {
			return false;
		}
		$create = str_replace("\"", "\\\"", $CREATE[0]['Create Table']);
		$create = str_replace("" . C("DB_PREFIX") . "comments_data_1", "" . C("DB_PREFIX") . "comments_data_" . ((int) $r['stbsum'] + 1), $create);
		if (empty($create)) {
			return false;
		}
		//执行SQL
		$status = $this->execute($create);
		if ($status == false && is_bool($status)) {
			return false;
		}
		//分表数加1
		return $db->where(array("stbsum" => $r['stbsum']))->setInc('stbsum', 1);
	}

	//删除字段
	public function fieldDelete($fid) {
		if (!$fid) {
			return false;
		}
		$Setting = M('CommentsSetting')->find();
		$r = $this->where(array("fid" => $fid))->find();
		if (!$r) {
			return false;
		}
		if ((int) $r['issystem'] == 1) {
			$this->query("alter table " . C("DB_PREFIX") . "comments DROP `" . $r['f'] . "`");
		} else {
			for ($i = 1; $i <= (int) $Setting['stbsum']; $i++) {
				$this->query("alter table " . C("DB_PREFIX") . "comments_data_" . $i . " DROP `" . $r['f'] . "` ");
			}
		}
		return $this->where(array("fid" => $fid))->delete();
	}

	/**
	 * 添加字段
	 * @param type $data 字段相关配置
	 * @return boolean
	 */
	public function fieldAdd($data) {
		//取得字段SQL语句
		$field = $this->returnPlFtype($data);
		$setting = M('CommentsSetting')->find();
		if (empty($field) || empty($setting)) {
			return false;
		}
		//添加字段信息
		$id = $this->add($data);
		if ($id) {
			if ((int) $data['issystem']) {
				//对主表添加字段
				$this->query("alter table " . C("DB_PREFIX") . "comments add " . $field);
			} else {
				//所有副表都增加字段
				for ($i = 1; $i <= (int) $setting['stbsum']; $i++) {
					$this->query("alter table " . C("DB_PREFIX") . "comments_data_" . $i . " add " . $field);
				}
			}
			return $id;
		} else {
			return false;
		}
	}

	/**
	 * 编辑字段
	 * @param type $data 字段相关配置
	 * @return boolean
	 */
	public function fieldEdit($data) {
		$fid = $data['fid'];
		if (!$data || !$fid) {
			return false;
		}
		$r = $this->where(array("fid" => $fid))->find();
		if (!$r) {
			return false;
		}
		//取得字段SQL语句
		$data['f'] = $r['f'];
		$field = $this->returnPlFtype($data);
		if (isset($data['issystem'])) {
			unset($data['issystem']);
		}
		unset($data['fid']);
		$setting = M("CommentsSetting")->find();
		if (empty($field) || empty($setting)) {
			return false;
		}
		//添加字段信息
		$id = $this->where(array('fid' => $fid))->save($data);
		if (false !== $id) {
			if ((int) $r['issystem']) {
				//对主表添加字段
				$this->query("alter table " . C("DB_PREFIX") . "comments change `" . $r['f'] . "` " . $field);
			} else {
				//所有副表都增加字段
				for ($i = 1; $i <= (int) $setting['stbsum']; $i++) {
					$this->query("alter table " . C("DB_PREFIX") . "comments_data_" . $i . " change `" . $r['f'] . "` " . $field);
				}
			}
			return $id;
		} else {
			return false;
		}
	}

	/**
	 * 返回字段类型SQL
	 * @param string $add 字段配置信息
	 * @return string
	 */
	public function returnPlFtype($add) {
		//字段类型
		if ($add["ftype"] == "TINYINT" || $add["ftype"] == "SMALLINT" || $add["ftype"] == "INT" || $add["ftype"] == "BIGINT" || $add["ftype"] == "FLOAT" || $add["ftype"] == "DOUBLE") {
			$def = " default '0'";
		} elseif ($add["ftype"] == "VARCHAR") {
			$def = " default ''";
		} else {
			$def = "";
		}
		$type = $add["ftype"];
		//VARCHAR
		if ($add["ftype"] == 'VARCHAR' && empty($add["flen"])) {
			$add[flen] = '255';
		}
		//字段长度
		if ($add["flen"]) {
			if ($add["ftype"] != "TEXT" && $add["ftype"] != "MEDIUMTEXT" && $add["ftype"] != "LONGTEXT") {
				$type .= "(" . $add[flen] . ")";
			}
		}
		$field = "`" . $add["f"] . "` " . $type . " NOT NULL" . $def;
		return $field;
	}

}
