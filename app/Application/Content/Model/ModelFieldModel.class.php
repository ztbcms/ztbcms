<?php

// +----------------------------------------------------------------------
// | 模型字段管理
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class ModelFieldModel extends Model {

	protected $tableName = 'model_field';
	//字段类型存放路径
	private $fieldPath = '';
	//不显示的字段类型（字段类型）
	public $not_allow_fields = array('catid', 'typeid', 'title', 'keyword', 'template', 'username', 'tags');
	//允许添加但必须唯一的字段（字段名）
	public $unique_fields = array('pages', 'readpoint', 'author', 'copyfrom', 'islink', 'posid');
	//禁止被禁用（隐藏）的字段列表（字段名）
	public $forbid_fields = array(/*'catid',  'title' , 'updatetime', 'inputtime', 'url', 'listorder', 'status', 'template', 'username', 'allow_comment', 'tags' */);
	//禁止被删除的字段列表（字段名）
	public $forbid_delete = array(/*'catid', 'typeid', 'title', 'thumb', 'keyword', 'keywords', 'updatetime', 'tags', 'inputtime', 'posid', 'url', 'listorder', 'status', 'template', 'username', 'allow_comment'*/);
	//可以追加 JS和CSS 的字段（字段名）
	public $att_css_js = array('text', 'textarea', 'box', 'number', 'keyword', 'typeid');
	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('modelid', 'require', '请选择模型！'),
		array('formtype', 'require', '字段类型不能为空！'),
		array('field', 'require', '字段名称必须填写！'),
		array('field', 'isFieldUnique', '该字段名称已经存在！', 0, 'callback', 1),
		array('name', 'require', '字段别名必须填写！'),
		array('field', '/^[a-z_0-9]+$/i', '字段名只支持英文！', 0, 'regex', 3),
		array('isbase', array(0, 1), '是否作为基本信息设置错误！', 0, 'in', 3),
		array('isadd', array(0, 1), '是否前台投稿中显示设置错误！', 0, 'in', 3),
	);
	//array(填充字段,填充内容,[填充条件,附加规则])
	protected $_auto = array(
	);

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->fieldPath = APP_PATH . 'Content/Fields/';
	}

	//返回字段存放路径
	public function getFieldPath() {
		return $this->fieldPath;
	}

	/**
	 * 验证字段名是否已经存在
	 * @param string $fieldName
	 * @return boolean false已经存在，true不存在
	 */
	public function isFieldUnique($fieldName) {
		if (empty($fieldName)) {
			return true;
		}
		if ($this->where(array('modelid' => $this->modelid, 'field' => $fieldName))->count()) {
			return false;
		}
		return true;
	}

	/**
	 * 获取可用字段类型列表
	 * @return array
	 */
	public function getFieldTypeList() {
		$fields = include $this->fieldPath . 'fields.inc.php';
		$fields = $fields ?: array();
		return $fields;
	}

	/**
	 * 根据模型ID读取全部字段信息
	 * @param string $modelid 模型ID
	 * @return null|array
	 */
	public function getModelField($modelid) {
		return $this->where(array("modelid" => $modelid))->order(array("listorder" => "ASC"))->select();
	}

	/**
	 * 检查该字段是否允许添加
	 * @param string $field 字段名称
	 * @param string $field_type 字段类型
	 * @param string $modelid 模型
	 * @return boolean
	 */
	public function isAddField($field, $field_type, $modelid) {
		//判断是否唯一字段
		if (in_array($field, $this->unique_fields)) {
			$f_datas = $this->where(array("modelid" => $modelid))->getField("field,field,formtype,name");
			return empty($f_datas[$field]) ? true : false;
		}
		//不显示的字段类型（字段类型）
		if (in_array($field_type, $this->not_allow_fields)) {
			return false;
		}
		//禁止被禁用的字段列表（字段名）
		if (in_array($field, $this->forbid_fields)) {
			return false;
		}
		//禁止被删除的字段列表（字段名）
		if (in_array($field, $this->forbid_delete)) {
			return false;
		}
		return true;
	}

	/**
	 * 判断字段是否允许被编辑
	 * @param string $field 字段名称
	 * @return boolean
	 */
	public function isEditField($field) {
		//判断是否唯一字段
		if (in_array($field, $this->unique_fields)) {
			return false;
		}
		//禁止被禁用的字段列表（字段名）
		if (in_array($field, $this->forbid_fields)) {
			return false;
		}
		//禁止被删除的字段列表（字段名）
		if (in_array($field, $this->forbid_delete)) {
			return false;
		}
		return true;
	}

	/**
	 * 判断字段是否允许删除
	 * @param string $field 字段名称
	 * @return boolean
	 */
	public function isDelField($field) {
		//禁止被删除的字段列表（字段名）
		if (in_array($field, $this->forbid_delete)) {
			return false;
		}
		return true;
	}

	/**
	 * 根据模型ID，返回表名
	 * @param string $modelid
	 * @param string|int $issystem
	 * @return string
	 */
	protected function getModelTableName($modelid, $issystem = 1) {
		//读取模型配置 以后优化缓存形式
		$model_cache = cache("Model");
		//表名获取
		$model_table = $model_cache[$modelid]['tablename'];
		//完整表名获取 判断主表 还是副表
		$tablename = $issystem ? $model_table : $model_table . "_data";
		return $tablename;
	}

	/**
	 * 添加字段
	 * @param array $data 字段相关数据
	 * @return boolean
	 */
	public function addField($data) {
		//保存一份原始数据
		$oldData = $data;
		//字段附加配置
		$setting = $data['setting'];
		//附加属性值
		$data['setting'] = serialize($setting);
		//模型id
		$modelid = $data['modelid'];
		//完整表名获取 判断主表 还是副表
		$tablename = $this->getModelTableName($modelid, $data['issystem']);
		if (!$this->table_exists($tablename)) {
			$this->error = '数据表不存在！';
			return false;
		}
		//数据正则
		$pattern = $data['pattern'];
		//进行数据验证
		$field = $data['field'];
		$data = $this->create($data, 1);
		if ($data) {
			$data['pattern'] = $pattern;
			//检查字段是否存在
			if ($this->field_exists($tablename, $data['field'])) {
				$this->error = '该字段已经存在！';
				return false;
			}
			/**
			 * 对应字段配置
			 * $field_type = 'varchar'; //字段数据库类型
			 * $field_basic_table = 1; //是否允许作为主表字段
			 * $field_allow_index = 1; //是否允许建立索引
			 * $field_minlength = 0; //字符长度默认最小值
			 * $field_maxlength = ''; //字符长度默认最大值
			 * $field_allow_search = 1; //作为搜索条件
			 * $field_allow_fulltext = 0; //作为全站搜索信息
			 * $field_allow_isunique = 1; //是否允许值唯一
			 */
			require $this->fieldPath . "{$data['formtype']}/config.inc.php";
			//根据字段设置临时更改字段类型，否则使用字段配置文件配置的类型
			if (isset($oldData['setting']['fieldtype'])) {
				$field_type = $oldData['setting']['fieldtype'];
			}
			//特定字段类型强制使用特定字段名，也就是字段类型等于字段名
			if (in_array($field_type, $this->forbid_delete)) {
				$data['field'] = $field_type;
			}
			//检查该字段是否允许添加
			if (false === $this->isAddField($data['field'], $data['formtype'], $modelid)) {
				$this->error = '该字段名称/类型不允许添加！';
				return false;
			}
			//增加字段
			$field = array(
				'tablename' => C("DB_PREFIX") . $tablename,
				'fieldname' => $data['field'],
				'maxlength' => $data['maxlength'],
				'minlength' => $data['minlength'],
				'defaultvalue' => $setting['defaultvalue'],
				'minnumber' => $setting['minnumber'],
				'decimaldigits' => $setting['decimaldigits'],
                'comment' => $data['name'] //字段别名 即为字段注释
			);
			if ($this->addFieldSql($field_type, $field)) {
				$fieldid = $this->add($data);
				//清理缓存
				cache('ModelField', NULL);
				if ($fieldid) {
					return $fieldid;
				} else {
					$this->error = '字段信息入库失败！';
					//回滚
					$this->execute("ALTER TABLE  `{$field['tablename']}` DROP  `{$field['fieldname']}`");
					return false;
				}
			} else {
				$this->error = '数据库字段添加失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *  编辑字段
	 * @param array $data 编辑字段数据
	 * @param int $fieldid 字段id
	 * @return boolean
	 */
	public function editField($data, $fieldid = 0) {
		if (!$fieldid && !isset($data['fieldid'])) {
			$this->error = '缺少字段id！';
			return false;
		} else {
			$fieldid = $fieldid ? $fieldid : (int) $data['fieldid'];
		}
		//原字段信息
		$info = $this->where(array("fieldid" => $fieldid))->find();
		if (empty($info)) {
			$this->error = '该字段不存在！';
			return false;
		}
		//字段主表副表不能修改
		unset($data['issystem']);
		//字段类型
		if (empty($data['formtype'])) {
			$data['formtype'] = $info['formtype'];
		}
		//模型id
		$modelid = $info['modelid'];
		//完整表名获取 判断主表 还是副表
		$tablename = $this->getModelTableName($modelid, $info['issystem']);
		if (!$this->table_exists($tablename)) {
			$this->error = '数据表不存在！';
			return false;
		}
		//保存一份原始数据
		$oldData = $data;
		//字段附加配置
		$setting = $data['setting'];
		/**
		 * 对应字段配置
		 * $field_type = 'varchar'; //字段数据库类型
		 * $field_basic_table = 1; //是否允许作为主表字段
		 * $field_allow_index = 1; //是否允许建立索引
		 * $field_minlength = 0; //字符长度默认最小值
		 * $field_maxlength = ''; //字符长度默认最大值
		 * $field_allow_search = 1; //作为搜索条件
		 * $field_allow_fulltext = 0; //作为全站搜索信息
		 * $field_allow_isunique = 1; //是否允许值唯一
		 */
		require $this->fieldPath . "{$data['formtype']}/config.inc.php";
		//根据字段设置临时更改字段类型，否则使用字段配置文件配置的类型
		if (isset($oldData['setting']['fieldtype'])) {
			$field_type = $oldData['setting']['fieldtype'];
		}
		//附加属性值
		$data['setting'] = serialize($setting);
		//数据正则
		$pattern = $data['pattern'];
		//进行数据验证
		$data = $this->create($data, 2);
		if ($data) {
			$data['pattern'] = $pattern;
			if (false !== $this->where(array("fieldid" => $fieldid))->save($data)) {
				//清理缓存
				cache('ModelField', NULL);
				//如果字段名变更
				if ($data['field'] && $info['field']) {
					//检查字段是否存在，只有当字段名改变才检测
					if ($data['field'] != $info['field'] && $this->field_exists($tablename, $data['field'])) {
						$this->error = '该字段已经存在！';
						//回滚
						$this->where(array("fieldid" => $fieldid))->save($info);
						return false;
					}
					//合并字段更改后的
					$newInfo = array_merge($info, $data);
					$newInfo['setting'] = unserialize($newInfo['setting']);
					$field = array(
						'tablename' => C("DB_PREFIX") . $tablename,
						'newfilename' => $data['field'],
						'oldfilename' => $info['field'],
						'maxlength' => $newInfo['maxlength'],
						'minlength' => $newInfo['minlength'],
						'defaultvalue' => $newInfo['setting']['defaultvalue'],
						'minnumber' => $newInfo['setting']['minnumber'],
						'decimaldigits' => $newInfo['setting']['decimaldigits'],
                        'comment' => $data['name'] //字段别名 即为字段注释
					);

					if (false === $this->editFieldSql($field_type, $field)) {
						$this->error = '数据库字段结构更改失败！';
						//回滚
						$this->where(array("fieldid" => $fieldid))->save($info);
						return false;
					}
				}
				return true;
			} else {
				$this->error = '数据库更新失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 删除字段
	 * @param string $fieldid 字段id
	 * @return boolean
	 */
	public function deleteField($fieldid) {
		//原字段信息
		$info = $this->where(array("fieldid" => $fieldid))->find();
		if (empty($info)) {
			$this->error = '该字段不存在！';
			return false;
		}
		//模型id
		$modelid = $info['modelid'];
		//完整表名获取 判断主表 还是副表
		$tablename = $this->getModelTableName($modelid, $info['issystem']);
		if (!$this->table_exists($tablename)) {
			$this->error = '数据表不存在！';
			return false;
		}
		//判断是否允许删除
		if (false === $this->isDelField($info['field'])) {
			$this->error = '该字段不允许被删除！';
			return false;
		}
		if ($this->deleteFieldSql($info['field'], C("DB_PREFIX") . $tablename)) {
			$this->where(array("fieldid" => $fieldid, "modelid" => $modelid))->delete();
			return true;
		} else {
			$this->error = '数据库表字段删除失败！';
			return false;
		}
	}

	/**
	 * 根据字段类型，增加对应的字段到相应表里面
	 * @param string $field_type 字段类型
	 * @param array $field 相关配置
	 * $field = array(
	 *      'tablename' 表名(完整表名)
	 *      'fieldname' 字段名
	 *      'maxlength' 最大长度
	 *      'minlength' 最小值
	 *      'defaultvalue' 默认值
	 *      'minnumber' 是否正整数 和整数 1为正整数，-1是为整数
	 *      'decimaldigits' 小数位数
     *      'comment' 字段注释
	 * )
     * @return boolean
	 */
	protected function addFieldSql($field_type, $field) {
		//表名
		$tablename = $field['tablename'];
		//字段名
		$fieldname = $field['fieldname'];
		//最大长度
		$maxlength = $field['maxlength'];
		//最小值
		$minlength = $field['minlength'];
		//默认值
		$defaultvalue = isset($field['defaultvalue']) ? $field['defaultvalue'] : '';
		//是否正整数 和整数 1为正整数，-1是为整数
		$minnumber = isset($field['minnumber']) ? $field['minnumber'] : 1;
		//小数位数
		$decimaldigits = isset($field['decimaldigits']) ? $field['decimaldigits'] : '';
        //字段注释
        $comment = isset($field['comment']) ? $field['comment'] : '';

		switch ($field_type) {
			case "varchar":
				if (!$maxlength) {
					$maxlength = 255;
				}
				$maxlength = min($maxlength, 255);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` VARCHAR( {$maxlength} ) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "tinyint":
				if (!$maxlength) {
					$maxlength = 3;
				}
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TINYINT( {$maxlength} ) " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "number": //特殊字段类型，数字类型，如果小数位是0字段类型为 INT,否则是FLOAT
				$minnumber = intval($minnumber);
				$defaultvalue = $decimaldigits == 0 ? intval($defaultvalue) : floatval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` " . ($decimaldigits == 0 ? 'INT' : 'FLOAT') . " " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "smallint":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` SMALLINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "mediumint":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "int":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "mediumtext":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` MEDIUMTEXT" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "text":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TEXT" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "date":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DATE" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "datetime":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "timestamp":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case 'readpoint': //特殊字段类型
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD  `readpoint` SMALLINT(5) unsigned NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "double":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` DOUBLE NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "float":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` FLOAT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "bigint":
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}` BIGINT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "longtext":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}`  LONGTEXT " . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '数据库字段添加失败！';
					return false;
				}
				break;
			case "char":
				$sql = "ALTER TABLE `{$tablename}` ADD `{$fieldname}`  CHAR(255) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "pages": //特殊字段类型
				$this->execute("ALTER TABLE `{$tablename}` ADD `paginationtype` TINYINT( 1 ) NOT NULL DEFAULT '0'" . " COMMENT '{$comment}'");
				$this->execute("ALTER TABLE `{$tablename}` ADD `maxcharperpage` MEDIUMINT( 6 ) NOT NULL DEFAULT '0'" . " COMMENT '{$comment}'");
				return true;
				break;
			default:
				return false;
				break;
		}
		return true;
	}

	/**
	 * 执行数据库表结构更改
	 * @param string $field_type 字段类型
	 * @param array $field 相关配置
	 * $field = array(
	 *      'tablename' 表名(完整表名)
	 *      'newfilename' 新字段名
	 *      'oldfilename' 原字段名
	 *      'maxlength' 最大长度
	 *      'minlength' 最小值
	 *      'defaultvalue' 默认值
	 *      'minnumber' 是否正整数 和整数 1为正整数，-1是为整数
	 *      'decimaldigits' 小数位数
     *      'comment' 字段注释
	 * )
     * @return boolean
	 */
	protected function editFieldSql($field_type, $field) {
		//表名
		$tablename = $field['tablename'];
		//原字段名
		$oldfilename = $field['oldfilename'];
		//新字段名
		$newfilename = $field['newfilename'] ? $field['newfilename'] : $oldfilename;
		//最大长度
		$maxlength = $field['maxlength'];
		//最小值
		$minlength = $field['minlength'];
		//默认值
		$defaultvalue = isset($field['defaultvalue']) ? $field['defaultvalue'] : '';
		//是否正整数 和整数 1为正整数，-1是为整数
		$minnumber = isset($field['minnumber']) ? $field['minnumber'] : 1;
		//小数位数
		$decimaldigits = isset($field['decimaldigits']) ? $field['decimaldigits'] : '';
        //字段注释
        $comment = isset($field['comment']) ? $field['comment'] : '';

		if (empty($tablename) || empty($newfilename)) {
			$this->error = '表名或者字段名不能为空！';
			return false;
		}

		switch ($field_type) {
			case 'varchar':
				//最大值
				if (!$maxlength) {
					$maxlength = 255;
				}
				$maxlength = min($maxlength, 255);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` VARCHAR( {$maxlength} ) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'tinyint':
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TINYINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'number': //特殊字段类型，数字类型，如果小数位是0字段类型为 INT,否则是FLOAT
				$minnumber = intval($minnumber);
				$defaultvalue = $decimaldigits == 0 ? intval($defaultvalue) : floatval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` " . ($decimaldigits == 0 ? 'INT' : 'FLOAT') . " " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'smallint':
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` SMALLINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'mediumint':
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` MEDIUMINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'int':
				$minnumber = intval($minnumber);
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'mediumtext':
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` MEDIUMTEXT" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'text':
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TEXT" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'date':
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DATE" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'datetime':
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'timestamp':
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case 'readpoint': //特殊字段类型
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `readpoint` SMALLINT(5) unsigned NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "double":
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` DOUBLE NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "float":
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}` FLOAT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "bigint":
				$defaultvalue = intval($defaultvalue);
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  BIGINT NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "longtext":
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  LONGTEXT" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			case "char":
				$sql = "ALTER TABLE `{$tablename}` CHANGE `{$oldfilename}` `{$newfilename}`  CHAR(255) NOT NULL DEFAULT '{$defaultvalue}'" . " COMMENT '{$comment}'";
				if (false === $this->execute($sql)) {
					$this->error = '字段结构更改失败！';
					return false;
				}
				break;
			//特殊自定义字段
			case 'pages':
				break;
			default:
				$this->error = "字段类型" . $field_type . "不存在相应信息！";
				return false;
				break;
		}
		return true;
	}

	/**
	 * 根据字段类型，删除对应的字段到相应表里面
	 * @param string $filename 字段名称
	 * @param string $tablename 完整表名
     * @return boolean
	 */
	protected function deleteFieldSql($filename, $tablename) {
		//不带表前缀的表名
		$noprefixTablename = str_replace(C("DB_PREFIX"), '', $tablename);
		if (empty($tablename) || empty($filename)) {
			$this->error = '表名或者字段名不能为空！';
			return false;
		}

		if (false === $this->table_exists($noprefixTablename)) {
			$this->error = '该表不存在！';
			return false;
		}
		switch ($filename) {
			case 'readpoint': //特殊字段类型
				$sql = "ALTER TABLE `{$tablename}` DROP `readpoint`;";
				if (false === $this->execute($sql)) {
					$this->error = '字段删除失败！';
					return false;
				}
				break;
			//特殊自定义字段
			case 'pages':
				if ($this->field_exists($noprefixTablename, "paginationtype")) {
					$this->execute("ALTER TABLE `{$tablename}` DROP `paginationtype`;");
				}
				if ($this->field_exists($noprefixTablename, "maxcharperpage")) {
					$this->execute("ALTER TABLE `{$tablename}` DROP `maxcharperpage`;");
				}
				break;
			default:
				$sql = "ALTER TABLE `{$tablename}` DROP `{$filename}`;";
				if (false === $this->execute($sql)) {
					$this->error = '字段删除失败！';
					return false;
				}
				break;
		}
		return true;
	}

	//生成模型字段缓存
	public function model_field_cache() {
		$cache = array();
		$modelList = M("Model")->select();
		foreach ($modelList as $info) {
			$data = $this->where(array("modelid" => $info['modelid'], "disabled" => 0))->order(" listorder ASC ")->select();
			$fieldList = array();
			if (!empty($data) && is_array($data)) {
				foreach ($data as $rs) {
					$fieldList[$rs['field']] = $rs;
				}
			}
			$cache[$info['modelid']] = $fieldList;
		}
		cache('ModelField', $cache);
		return $cache;
	}

}
