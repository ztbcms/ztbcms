<?php

// +----------------------------------------------------------------------
// | 网站配置模型
// +----------------------------------------------------------------------

namespace Common\Model;

class ConfigModel extends Model {

	/**
	 * 增加扩展配置项
	 * @param array $data
	 * @return boolean
	 */
	public function extendAdd($data) {
		if (empty($data)) {
			$this->error = '数据不能为空！';
			return false;
		}
		if (empty($data['setting']['title'])) {
			$this->error = '名称不能为空！';
			return false;
		}
		$data['fieldname'] = strtolower($data['fieldname']);
		$db = M('ConfigField');
		//验证规则
		$validate = array(
			array('fieldname', 'require', '键名不能为空！', 1, 'regex', 3),
			array('fieldname', '', '该键名已经存在！', 0, 'unique', 1),
			array('type', 'require', '类型不能为空！', 1, 'regex', 3),
			array('fieldname', '/^[a-z_0-9]+$/i', '键名只支持英文、数字、下划线！', 0, 'regex', 3),
		);
		$data = $db->validate($validate)->create($data);
		if ($data) {
			$data['createtime'] = time();
			//检查config表是否已经存在
			if ($this->where(array('varname' => $data['fieldname']))->count()) {
				$this->error = '该键名已经存在！';
				return false;
			}
			$setting = $data['setting'];
			if ($data['type'] == 'radio' || $data['type'] == 'select') {
				$option = array();
				$optionList = explode("\n", $setting['option']);
				if (is_array($optionList)) {
					foreach ($optionList as $rs) {
						$rs = explode('|', $rs);
						if (!empty($rs)) {
							$option[] = array(
								'title' => $rs[0],
								'value' => $rs[1],
							);
						}
					}
					$setting['option'] = $option;
				}
			}
			$data['setting'] = serialize($setting);
			$id = $db->add($data);
			if ($id) {
				//增加配置项
				$this->add(array(
					'varname' => $data['fieldname'],
					'info' => $setting['title'],
					'groupid' => 2,
					'value' => '',
				));
				return $id;
			} else {
				$this->error = '添加失败！';
				return false;
			}
		} else {
			$this->error = $db->getError();
			return false;
		}
	}

	/**
	 * 删除扩展配置项
	 * @param string $fid 配置项ID
	 * @return boolean
	 */
	public function extendDel($fid) {
		if (empty($fid)) {
			$this->error = '请指定需要删除的扩展配置项！';
			return false;
		}
		$db = M('ConfigField');
		//扩展字段详情
		$info = $db->where(array('fid' => $fid))->find();
		if (empty($info)) {
			$this->error = '该扩展配置项不存在！';
			return false;
		}
		//删除
		if ($this->where(array('varname' => $info['fieldname'], 'groupid' => 2))->delete() !== false) {
			$db->where(array('fid' => $fid))->delete();
			return true;
		} else {
			$this->error = '删除失败！';
			return false;
		}
	}

	/**
	 * 更新扩展配置项
	 * @param array $data 数据
	 * @return boolean
	 */
	public function saveExtendConfig($data) {
		if (empty($data) || !is_array($data)) {
			$this->error = '配置数据不能为空！';
			return false;
		}
		//令牌验证
		if (!$this->autoCheckToken($data)) {
			$this->error = L('_TOKEN_ERROR_');
			return false;
		}
		//去除token
		unset($data[C("TOKEN_NAME")]);
		foreach ($data as $key => $value) {
			if (empty($key)) {
				continue;
			}
			$saveData = array();
			$saveData["value"] = trim($value);
			if ($this->where(array("varname" => $key, 'groupid' => 2))->save($saveData) === false) {
				$this->error = "更新到{$key}项时，更新失败！";
				return false;
			}
		}
		return true;
	}

	/**
	 * 更新网站配置项
	 * @param type $data 数据
	 * @return boolean
	 */
	public function saveConfig($data) {
		if (empty($data) || !is_array($data)) {
			$this->error = '配置数据不能为空！';
			return false;
		}
		//令牌验证
		if (!$this->autoCheckToken($data)) {
			$this->error = L('_TOKEN_ERROR_');
			return false;
		}
		//去除token
		unset($data[C("TOKEN_NAME")]);
		foreach ($data as $key => $value) {
			if (empty($key)) {
				continue;
			}
			$saveData = array();
			$saveData["value"] = trim($value);
			if ($this->where(array("varname" => $key))->save($saveData) === false) {
				$this->error = "更新到{$key}项时，更新失败！";
				return false;
			}
		}
		return true;
	}

