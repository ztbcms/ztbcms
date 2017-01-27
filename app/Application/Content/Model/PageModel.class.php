<?php

// +----------------------------------------------------------------------
// | 单页模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class PageModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('catid', 'require', '栏目ID不能为空！', 0, 'regex', 3),
		array('title', 'require', '标题不能为空！', 1, 'regex', 3),
		array('content', 'require', '内容不能为空！', 1, 'regex', 3),
	);
	//自动完成
	protected $_auto = array(
		//array(填充字段,填充内容,填充条件,附加规则)
		array('updatetime', 'time', 1, 'function'),
	);

	/**
	 * 根据栏目ID获取内容
	 * @param string $catid 栏目ID
	 * @return boolean
	 */
	public function getPage($catid) {
		if (empty($catid)) {
			return false;
		}
		return $this->where(array('catid' => $catid))->find();
	}

	/**
	 * 更新单页内容
	 * @param array $post 表单数据
	 * @return boolean
	 */
	public function savePage($post) {
		if (empty($post)) {
			$this->error = '内容不能为空！';
			return false;
		}
		$data = $post['info'];
		//表单令牌
		$data[C("TOKEN_NAME")] = $post[C("TOKEN_NAME")];
		$catid = $data['catid'];
		//单页内容
		$info = $this->where(array('catid' => $catid))->find();
		if ($info) {
			unset($data['catid']);
		}
		$data = $this->token(false)->create($data, isset($data['catid']) ? 1 : 2);
		if ($data) {
			//取得标题颜色
			if (isset($post['style_color'])) {
				//颜色选择为隐藏域 在这里进行取值
				$data['style'] = $post['style_color'] ? strip_tags($post['style_color']) : '';
				//标题加粗等样式
				if (isset($post['style_font_weight'])) {
					$data['style'] = $data['style'] . ($post['style_font_weight'] ? ';' : '') . strip_tags($post['style_font_weight']);
				}
			}
			if ($info) {
				if ($this->where(array('catid' => $catid))->save($data) !== false) {
					//更新附件状态，把相关附件和文章进行管理
					service("Attachment")->api_update('', 'catid-' . $catid, 1);
					return true;
				}
			} else {
				if ($this->add($data) !== false) {
					//更新附件状态，把相关附件和文章进行管理
					service("Attachment")->api_update('', 'catid-' . $catid, 1);
					return true;
				}
			}
			$this->error = '操作失败！';
			return false;
		} else {
			return false;
		}
	}

}
