<?php

// +----------------------------------------------------------------------
// | 自定义页面模型
// +----------------------------------------------------------------------

namespace Template\Model;

use Common\Model\Model;

class CustomtempModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('tempname', 'require', '自定义模板名称不能为空！', 1, 'regex', 3),
		array('temppath', 'require', '路径不能为空！', 1, 'regex', 3),
		array('temptext', 'require', '页面内容不能为空！', 1, 'regex', 3),
		array('tempname', 'checkPname', '自定义页面文件名称有误！', 1, 'callback'),
		array('tempname,temppath', 'checkPath', '该路径已经存在自定义页面！', 1, 'callback', 1),
	);

	//检查相同路径的是否存在
	public function checkPath($data) {
		$info = $this->where($data)->find();
		if ($info) {
			return false;
		} else {
			return true;
		}
	}

	//自定义页面文件名称有误
	public function checkPname($data) {
		$name = explode('.', $data);
		if (count($name) == 2) {
			return true;
		} else {
			return false;
		}
	}

}
