<?php

// +----------------------------------------------------------------------
// | 表单模型
// +----------------------------------------------------------------------

namespace Formguide\Model;

use Content\Model\ModelModel;

class FormguideModel extends ModelModel {

	protected $tableName = 'model';
	//模型类型
	protected $modelType = 3;
	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('name', 'require', '表单名称不能为空！'),
		array('tablename', 'require', '表名不能为空！'),
		array('name', '', '该表单名称已经存在！', 0, 'unique', 3),
		array('tablename', '', '该表名已经存在！', 0, 'unique', 3),
		array('tablename', 'checkTablesql', '创建表单所需要的SQL文件丢失，创建失败！', 1, 'callback', 3),
		array('tablename', 'checkTablename', '该表名是系统保留或者已经存在，不允许创建！', 0, 'callback', 3),
	);

	//返回模型类型
	public function getModelType() {
		return $this->modelType;
	}

	/**
	 * 创建表单数据表
	 * @param type $tableName 模型主表名称（不包含表前缀）
	 * @param type $modelId 所属模型id
	 * @return boolean
	 */
	public function addModelFormguide($tableName, $modelId = false) {
		if (empty($tableName)) {
			return false;
		}
		$sql = "CREATE TABLE IF NOT EXISTS `think_form_table` (
                        `dataid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                        `userid` mediumint(8) unsigned NOT NULL,
                       `username` varchar(20) NOT NULL,
                       `datetime` int(10) unsigned NOT NULL,
                       `ip` char(15) NOT NULL,
                       PRIMARY KEY (`dataid`)
                    ) ENGINE=MyISAM;";
		//表名替换
		$sql = str_replace("think_form_table", C("DB_PREFIX") . $tableName, $sql);
		return $this->sql_execute($sql);
	}

	//缓存生成
	public function formguide_cache() {
		$formguide_cache = $this->getModelAll($this->modelType);
		if (!empty($formguide_cache)) {
			cache('Model_form', $formguide_cache);
		}
		return $formguide_cache;
	}

}
