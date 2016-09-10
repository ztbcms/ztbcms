<?php

// +----------------------------------------------------------------------
// | 评论标签
// +----------------------------------------------------------------------

namespace Comments\TagLib;

class Comments {

	/**
	 * 获取评论总数
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 是	 null	 栏目ID
	 * id	 是	 null	 信息ID
	 */
	public function get_comment($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $datacache = S($cacheID)) {
			return $datacache;
		}
		$catid = (int) $data['catid'];
		$id = (int) $data['id'];
		$commentid = "c-$catid-$id";

		$total = commcount($catid, $id);
		$data = array(
			"commentid" => $commentid,
			"total" => $total,
		);
		//结果进行缓存
		if ($cache) {
			S($cacheID, $data, $cache);
		}
		return $data;
	}

	/**
	 * 评论数据列表
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 否	 null	 栏目ID
	 * id	 否	 null	 信息ID
	 * hot	 否	 0	 排序方式｛0：最新｝
	 * date	 否	 Y-m-d H:i:s A	时间格式
	 */
	public function lists($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $cachedata = S($cacheID)) {
			return $cachedata;
		}
		$catid = (int) $data['catid'];
		$id = (int) $data['id'];
		$commentid = "c-$catid-$id";
		$hot = isset($data['hot']) ? $data['hot'] : 0;
		$date = !empty($data['date']) ? $data['date'] : "Y-m-d H:i:s A";
		//显示条数
		$num = empty($data['num']) ? 20 : (int) $data['num'];
		$where = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		$where['approved'] = array("EQ", 1);

		$db = D('Comments/Comments');
		$order = array("date" => "DESC");
		if ($hot == 0) {
			$order = array("date" => "DESC");
		}

		if ($catid > 0 && $id > 0) {
			$where['comment_id'] = array("EQ", $commentid);
		}

		$data = $db->where($where)->order($order)->limit($num)->select();
		//取详细数据
		$listComment = array();
		foreach ($data as $r) {
			$listArr[$r['stb']][] = $r['id'];
		}
		foreach ($listArr as $stb => $ids) {
			if ((int) $stb > 0) {
				$list = M($db->viceTableName($stb))->where(array('id' => array('IN', $ids)))->select();
				foreach ($list as $r) {
					$listComment[$r['id']] = $r;
				}
			}
		}
		//评论主表数据和副表数据合并
		foreach ($data as $k => $r) {
			if ((int) $r['id']) {
				$data[$k] = array_merge($r, $listComment[$r['id']]);
				//增加头像调用
				if ($r['user_id']) {
					$data[$k]['avatar'] = service("Passport")->getUserAvatar((int) $r['user_id']);
				} else {
					$data[$k]['avatar'] = CONFIG_SITEURL_MODEL . 'api.php?m=avatar&a=gravatar&email=' . $r['author_email'];
				}
			}
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $data, $cache);
		}
		return $data;
	}

	/**
	 * 评论排行榜
	 * @param type $data
	 */
	public function bang($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $cachedata = S($cacheID)) {
			return $cachedata;
		}
		//返回信息数
		$num = $data['num'] ? (int) $data['num'] : 10;
		$db = M("Comments");
		$data = $db->field(array('*', 'count(*)' => 'total'))->group('comment_id')->order(array('total' => 'DESC'))->limit($num)->select();
		//数据处理
		$return = array();
		foreach ($data as $r) {
			list($m, $catid, $id) = explode('-', $r['comment_id']);
			if (getCategory($catid, 'type') && getCategory($catid, 'type') != 0) {
				continue;
			}
			$modeid = getCategory($catid, 'modelid');
			$return[$id] = \Content\Model\ContentModel::getInstance($modeid)->where(array('id' => $id))->find();
			$return[$id]['comment_total'] = $r['total'];
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $return, $cache);
		}
		return $return;
	}

}
