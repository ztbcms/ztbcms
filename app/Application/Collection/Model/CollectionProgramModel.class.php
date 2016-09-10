<?php

// +----------------------------------------------------------------------
// | 采集模型
// +----------------------------------------------------------------------

namespace Collection\Model;

use Common\Model\Model;

class CollectionProgramModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('name', 'require', '方案名称不能为空！'),
		array('name', '', '该方案名称已经存在！', 0, 'unique', 1),
		array('nodeid', 'require', '采集节点ID不能为空！'),
		array('modelid', 'require', '模型ID不能为空！'),
		array('catid', 'require', '栏目ID不能为空！'),
	);

}
