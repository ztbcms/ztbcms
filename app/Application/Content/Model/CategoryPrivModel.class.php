<?php

// +----------------------------------------------------------------------
// | 栏目权限与角色之间的授权
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class CategoryPrivModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('roleid', 'require', '角色ID不能为空！', 1, 'regex', 3),
		array('catid', 'require', '栏目ID不能为空！', 1, 'regex', 3),
		array('action', 'require', '权限动作不能为空！', 1, 'regex', 3),
	);

	/**
	 * 更新权限
	 * @param  $catid 栏目ID
	 * @param  $priv_datas
	 * @param  $is_admin 1为管理员
	 */
	public function update_priv($catid, $priv_datas, $is_admin = 1) {
		//删除旧的
		$this->where(array('catid' => $catid, 'is_admin' => $is_admin))->delete();
		if (is_array($priv_datas) && !empty($priv_datas)) {
			foreach ($priv_datas as $r) {
				$r = explode(',', $r);
				//动作
				$action = $r[0];
				//角色或者会员用户组
				$roleid = $r[1];
				$this->add(array('catid' => $catid, 'roleid' => $roleid, 'is_admin' => $is_admin, 'action' => $action));
			}
		}
	}

	/**
	 * 检查栏目权限
	 * @param $privs 权限数据
	 * @param $action 动作
	 * @param $roleid 角色
	 * @param $is_admin 是否为管理组
	 */
	public function check_category_priv($privs, $action, $roleid, $is_admin = 1) {
		$checked = '';
		foreach ($privs as $priv) {
			if ($priv['is_admin'] == $is_admin && $priv['roleid'] == $roleid && $priv['action'] == $action) {
				$checked = 'checked';
			}

		}
		return $checked;
	}

}

?>
