<?php

// +----------------------------------------------------------------------
// | 模块绑定域名模型
// +----------------------------------------------------------------------

namespace Domains\Model;

use Common\Model\Model;

class DomainsModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('module', 'require', '必须选择模块！'),
		array('module', '', '该模块绑定已经存在！', 0, 'unique', 1),
		array('domain', 'require', '必须填写需要绑定的域名！'),
	);

	/**
	 * 添加
	 * @param array $data
	 * @return boolean
	 */
	public function AddDomains($data) {
		if (!$data) {
			return false;
		}
		if (in_array($data['module'], array("Domains", "Attachment", "Content", "Template"))) {
			$this->error = '该模块不允许绑定域名！';
			return false;
		}
		$id = $this->add($data);
		if ($id) {
			cache('Domains_list', NULL);
			cache('Module_Domains_list', NULL);
			return $id;
		}
		return false;
	}

	/**
	 * 编辑
	 * @param array $data
	 * @return boolean
	 */
	public function editDomains($data) {
		if (!$data) {
			return false;
		}
		if (in_array($data['module'], array("Domains", "Attachment", "Content", "Template"))) {
			$this->error = '该模块不允许绑定域名！';
			return false;
		}
		if ($this->save($data) !== false) {
			cache('Domains_list', NULL);
			cache('Module_Domains_list', NULL);
			return true;
		}
		return false;
	}

	//更新缓存
	public function domains_cache() {
		$Domains_data = $this->where(array("status" => 1))->field(array("module", "domain"))->select();
		foreach ($Domains_data as $r) {
			$r['domain'] = explode("|", $r['domain']);
			foreach ($r['domain'] as $dom) {
				$Domains_cache[$dom] = $r['module'];
			}
		}
		//缓存 域名->模块
		cache("Domains_list", $Domains_cache);
		return $Domains_cache;
	}

	public function domains_domainslist() {
		$Domains_data = $this->where(array("status" => 1))->field(array("module", "domain"))->select();
		foreach ($Domains_data as $r) {
			$r['domain'] = explode("|", $r['domain']);
			$Domains_list[$r['module']] = $r['domain'][0];
		}
		//缓存 模块->绑定的域名
		cache("Module_Domains_list", $Domains_list);
		return $Domains_list;
	}

}
