<?php

// +----------------------------------------------------------------------
// | 内容模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\RelationModel;
use Think\Model;

class ContentModel extends RelationModel {

	//当前模型id
	public $modelid = 0;
	//自动验证 array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array();
	//自动完成 array(填充字段,填充内容,[填充条件,附加规则])
	protected $_auto = array();
	//定义为虚拟模型
	//protected $autoCheckFields = false;

	/**
	 * 取得内容模型实例
	 * @param string $modelid 模型ID
	 * @return ContentModel|NULL
	 */
	static public function getInstance($modelid) {
		//静态成品变量 保存全局实例
		static $_instance = NULL;
		if (is_null($_instance[$modelid]) || !isset($_instance[$modelid])) {
			//内容模型缓存
			$modelCache = cache("Model");
			if (empty($modelCache[$modelid])) {
				return null;
			}
			$tableName = $modelCache[$modelid]['tablename'];
			$_instance[$modelid] = new ContentModel(ucwords($tableName));
			//内容模型
			if ($modelCache[$modelid]['type'] == 0) {
				$_instance[$modelid]->_validate = array(
					//栏目
					array('catid', 'require', '请选择栏目！', 1, 'regex', 1),
					array('catid', 'isUltimate', '该模型非终极栏目，无法添加信息！', 1, 'callback', 1),
					//标题
					array('title', 'require', '标题必须填写！', 1, 'regex', 1),
				);
			}
			//设置模型id
			$_instance[$modelid]->modelid = $modelid;
		}
		return $_instance[$modelid];
	}

	/**
	 * 进行关联查询
	 * @access public
	 * @param mixed $name 关联名称
	 * @return Model
	 */
	public function relation($name) {
		//关联关系
		$this->relationShipsDefine($this->name);
		return parent::relation($name);
	}

	/**
	 * 关联定义
	 * @param array|string $tableName 关联定义条件。如果是数组，直接定义配置好的关联条件，如果是字符串，则当作表名进行定义一对一关联条件
     * @return string
	 */
	public function relationShipsDefine($tableName) {
		if (is_array($tableName)) {
			$this->_link = $tableName;
		} else {
			$tableName = ucwords($tableName);
			//进行内容表关联定义
			$this->_link = array(
				//主表 附表关联
				$this->getRelationName($tableName) => array(
					"mapping_type" => self::HAS_ONE,
					"class_name" => $tableName . "_data",
					"foreign_key" => "id",
				),
			);
		}
		return $this->_link;
	}

	/**
	 * 获取关联定义名称
	 * @param string $tableName 表名
	 * @return string
	 */
	public function getRelationName($tableName = '') {
		if (empty($tableName)) {
			$tableName = $this->name;
		}
		return ucwords($tableName) . 'Data';
	}

	/**
	 * 对通过连表查询的数据进行合并处理
	 * @param array $data
     * @return array
	 */
	public function dataMerger(&$data) {
		$relationName = $this->getRelationName();
		$datafb = $data[$relationName];
		unset($datafb['id'], $data[$relationName]);
		if (is_array($datafb)) {
			$data = array_merge($data, $datafb);
		}
		return $data;
	}

