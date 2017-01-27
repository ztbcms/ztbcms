<?php

// +----------------------------------------------------------------------
// | 自定义列表模型
// +----------------------------------------------------------------------

namespace Template\Model;

use Common\Model\Model;

class CustomlistModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('name', 'require', '自定义列表名称不能为空！'),
		array('name', '', '该自定义列表已经存在！', 0, 'unique', 1),
		array('title', 'require', '自定义列表页面标题不能为空！'),
		array('totalsql', 'require', '数据统计SQL不能为空！'),
		array('listsql', 'require', '数据查询SQL不能为空！'),
		array('lencord', 'require', '每页显示数量不能为空！'),
	);
	//array(填充字段,填充内容,[填充条件,附加规则])
	protected $_auto = array(
		array('createtime', 'time', 1, 'function'),
	);

	/**
	 * 添加自定义列表
	 * @param array $post 表单提交数据
	 * @return boolean
	 */
	public function addCustomlist($post) {
		if (empty($post)) {
			$this->error = '自定义列表名称不能为空！';
			return false;
		}
		//检查是否使用已有URL规则
		if ((int) $post['isurltype'] == 1) {
			//添加urlruleid自动验证规则
			array_push($this->_validate, array('urlruleid', 'require', 'URL规则不能为空！', 1, 'regex', 3));
		} else {
			//添加urlrule自动验证规则
			array_push($this->_validate, array('urlrule', 'require', 'URL规则不能为空！', 1, 'regex', 3));
		}
		//模板
		if (empty($post['listpath'])) {
			//添加template自动验证规则
			array_push($this->_validate, array('template', 'require', '模板内容不能为空！', 1, 'regex', 3));
		} else {
			//添加listpath自动验证规则
			array_push($this->_validate, array('listpath', 'require', '列表模板不能为空！', 1, 'regex', 3));
		}
		$data = $this->create($post, 1);
		if (!$data) {
			return false;
		}
		$id = $this->add($data);
		if ($id) {
			//更新访问地址
			$urlArray = CMS()->Url->createListUrl((int) $id);
			if ($urlArray !== false) {
				$this->where(array('id' => $id))->save(array('url' => $urlArray['url']));
			}
			return $id;
		} else {
			$this->error = '自定义列表添加失败！';
			return false;
		}
	}

	/**
	 * 编辑自定义列表
	 * @param array $post 表单提交数据
	 * @return boolean
	 */
	public function editCustomlist($post) {
		if (empty($post)) {
			$this->error = '自定义列表名称不能为空！';
			return false;
		}
		$id = $post['id'];
		//原本数据
		$info = $this->where(array('id' => $id))->find();
		if (empty($info)) {
			$this->error = '该自定义列表不存在！';
			return false;
		}
		unset($post['id']);
		//检查是否使用已有URL规则
		if ((int) $post['isurltype'] == 1) {
			//添加urlruleid自动验证规则
			array_push($this->_validate, array('urlruleid', 'require', 'URL规则不能为空！', 1, 'regex', 3));
			$post['urlrule'] = '';
		} else {
			//添加urlrule自动验证规则
			array_push($this->_validate, array('urlrule', 'require', 'URL规则不能为空！', 1, 'regex', 3));
			$post['urlruleid'] = 0;
		}
		//模板
		if (empty($post['listpath'])) {
			//添加template自动验证规则
			array_push($this->_validate, array('template', 'require', '模板内容不能为空！', 1, 'regex', 3));
			$post['listpath'] = '';
		} else {
			//添加listpath自动验证规则
			array_push($this->_validate, array('listpath', 'require', '列表模板不能为空！', 1, 'regex', 3));
			$post['template'] = '';
		}
		$data = $this->create($post, 2);
		if (!$data) {
			return false;
		}
		if ($this->where(array('id' => $id))->save($data) !== false) {
			//更新访问地址
			$urlArray = CMS()->Url->createListUrl((int) $id);
			if ($urlArray !== false) {
				$this->where(array('id' => $id))->save(array('url' => $urlArray['url']));
			}
			return true;
		} else {
			$this->error = '自定义列表修改失败！';
			return false;
		}
	}

	/**
	 * 删除自定义列表
	 * @param string $id 自定义列表ID
	 * @return boolean
	 */
	public function deleteCustomlist($id) {
		if (empty($id)) {
			$this->error = '请指定需要删除的自定义列表！';
			return false;
		}
		//查询出信息
		$info = $this->where(array('id' => $id))->find();
		if (empty($info)) {
			$this->error = '该自定义列表不存在！';
			return false;
		}
		//删除生成的静态文件
		//计算总数
		$countArray = $this->execute($info['totalsql']);
		if (!empty($countArray)) {
			$count = $countArray[0]['total'];
			//分页总数
			$paging = ceil($count / $info['lencord']);
			for ($i = 1; $i <= $paging; $i++) {
				$customlistUrl = CMS()->Url->createListUrl($info, $i);
				if ($customlistUrl) {
					//生成路径
					$htmlpath = SITE_PATH . $customlistUrl["path"];
					//删除
					unlink($htmlpath);
				}
			}
		}

		if ($this->where(array('id' => $id))->delete() !== false) {
			return true;
		} else {
			$this->error = '删除失败！';
			return false;
		}
	}

}
