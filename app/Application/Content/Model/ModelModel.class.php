<?php

// +----------------------------------------------------------------------
// | 内容模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class ModelModel extends Model {

	private $libPath = ''; //当前模块路径

	const mainTableSql = 'Data/Sql/cms_zhubiao.sql'; //模型主表SQL模板文件
	const sideTablesSql = 'Data/Sql/cms_zhubiao_data.sql'; //模型副表SQL模板文件
	const modelTablesInsert = 'Data/Sql/cms_insert.sql'; //可用默认模型字段
	const membershipModelSql = 'Data/Sql/cms_member.sql'; //会员模型

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])

	protected $_validate = array(
		array('name', 'require', '模型名称不能为空！'),
		array('tablename', 'require', '表名不能为空！'),
		array('tablename', '/^[a-zwd_]+$/i', '模型表键名只支持英文！', 0, 'regex', 3),
		array('name', '', '该模型名称已经存在！', 0, 'unique', 1),
		array('tablename', '', '该表名已经存在！', 0, 'unique', 3),
		array('tablename', 'checkTablesql', '创建模型所需要的SQL文件丢失，创建失败！', 1, 'callback', 3),
		array('tablename', 'checkTablename', '该表名是系统保留或者已经存在，不允许创建！', 0, 'callback', 1),
	);
	//array(填充字段,填充内容,[填充条件,附加规则])
	protected $_auto = array(
		array("disabled", 0),
		array("sort", 0),
		array('addtime', 'time', 1, 'function'),
	);

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->libPath = APP_PATH . 'Content/';
	}

	/**
	 * 检查需要创建的表名是否为系统保留名称
	 * @param string $tablename 表名，不带表前缀
	 * @return boolean 存在返回false，不存在返回true
	 */
	public function checkTablename($tablename) {
		if (!$tablename) {
			return false;
		}
		//检查是否在保留内
		if (in_array($tablename, array("member_group", "member_content"))) {
			return false;
		}
		//检查该表名是否存在
		if ($this->table_exists($tablename)) {
			return false;
		}

		return true;
	}

	//检查SQL文件是否存在！
	public function checkTablesql() {
		//检查主表结构sql文件是否存在
		if (!is_file($this->libPath . self::mainTableSql)) {
			return false;
		}
		if (!is_file($this->libPath . self::sideTablesSql)) {
			return false;
		}
		if (!is_file($this->libPath . self::modelTablesInsert)) {
			return false;
		}
		if (!is_file($this->libPath . self::membershipModelSql)) {
			return false;
		}
		return true;
	}

	/**
	 * 创建会员模型
	 * @param string $tableName 模型主表名称（不包含表前缀）
	 * @param string $modelId 所属模型id
	 * @return boolean
	 */
	public function AddModelMember($tableName, $modelId) {
		if (empty($tableName)) {
			return false;
		}
		//表前缀
		$dbPrefix = C("DB_PREFIX");
		//读取会员模型SQL模板
		$membershipModelSql = file_get_contents($this->libPath . self::membershipModelSql);
		//表前缀，表名，模型id替换
		$sqlSplit = str_replace(array('@cms@', '@zhubiao@', '@modelid@'), array($dbPrefix, $tableName, $modelId), $membershipModelSql);
		return $this->sql_execute($sqlSplit);
	}

	/**
	 * 创建模型
	 * @param array $data 提交数据
	 * @return boolean
	 */
	public function addModel($data) {
		if (empty($data)) {
			return false;
		}
		//数据验证
		$data = $this->create($data, 1);
		if ($data) {
			//强制表名为小写
			$data['tablename'] = strtolower($data['tablename']);
			//添加模型记录
			$modelid = $this->add($data);
			if ($modelid) {
				//创建数据表
				if ($this->createModel($data['tablename'], $modelid)) {
					cache("Model", NULL);
					return $modelid;
				} else {
					//表创建失败
					$this->where(array("modelid" => $modelid))->delete();
					$this->error = '数据表创建失败！';
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 编辑模型
	 * @param array $data 提交数据
	 * @return boolean
	 */
	public function editModel($data, $modelid = 0) {
		if (empty($data)) {
			return false;
		}
		//模型ID
		$modelid = $modelid ? $modelid : (int) $data['modelid'];
		if (!$modelid) {
			$this->error = '模型ID不能为空！';
			return false;
		}
		//查询模型数据
		$info = $this->where(array("modelid" => $modelid))->find();
		if (empty($info)) {
			$this->error = '该模型不存在！';
			return false;
		}
		$data['modelid'] = $modelid;
		//数据验证
		$data = $this->create($data, 2);
		if ($data) {
			//强制表名为小写
			$data['tablename'] = strtolower($data['tablename']);
			//是否更改表名
			if ($info['tablename'] != $data['tablename'] && !empty($data['tablename'])) {
				//检查新表名是否存在
				if ($this->table_exists($data['tablename']) || $this->table_exists($data['tablename'] . '_data')) {
					$this->error = '该表名已经存在！';
					return false;
				}
				if (false !== $this->where(array("modelid" => $modelid))->save($data)) {
					//表前缀
					$dbPrefix = C("DB_PREFIX");
					//表名更改
					if (!$this->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}` TO  `{$dbPrefix}{$data['tablename']}` ;")) {
						$this->error = '数据库修改表名失败！';
						return false;
					}
					//修改副表
					if (!$this->sql_execute("RENAME TABLE  `{$dbPrefix}{$info['tablename']}_data` TO  `{$dbPrefix}{$data['tablename']}_data` ;")) {
						//主表已经修改，进行回滚
						$this->sql_execute("RENAME TABLE  `{$dbPrefix}{$data['tablename']}` TO  `{$dbPrefix}{$info['tablename']}` ;");
						$this->error = '数据库修改副表表名失败！';
						return false;
					}
					//更新缓存
					cache("Model", NULL);
					return true;
				} else {
					$this->error = '模型更新失败！';
					return false;
				}
			} else {
				if (false !== $this->where(array("modelid" => $modelid))->save($data)) {
					return true;
				} else {
					$this->error = '模型更新失败！';
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * 创建内容模型
	 * @param string $tableName 模型主表名称（不包含表前缀）
	 * @param string $modelId 模型id
	 * @return boolean
	 */
	protected function createModel($tableName, $modelId) {
		if (empty($tableName) || $modelId < 1) {
			return false;
		}
		//表前缀
		$dbPrefix = C("DB_PREFIX");
		//读取模型主表SQL模板
		$mainTableSqll = file_get_contents($this->libPath . self::mainTableSql);
		//副表
		$sideTablesSql = file_get_contents($this->libPath . self::sideTablesSql);
		//字段数据
		$modelTablesInsert = file_get_contents($this->libPath . self::modelTablesInsert);
		//表前缀，表名，模型id替换
		$sqlSplit = str_replace(array('@cms@', '@zhubiao@', '@modelid@'), array($dbPrefix, $tableName, $modelId), $mainTableSqll . "\n" . $sideTablesSql . "\n" . $modelTablesInsert);

		return $this->sql_execute($sqlSplit);
	}

	/**
	 * 删除表
	 * @param $table string 不带表前缀
     * @return boolean
	 */
	public function deleteTable($table) {
		if ($this->table_exists($table)) {
			$this->drop_table($table);
		}
		return true;
	}

	/**
	 * 根据模型ID删除模型
	 * @param string $modelid 模型id
	 * @return boolean
	 */
	public function deleteModel($modelid) {
		if (empty($modelid)) {
			return false;
		}
		//这里可以根据缓存获取表名
		$modeldata = $this->where(array("modelid" => $modelid))->find();
		if (!$modeldata) {
			return false;
		}
		//表名
		$model_table = $modeldata['tablename'];
		//删除模型数据
		$this->where(array("modelid" => $modelid))->delete();
		//更新缓存
		cache("Model", NULL);
		//删除所有和这个模型相关的字段
		D("ModelField")->where(array("modelid" => $modelid))->delete();
		//删除主表
		$this->deleteTable($model_table);
		if ((int) $modeldata['type'] == 0) {
			//删除副表
			$this->deleteTable($model_table . "_data");
		}
		return true;
	}

	/**
	 * 模型导入
	 * @param array $data 数据
	 * @param string $tablename 导入的模型表名
	 * @param string $name 模型名称
	 * @return int|boolean
	 */
	public function import($data, $tablename = '', $name = '') {
		if (empty($data)) {
			$this->error = '没有导入数据！';
			return false;
		}
		//解析
		$data = json_decode(base64_decode($data), true);
		if (empty($data)) {
			$this->error = '解析数据失败，无法进行导入！';
			return false;
		}
		//取得模型数据
		$model = $data['model'];
		if (empty($model)) {
			$this->error = '解析数据失败，无法进行导入！';
			return false;
		}
		C('TOKEN_ON', false);
		if ($name) {
			$model['name'] = $name;
		}
		if ($tablename) {
			$model['tablename'] = $tablename;
		}
		//导入
		$modelid = $this->addModel($model);
		if ($modelid) {
			if (!empty($data['field'])) {
				foreach ($data['field'] as $value) {
					$value['modelid'] = $modelid;
					if ($value['setting']) {
						$value['setting'] = unserialize($value['setting']);
					}
					$model = new \Content\Model\ModelFieldModel();
					if ($model->addField($value) == false) {
						$value['setting'] = serialize($value['setting']);
						$model->where(array('modelid' => $modelid, 'field' => $value['field'], 'name' => $value['name']))->save($value);
					}
					unset($model);
				}
			}
			return $modelid;
		} else {
			return false;
		}
	}

	/**
	 * 模型导出
	 * @param string $modelid 模型ID
	 * @return boolean
	 */
	public function export($modelid) {
		if (empty($modelid)) {
			$this->error = '请指定需要导出的模型！';
			return false;
		}
		//取得模型信息
		$info = $this->where(array('modelid' => $modelid, 'type' => 0))->find();
		if (empty($info)) {
			$this->error = '该模型不存在，无法导出！';
			return false;
		}
		unset($info['modelid']);
		//数据
		$data = array();
		$data['model'] = $info;
		//取得对应模型字段
		$fieldList = M('ModelField')->where(array('modelid' => $modelid))->select();
		if (empty($fieldList)) {
			$fieldList = array();
		}
		//去除fieldid，modelid字段内容
		foreach ($fieldList as $k => $v) {
			unset($fieldList[$k]['fieldid'], $fieldList[$k]['modelid']);
		}
		$data['field'] = $fieldList;
		return base64_encode(json_encode($data));
	}

	//兼容方法...
	public function delete_model($modelid) {
		return $this->deleteModel($modelid);
	}

	/**
	 * 执行SQL
	 * @param string $sqls SQL语句
	 * @return boolean
	 */
	protected function sql_execute($sqls) {
		$sqls = $this->sql_split($sqls);
		if (is_array($sqls)) {
			foreach ($sqls as $sql) {
				if (trim($sql) != '') {
					$this->execute($sql, true);
				}
			}
		} else {
			$this->execute($sqls, true);
		}
		return true;
	}

	/**
	 * SQL语句预处理
	 * @param string $sql
	 * @return array
	 */
	public function sql_split($sql) {
		if (mysql_get_server_info() > '4.1' && C('DB_CHARSET')) {
			$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . C('DB_CHARSET'), $sql);
		}
		if (C("DB_PREFIX") != "cms_") {
			$sql = str_replace("cms_", C("DB_PREFIX"), $sql);
		}
		$sql = str_replace("\r", "\n", $sql);
		$ret = array();
		$num = 0;
		$queriesarray = explode(";\n", trim($sql));
		unset($sql);
		foreach ($queriesarray as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			$queries = array_filter($queries);
			foreach ($queries as $query) {
				$str1 = substr($query, 0, 1);
				if ($str1 != '#' && $str1 != '-') {
					$ret[$num] .= $query;
				}

			}
			$num++;
		}
		return $ret;
	}

	/**
	 * 根据模型类型取得数据用于缓存
	 * @param string $type
	 * @return array
	 */
	public function getModelAll($type = null) {
		$where = array('disabled' => 0);
		if (!is_null($type)) {
			$where['type'] = $type;
		}
		$data = $this->where($where)->select();
		$Cache = array();
		foreach ($data as $v) {
			$Cache[$v['modelid']] = $v;
		}
		return $Cache;
	}

	/**
	 * 生成模型缓存，以模型ID为下标的数组
	 * @return array
	 */
	public function model_cache() {
		$data = $this->getModelAll();
		cache('Model', $data);
		return $data;
	}

}
