<?php

// +----------------------------------------------------------------------
// | 后台评论表情管理
// +----------------------------------------------------------------------

namespace Comments\Controller;

use Common\Controller\AdminBase;

class EmotionController extends AdminBase {

	private $db;
	public $emotionPath;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D('Comments/Emotion');
		$this->emotionPath = SITE_PATH . "/statics/images/emotion/";
	}

	//表情管理
	public function index() {
		//操作
		$action = I('get.action', 'trim', '');
		$dir = new \Dir($this->emotionPath);
		//取得表情目录全部表情信息
		$emotionPathArray = $dir->toArray();
		//取得已经添加的表情
		$emotionDataList = $this->db->order(array('vieworder' => 'ASC', 'emotion_id' => 'DESC'))->select();
		$emotionDataList = $emotionDataList ? $emotionDataList : array();
		//筛选出未添加的表情图片
		$noEmotionArray = array();
		foreach ($emotionPathArray as $k => $emotion) {
			//是否已经添加
			foreach ($emotionDataList as $val) {
				if ($emotion['filename'] == $val['emotion_icon']) {
					unset($emotionPathArray[$k]);
				}
			}
		}
		//其他操作
		if ($action) {
			//添加
			if ($action == 'add') {
				//添加表情
				$emotionid = I('post.emotionid', '', '');
				if (!$emotionid) {
					$this->error("请选择需要添加的表情！");
				}
				$orderid = I('post.orderid', '', '');
				$icon = I('post.icon', '', '');
				$emotionname = I('post.emotionname', '', '');
				C('TOKEN_ON', false);
				foreach ($emotionid as $k => $eid) {
					$info = $emotionPathArray[$eid];
					if (!$info) {
						$this->error("该表情已经添加或者不存在！");
					}
					if (!$emotionname[$k]) {
						$this->error("表情名称不能为空！");
					}
					//自动验证
					$data = $this->db->token(false)->create(array(
						'emotion_name' => $emotionname[$k],
						'emotion_icon' => $info['filename'],
						'vieworder' => 0,
						'isused' => 1,
					));
					if ($data) {
						$this->db->add($data);
					} else {
						$this->error($this->db->getError());
					}
				}
				$this->db->emotion_cache();
				$this->success("表情添加成功！");
				return true;
			} else if ($action == 'delete') {
//删除
				$emotion_id = I('get.emotion_id', 'intval', 0);
				if (!$emotion_id) {
					$this->error('请选择需要删除的表情！');
				}
				if (false !== $this->db->where(array('emotion_id' => $emotion_id))->delete()) {
					$this->db->emotion_cache();
					$this->success('删除成功！');
					return true;
				} else {
					$this->error('删除失败！');
					return false;
				}
			} else if ($action == 'save') {
//更新
				//是否启用
				$isused = I('post.isused', '', '');
				//表情id
				$emotionid = I('post.emotionid', '', '');
				if (!$emotionid) {
					$this->error('请选择需要更新的数据！');
					return false;
				}
				//排序
				$orderid = I('post.orderid', '', '');
				//表情名称
				$emotionname = I('post.emotionname', '', '');
				//更新
				foreach ($emotionid as $k => $eid) {
					$save = array();
					if ($emotionname[$k]) {
						$save['emotion_name'] = trim($emotionname[$k]);
					}
					$save['isused'] = $isused[$eid] ? 1 : 0;
					$save['vieworder'] = (int) $orderid[$k];
					$this->db->where(array('emotion_id' => $eid))->save($save);
				}
				$this->db->emotion_cache();
				$this->success("更新成功！");
				return true;
			}
		}

		$this->assign("noEmotion", $emotionPathArray);
		$this->assign("data", $emotionDataList);
		$this->display();
	}

}
