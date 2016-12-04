<?php

// +----------------------------------------------------------------------
// | 推荐位模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class PositionDataModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
	);

	/**
	 * 推荐位中被推送过来的信息编辑
	 * @param array $data
	 * @return boolean
	 */
	public function positionEdit($data) {
		if (!is_array($data)) {
			return false;
		}
		if (!$data['posid'] || !$data['modelid'] || !$data['id']) {
			return false;
		} else {
			$posid = $data['posid'];
			$modelid = $data['modelid'];
			$id = $data['id'];
			unset($data['posid'], $data['modelid'], $data['id']);
		}
		$content_input = new \content_input($modelid);
		$data['data'] = $content_input->get($data['data'], 2);
		$data['data'] = serialize($data['data']);
		if ($this->where(array('id' => $id, 'modelid' => $modelid, 'posid' => $posid))->save($data) !== false) {
			service('Attachment')->api_update('', 'position-' . $modelid . '-' . $id, 1);
			return true;
		}
		return false;
	}

	/**
	 * 信息从推荐位中移除
	 * @param string $posid 推荐位id
	 * @param string $id 信息id
	 * @param string $modelid] 模型id
     * @return boolean
	 */
	public function deleteItem($posid, $id, $modelid) {
		if (!$posid || !$id || !$modelid) {
			return false;
		}
		$where = array();
		$where['id'] = $id;
		$where['modelid'] = $modelid;
		$where['posid'] = intval($posid);
		if ($this->where($where)->delete() !== false) {
			$this->contentPos($id, $modelid);
			//删除相关联的附件
			service('Attachment')->api_delete('position-' . $modelid . '-' . $id);
			return false;
		} else {
			return false;
		}
	}

	/**
	 * 根据模型ID和信息ID删除推荐信息
	 * @param string $modelid
	 * @param string $id
	 * @return boolean
	 */
	public function deleteByModeId($modelid, $id) {
		if (empty($modelid) || empty($id)) {
			return false;
		}
		$where = array();
		$where['id'] = $id;
		$where['modelid'] = $modelid;
		if ($this->where($where)->delete() !== false) {
			$this->contentPos($id, $modelid);
			//删除相关联的附件
			service('Attachment')->api_delete('position-' . $modelid . '-' . $id);
			return false;
		} else {
			return false;
		}
	}

	/**
	 * 更新信息推荐位状态
	 * @param string $id 信息id
	 * @param string $modelid 模型id
	 * @return boolean
	 */
	public function contentPos($id, $modelid) {
		$id = intval($id);
		$modelid = intval($modelid);
		$info = $this->where(array('id' => $id, 'modelid' => $modelid))->find();
		if ($info) {
			$posids = 1;
		} else {
			$posids = 0;
		}
		//更改文章推荐位状态
		$status = ContentModel::getInstance($modelid)->where(array('id' => $id))->save(array('posid' => $posids));
        if (false !== $status && $status > 0) {
			return true;
		} else {
			//有可能副表
			$tablename = getModel($modelid, 'tablename');
			if ($this->field_exists("{$tablename}_data", 'posid')) {
				return M($tablename . 'Data')->where(array('id' => $id))->save(array('posid' => $posids)) !== false ? true : false;
			}
			return false;
		}
	}

}