	/**
	 * 保存高级配置
	 * @param array $data 配置信息
	 * @return boolean
	 */
	public function addition($data) {
		if (empty($data)) {
			$this->error = '没有数据！';
			return false;
		}
		//配置文件地址
		$filename = COMMON_PATH . 'Conf/addition.php';
		//检查文件是否可写
		if (is_writable($filename) == false) {
			$this->error = '请检查[' . COMMON_PATH . 'Conf/addition.php' . ']文件权限是否可写！';
			return false;
		}
		if (isset($data[C('TOKEN_NAME')])) {
			unset($data[C('TOKEN_NAME')]);
		}
		//默认值
		$data['DEFAULT_GROUP'] = $data['DEFAULT_GROUP'] ? $data['DEFAULT_GROUP'] : "Contents";
		$data['TOKEN_ON'] = (int) $data['TOKEN_ON'] ? true : false;
		$data['URL_MODEL'] = isset($data['URL_MODEL']) ? (int) $data['URL_MODEL'] : 0;
		$data['DEFAULT_TIMEZONE'] = $data['DEFAULT_TIMEZONE'] ? $data['DEFAULT_TIMEZONE'] : "PRC";
		$data['DATA_CACHE_TYPE'] = $data['DATA_CACHE_TYPE'] ? $data['DATA_CACHE_TYPE'] : "File";
		$data['DEFAULT_LANG'] = $data['DEFAULT_LANG'] ? $data['DEFAULT_LANG'] : "zh-cn";
		$data['DEFAULT_AJAX_RETURN'] = $data['DEFAULT_AJAX_RETURN'] ? $data['DEFAULT_AJAX_RETURN'] : "JSON";
		$data['SESSION_OPTIONS'] = $data['SESSION_OPTIONS'] ? $data['SESSION_OPTIONS'] : array();
		$data['URL_PATHINFO_DEPR'] = $data['URL_PATHINFO_DEPR'] ? $data['URL_PATHINFO_DEPR'] : "/";
		//URL区分大小写设置
		$data['URL_CASE_INSENSITIVE'] = (int) $data['URL_CASE_INSENSITIVE'] ? true : false;
		//云平台开关
		$data['CLOUD_ON'] = (int) $data['CLOUD_ON'] ? true : false;
		//函数加载
		$data['LOAD_EXT_FILE'] = trim($data['LOAD_EXT_FILE']);
		//默认分页模板
		$data['PAGE_TEMPLATE'] = str_replace("\n", "", trim($data['PAGE_TEMPLATE']));

		//**********************检测一些设置，会导致网站瘫痪的**********************
		//缓存类型检测
		if ($data['DATA_CACHE_TYPE'] == 'Memcache') {
			if (class_exists('Memcache') == false) {
				$this->error = '您的环境不支持Memcache，无法开启！';
				return false;
			}
		}
		if ($data['DATA_CACHE_TYPE'] == 'Redis') {
			if (class_exists('Redis') == false) {
				$this->error = '您的环境不支持Redis，无法开启！';
				return false;
			}
		}
		if ($data['DATA_CACHE_TYPE'] == 'Xcache') {
			if (function_exists('xcache_set') == false) {
				$this->error = '您的环境不支持Xcache，无法开启！';
				return false;
			}
		}
		//***********************END************************************

		file_exists($filename) or touch($filename);
		$return = var_export($data, TRUE);
		if ($return) {
			if (file_put_contents($filename, "<?php \r\n return " . $return . ";")) {
				return true;
			} else {
				$this->error = '配置更新失败！';
				return false;
			}
		} else {
			$this->error = '配置数据为空！';
			return false;
		}
	}

	/**
	 * 更新缓存
	 * @return array
	 */
	public function config_cache() {
		$data = M("Config")->getField("varname,value");
		cache("Config", $data);
		return $data;
	}

	// 写入数据前的回调方法 包括新增和更新
	protected function _before_write(&$data) {
		cache('Config', NULL);
	}

}
