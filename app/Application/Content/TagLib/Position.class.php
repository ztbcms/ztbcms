<?php

// +----------------------------------------------------------------------
// | 推荐位解析标签
// +----------------------------------------------------------------------

namespace Content\TagLib;

class Position {

	/**
	 * 推荐位数据获取
     *
     * @param $data
	 * 参数名	 是否必须	 默认值	 说明
	 * posid	 是	 null	 推荐位ID
	 * catid	 否	 null	 调用栏目ID
	 * thumb	 否	 0	     是否仅必须缩略图
	 * order	 否	 null	 排序类型
	 * num	     是	 null	 数据调用数量
     * page      否   1      页码
     * @return mixed
	 */
	public function position($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $return = S($cacheID)) {
			return $return;
		}
		$posid = (int) $data['posid'];
		if ($posid < 1) {
			return false;
		}
		$catid = (int) $data['catid'];
		$thumb = isset($data['thumb']) ? $data['thumb'] : 0;
		$order = empty($data['order']) ? array("listorder" => "DESC", "id" => "DESC") : $data['order'];
		$num = (int) $data['num'];
		$page = (int) $data['page'];

		$db = M('PositionData');
		$Position = cache('Position');
		if ($num == 0) {
			$num = $Position[$posid]['maxnum'];
		}
		if ($page == 0) {
			$page = 1;
		}
		$where = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		$where['posid'] = array("EQ", $posid);
		if ($thumb) {
			$where['thumb'] = array("EQ", 1);
		}
		if ($catid > 0) {
			$cat = getCategory($catid);
			if ($cat) {
				//是否包含子栏目
				if ($cat['child']) {
					$where['catid'] = array("IN", $cat['arrchildid']);
				} else {
					$where['catid'] = array("EQ", $catid);
				}
			}
		}
		$data = $db->where($where)->order($order)->limit($num)->page($page)->select();
		foreach ($data as $k => $v) {
			$data[$k]['data'] = unserialize($v['data']);
			$data[$k]['data']['url'] = \Content\Model\ContentModel::getInstance($v['modelid'])->where(array("id" => $v['id']))->getField("url");
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $data, $cache);
		}
		return $data;
	}

}
