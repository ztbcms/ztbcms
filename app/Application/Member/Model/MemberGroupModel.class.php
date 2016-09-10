<?php

// +----------------------------------------------------------------------
// | 前台会员用户组模型
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class MemberGroupModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('name', 'require', '会员组名称不能为空！'),
		array('name', '', '该会员组已经存在！', 0, 'unique', 3),
		array('icon', 'require', '用户组图标不能为空！', 1),
		array('point', 'require', '积分不能为空！'),
		array('point', 'number', '积分只能是数字！'),
		array('starnum', 'require', '星星数不能为空！'),
		array('starnum', 'number', '星星数只能是数字！'),
	);
	protected $_auto = array(
		// 对expand字段进行序列化处理
		array('expand', 'serialize', 3, 'function'),
		//是否是系统组
		array('issystem', '0'),
		//是否允许访问
		array('allowvisit', '1'),
		//是否禁用
		array('disabled', '0'),
	);

	/**
	 * 编辑会员组
	 * @param type $data 数据
	 * @return boolean
	 */
	public function groupEdit($data) {
		if (!is_array($data)) {
			return false;
		}
		$data['allowpost'] = $data['allowpost'] ? $data['allowpost'] : 0;
		$data['allowpostverify'] = $data['allowpostverify'] ? $data['allowpostverify'] : 0;
		$data['allowupgrade'] = $data['allowupgrade'] ? $data['allowupgrade'] : 0;
		$data['allowsendmessage'] = $data['allowsendmessage'] ? $data['allowsendmessage'] : 0;
		$data['allowattachment'] = $data['allowattachment'] ? $data['allowattachment'] : 0;
		$data['allowsearch'] = $data['allowsearch'] ? $data['allowsearch'] : 0;
		$status = $this->save($data);
		if ($status !== false) {
			//更新附件状态
			if ($data['icon']) {
				//更新附件状态
				service("Attachment")->api_update('', 'member_group-' . $data['groupid'], 1);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 添加会员组
	 * @param type $data 提交数据
	 * @return boolean
	 */
	public function groupAdd($data) {
		if (!is_array($data)) {
			return false;
		}
		$groupid = $this->add($data);
		if ($groupid !== false) {
			//更新附件状态
			if ($data['icon']) {
				//更新附件状态
				service("Attachment")->api_update('', 'member_group-' . $groupid, 1);
			}
			return $groupid;
		} else {
			return false;
		}
	}

	/**
	 * 删除用户组
	 * @param type $groupid 用户组ID，可以是数组
	 * @return boolean
	 */
	public function groupDelete($groupid) {
		if (empty($groupid)) {
			$this->error = '没有指定需要删除的会员组别！';
			return false;
		}
		$where = array();
		if (is_array($groupid)) {
			foreach ($groupid as $gid) {
				$info = $this->where(array("groupid" => $gid))->find();
				if ($info['issystem']) {
					$this->error = '系统用户组[' . $info['name'] . ']不能删除！';
					return false;
				}
				//删除附件
				service("Attachment")->api_delete('member_group-' . $id);
			}
			$where['groupid'] = array('IN', $groupid);
		} else {
			$info = $this->where(array("groupid" => $groupid))->find();
			if ($info['issystem']) {
				$this->error = '系统用户组[' . $info['name'] . ']不能删除！';
				return false;
			}
			$where['groupid'] = $groupid;
			//删除附件
			service("Attachment")->api_delete('member_group-' . $groupid);
		}
		if (false !== $this->where($where)->delete()) {
			return true;
		} else {
			$this->error = '删除失败！';
			return false;
		}
	}

	//生成会员组缓存
	public function membergroup_cache() {
		$data = $this->select();
		$return = array();
		foreach ($data as $k => $v) {
			if ($v['expand']) {
				$v['expand'] = unserialize($v['expand']);
			} else {
				$v['expand'] = array();
			}
			$return[$v['groupid']] = $v;
		}
		cache("Member_group", $return);
		return $return;
	}

}
