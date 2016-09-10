<?php

// +----------------------------------------------------------------------
// | 友情链接
// +----------------------------------------------------------------------

namespace Links\Model;

use Common\Model\Model;

class LinksModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('url', 'require', '链接地址不能为空！', 1, 'regex', 3),
		array('name', 'require', '链接名称不能为空！', 1, 'regex', 3),
		array('name', '', '该链接名称已经存在！', 0, 'unique', 3),
		//array('termsid', 'require', '分类不能为空！', 1, 'regex', 3),
	);

}