	/**
	 * 创建数据对象 但不保存到数据库
	 * @access public
	 * @param mixed $data 创建数据
	 * @param string $type 状态
	 * @param boolean $name 关联名称
	 * @return mixed
	 */
	public function create($data = '', $type = '', $name = true) {
		//是否使用关联
		if (empty($this->options['link'])) {
			return parent::create($data, $type);
		}
		// 如果没有传值默认取POST数据
		if (empty($data)) {
			$data = $_POST;
		} elseif (is_object($data)) {
			$data = get_object_vars($data);
		}
		// 验证数据
		if (empty($data) || !is_array($data)) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}
		//关联定义
		$relation = $this->_link;
		//验证规则
		$_validate = $this->_validate;
		//自动完成
		$_auto = $this->_auto;
		if (!empty($relation)) {
			// 遍历关联定义
			foreach ($relation as $key => $val) {
				// 操作制定关联类型
				$mappingName = $val['mapping_name'] ? $val['mapping_name'] : $key; // 映射名称
				if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName, $name))) {
					//关联类名
					$mappingClass = !empty($val['class_name']) ? $val['class_name'] : $key;
					//关联类型
					$mappingType = !empty($val['mapping_type']) ? $val['mapping_type'] : $val;
					switch ($mappingType) {
						case self::HAS_ONE:
							//是否有副表数据
							$isLinkData = false;
							//数据
							if (isset($data[$mappingName])) {
								$sideTablesData = $data[$mappingName];
								unset($data[$mappingName]);
								$isLinkData = true;
							}
							//自动验证
							if (isset($_validate[$mappingName])) {
								$_validateSideTables = $_validate[$mappingName];
								unset($_validate[$mappingName], $this->_validate[$mappingName]);
							}
							//自动完成
							if (isset($_auto[$mappingName])) {
								$_autoSideTables = $_auto[$mappingName];
								unset($_auto[$mappingName], $this->_auto[$mappingName]);
							}
							//进行主表create
							if ($type == 1) {
								$data = parent::create($data, $type);
							} else {
								if (empty($data)) {
									$data = true;
									if (empty($sideTablesData)) {
										$this->error = L('_DATA_TYPE_INVALID_');
										return false;
									}
								} else {
									$data = parent::create($data, $type);
								}
								//存在主键副表也自动加上
								if (!empty($data[$this->getPk()])) {
									$sideTablesData[$this->getPk()] = $data[$this->getPk()];
								}
							}
							//下面进行的是副表验证操作，这里需要检查特殊情况，例如没有开启关联的，其实不用进行下面
							if (empty($this->options['link']) || empty($isLinkData)) {
								return $data;
							}
							//关闭表单验证
							C('TOKEN_ON', false);
							//不管成功或者失败，清空_validate和_auto
							$this->_validate = $this->_auto = array();
							if ($data) {
								if (empty($sideTablesData)) {
									return $data;
								} else {
									$sideTablesData = M($mappingClass)->validate($_validateSideTables)->auto($_autoSideTables)->create($sideTablesData, $type);
									if ($sideTablesData) {
										if (is_array($data)) {
											return array_merge($data, array($mappingName => $sideTablesData));
										} else {
											return array($mappingName => $sideTablesData);
										}
									} else {
										$this->error = M($mappingClass)->getError();
										return false;
									}
								}
							} else {
								return false;
							}
							break;
						default:
							return parent::create($data, $type);
							break;
					}
				}
			}
		}
		return parent::create($data, $type);
	}

	/**
	 * 是否终极栏目
	 * @param int $catid
	 * @return boolean
	 */
	public function isUltimate($catid) {
		if (getCategory($catid, 'child')) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 添加验证规则
	 * @param array $validate 规则
	 * @param boolean $issystem 是否主表
	 * @param boolean|string $name 关联名称
	 * @return array
	 */
	public function addValidate(array $validate, $issystem = true, $name = true) {
		$relation = $this->_link;
		if (!empty($relation)) {
			// 遍历关联定义
			foreach ($relation as $key => $val) {
				// 操作制定关联类型
				$mappingName = $val['mapping_name'] ? $val['mapping_name'] : $key; // 映射名称
				if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName, $name))) {
					//关联类名
					$mappingClass = !empty($val['class_name']) ? $val['class_name'] : $key;
					if ($issystem) {
						$this->_validate[] = $validate;
					} else {
						$this->_validate[$mappingName][] = $validate;
					}
				}
			}
		}
		return $this->_validate;
	}

	/**
	 * 添加自动完成
	 * @param array $auto 规则
	 * @param boolean $issystem 是否主表
	 * @param boolean $name 关联名称
	 * @return array
	 */
	public function addAuto(array $auto, $issystem = true, $name = true) {
		$relation = $this->_link;
		if (!empty($relation)) {
			// 遍历关联定义
			foreach ($relation as $key => $val) {
				// 操作制定关联类型
				$mappingName = $val['mapping_name'] ? $val['mapping_name'] : $key; // 映射名称
				if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName, $name))) {
					//关联类名
					$mappingClass = !empty($val['class_name']) ? $val['class_name'] : $key;
					if ($issystem) {
						$this->_auto[] = $auto;
					} else {
						$this->_auto[$mappingName][] = $auto;
					}
				}
			}
		}
		return $this->_auto;
	}

	/**
	 * 信息锁定
	 * @param int $catid 栏目ID
	 * @param int $id 信息ID
	 * @param int $userid 用户名ID
	 * @return boolean
	 */
	public function locking($catid, $id, $userid = 0) {
		$db = M("Locking");
		$time = time();
		//锁定有效时间
		$Lock_the_effective_time = 300;
		if (empty($userid)) {
			$userid = \Admin\Service\User::getInstance()->id;
		}
		$where = array();
		$where['catid'] = array("EQ", $catid);
		$where['id'] = array("EQ", $id);
		$where['locktime'] = array("EGT", $time - $Lock_the_effective_time);
		$info = $db->where($where)->find();
		if ($info && $info['userid'] != \Admin\Service\User::getInstance()->id) {
			$this->error = '该信息已经被用户【<font color=\"red\">' . $info['username'] . '</font>】锁定~请稍后在修改！';
			return false;
		}
		//删除失效的
		$where = array();
		$where['locktime'] = array("LT", $time - $Lock_the_effective_time);
		$db->where($where)->delete();
		return true;
	}

	/**
	 * 内容模型处理类生成
	 */
	public static function classGenerate() {
		//字段类型存放目录
		$fields_path = APP_PATH . 'Content/Fields/';
		//内置字段类型列表
		$fields = include $fields_path . 'fields.inc.php';
		$fields = $fields ?: array();
		//更新内容模型数据处理相关类
		$classtypes = array('form', 'input', 'output', 'update', 'delete');
		//缓存生成路径
		$cachemodepath = RUNTIME_PATH;
		foreach ($classtypes as $classtype) {
			$content_cache_data = file_get_contents($fields_path . "content_$classtype.class.php");
			$cache_data = '';
			//循环字段列表，把各个字段的 form.inc.php 文件合并到 缓存 content_form.class.php 文件
			foreach ($fields as $field => $fieldvalue) {
				//检查文件是否存在
				if (file_exists($fields_path . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php')) {
					//读取文件，$classtype.inc.php
					$ca = file_get_contents($fields_path . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php');
					$cache_data .= str_replace(array("<?php", "?>"), "", $ca);
				}
			}
			$content_cache_data = str_replace('##{字段处理函数}##', $cache_data, $content_cache_data);
			//写入缓存
			file_put_contents($cachemodepath . 'content_' . $classtype . '.class.php', $content_cache_data);
			//设置权限
			chmod($cachemodepath . 'content_' . $classtype . '.class.php', 0777);
			unset($cache_data);
		}
	}

}
