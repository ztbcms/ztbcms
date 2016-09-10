<?php

// +----------------------------------------------------------------------
// | Tags标签处理类
// +----------------------------------------------------------------------

namespace Content\TagLib;

class Tags {

	public $db, $where;

	public function __construct() {
		$this->db = M('Tags');
	}

	/**
	 * 组合查询条件
	 * @param type $data
	 * @return type
	 */
	public function where($data) {
		$where = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		if (isset($data['tagid'])) {
			if (strpos($data['tagid'], ',') !== false) {
				$tagid = explode(',', $data['tagid']);
				$r = $this->db->where(array('tagid' => array('in', $tagid)))->getField('tagid,tag');
				$where['tag'] = array('IN', $r);
			} else {
				$r = $this->db->where(array('tagid' => (int) $data['tagid']))->find();
				$where['tag'] = $r['tag'];
			}
		} else {
			if (is_array($data['tag'])) {
				$where['tag'] = array('IN', $data['tag']);
			} else {
				$tags = strpos($data['tag'], ',') !== false ? explode(',', $data['tag']) : explode(' ', $data['tag']);
				if (count($tags) == 1) {
					$where['tag'] = array('EQ', $data['tag']);
				} else {
					$where['tag'] = array('IN', $tags);
				}
			}
		}
		$this->where = $where;
		return $where;
	}

	/**
	 * 统计
	 */
	public function count($data) {
		if ($data['action'] == 'lists') {
			$usetimes = $this->db->where($this->where($data))->sum('usetimes');
			return $usetimes;
		}
	}

	/**
	 * 列表（lists）
	 * 参数名	 是否必须	 默认值	 说明
	 * tag	 否	 null	 tag名称
	 * tagid	 否	 null	 tagID
	 * num	 否	 10	 返回数量
	 * order	 否	 null	 排序类型
	 *
	 * @param $data
	 */
	public function lists($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $return = S($cacheID)) {
			return $return;
		}
		//查询条件
		$this->where($data);
		//判断是否启用分页，如果没启用分页则显示指定条数的内容
		if (!isset($data['limit'])) {
			$data['limit'] = (int) $data['num'] == 0 ? 10 : (int) $data['num'];
		}
		//排序
		if (empty($data['order'])) {
			$data['order'] = array("updatetime" => "DESC");
		}
		$db = M('TagsContent');
		$return = $db->where($this->where)->order($data['order'])->limit($data['limit'])->select();
		//读取文章信息
		foreach ($return as $k => $v) {
			$r = \Content\Model\ContentModel::getInstance($v['modelid'])->where(array('id' => $v['contentid']))->find();
			if ($r) {
				$return[$k] = array_merge($v, $r);
			}
		}
		if ($cache) {
			S($cacheID, $return, $cache);
		}
		return $return;
	}

	/**
	 * 排行榜 （top）
	 * 参数名	 是否必须	 默认值	 说明
	 * num	 否	 10	 返回数量
	 * order	 否	 hits DESC	 排序类型
	 */
	public function top($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $return = S($cacheID)) {
			return $return;
		}
		$num = $data['num'] ? $data['num'] : 10;
		$order = array("hits" => "DESC");
		if ($data['order']) {
			$order = $data['order'];
		}
		$where = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		$return = $this->db->where($where)->order($order)->limit($num)->select();
		//增加访问路径
		foreach ($return as $k => $v) {
			$url = CMS()->Url->tags($v);
			$return[$k]['url'] = $url['url'];
		}
		if ($cache) {
			S($cacheID, $return, $cache);
		}
		return $return;
	}

}
