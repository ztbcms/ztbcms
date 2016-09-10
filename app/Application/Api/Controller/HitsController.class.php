<?php

// +----------------------------------------------------------------------
// | 点击数
// +----------------------------------------------------------------------

namespace Api\Controller;

use Common\Controller\CMS;

class HitsController extends CMS {

	//内容模型
	protected $db;

	//获取点击数
	public function index() {
		//栏目ID
		$catid = I('get.catid', 0, 'intval');
		//信息ID
		$id = I('get.id', 0, 'intval');
		//模型ID
		$modelid = (int) getCategory($catid, 'modelid');
		if (empty($modelid)) {
			exit;
		}
		$this->db = \Content\Model\ContentModel::getInstance($modelid);
		$r = $this->db->where(array('id' => $id))->field('catid,id,dayviews,monthviews,views,weekviews,yesterdayviews,viewsupdatetime')->find();
		if (empty($r)) {
			exit;
		}
		$r['modelid'] = $modelid;
		//增加点击率
		$this->hits($r);
		echo json_encode($r);
	}

	/**
	 * 增加点击数
	 * @param type $r 点击相关数据
	 * @return boolean
	 */
	private function hits($r) {
		if (empty($r)) {
			return false;
		}
		//当前时间
		$time = time();
		//总点击+1
		$views = $r['views'] + 1;
		//昨日
		$yesterdayviews = (date('Ymd', $r['viewsupdatetime']) == date('Ymd', strtotime('-1 day'))) ? $r['dayviews'] : $r['yesterdayviews'];
		//今日点击
		$dayviews = (date('Ymd', $r['viewsupdatetime']) == date('Ymd', $time)) ? ($r['dayviews'] + 1) : 1;
		//本周点击
		$weekviews = (date('YW', $r['viewsupdatetime']) == date('YW', $time)) ? ($r['weekviews'] + 1) : 1;
		//本月点击
		$monthviews = (date('Ym', $r['viewsupdatetime']) == date('Ym', $time)) ? ($r['monthviews'] + 1) : 1;
		$data = array(
			'views' => $views,
			'yesterdayviews' => $yesterdayviews,
			'dayviews' => $dayviews,
			'weekviews' => $weekviews,
			'monthviews' => $monthviews,
			'viewsupdatetime' => $time,
		);
		$status = $this->db->where(array('id' => $r['id']))->save($data);
		return false !== $status ? true : false;
	}

}
