<?php

// +----------------------------------------------------------------------
// | 内容解析标签处理类
// +----------------------------------------------------------------------

namespace Content\TagLib;

class Content {

	public $db, $table_name, $modelid, $where;

	/**
	 * 组合查询条件
	 * @param type $attr
	 * @return type
	 */
	public function where($attr) {
		$where = array();
		//设置SQL where 部分
		if (isset($attr['where']) && $attr['where']) {
			$where['_string'] = $attr['where'];
		}
		//栏目id条件
		if (isset($attr['catid']) && (int) $attr['catid']) {
			$catid = (int) $attr['catid'];
			if (getCategory($catid, 'child')) {
				$catids_str = getCategory($catid, 'arrchildid');
				$pos = strpos($catids_str, ',') + 1;
				$catids_str = substr($catids_str, $pos);
				$where['catid'] = array("IN", $catids_str);
			} else {
				$where['catid'] = array("EQ", $catid);
			}
		}
		//缩略图
		if (isset($attr['thumb'])) {
			if ($attr['thumb']) {
				$where['thumb'] = array("NEQ", "");
			} else {
				$where['thumb'] = array("EQ", "");
			}
		}
		//审核状态,自行用where语句实现，不强制只列出审核通过的
		// $where['status'] = array("EQ", 99);
		$this->where = $where;
		return $this->where;
	}

	/**
	 * 初始化模型
	 * @param $catid
	 */
	public function set_modelid($catid = 0, $isModelid = false) {
		if ($catid && !$isModelid) {
			if (getCategory($catid, 'type') && getCategory($catid, 'type') != 0) {
				return false;
			}
			$this->modelid = getCategory($catid, 'modelid');
		} else {
			$this->modelid = $catid;
		}
		return $this->db = \Content\Model\ContentModel::getInstance($this->modelid);
	}

	/**
	 * 统计
	 */
	public function count($data) {
		if ($data['action'] == 'lists') {
			if (!$this->set_modelid($data['catid'])) {
				return false;
			}
			return $this->db->where($this->where($data))->count();
		}
	}

	/**
	 * 内容列表（lists）
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 否	 null	 调用栏目ID
	 * where	 否	 null	 sql语句的where部分
	 * thumb	 否	 0	 是否仅必须缩略图
	 * order	 否	 null	 排序类型
	 * num	 是	 null	 数据调用数量
	 * moreinfo	 否	 0	 是否调用副表数据 1为是
	 *
	 * moreinfo参数属性，本参数表示在返回数据的时候，会把副表中的数据也一起返回。一个内容模型分为2个表，一个主表一个副表，主表中一般是保存了标题、所属栏目等等短小的数据（方便用于索引），而副表则保存了大字段的数据，如内容等数据。在模型管理中新建字段的时候，是允许你选择存入到主表还是副表的（我们推荐的是，把不重要的信息放到副表中）。
	 * @param $data
	 */
	public function lists($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $return = S($cacheID)) {
			return $return;
		}
		if (!$data['catid']) {
			return false;
		}
		$getLastSql = array();
		if (!$this->set_modelid($data['catid'])) {
			return false;
		}
		$this->where($data);
		//判断是否启用分页，如果没启用分页则显示指定条数的内容
		if (!isset($data['limit'])) {
			$data['limit'] = (int) $data['num'] == 0 ? 10 : (int) $data['num'];
		}
		//排序
		if (empty($data['order'])) {
			$data['order'] = array('updatetime' => 'DESC', 'id' => 'DESC');
		}
		$dataList = $this->db->relation($data['moreinfo'] ? true : false)->where($this->where)->limit($data['limit'])->order($data['order'])->select();
		$getLastSql[] = $this->db->getLastSql();
		//是否经过ContentOutput处理
		if ($data['output']) {
			CMS()->ContentOutput->setModelid($this->modelid);
		}
		//把数据组合成以id为下标的数组集合
		if ($dataList) {
			$return = array();
			foreach ($dataList as $r) {
				$return[$r['id']] = $r;
				//调用副表的数据
				if (isset($data['moreinfo']) && intval($data['moreinfo']) == 1) {
					$this->db->dataMerger($return[$r['id']]);
				}
				if ($data['output']) {
					$_original = $return[$r['id']];
					$return[$r['id']] = CMS()->ContentOutput->get($return[$r['id']]);
					$return[$r['id']]['_original'] = $_original;
				}
			}
		} else {
			return false;
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $return, $cache);
		}
		//log
		if (APP_DEBUG) {
			$msg = "Content标签->lists：参数：catid={$data['catid']} ,modelid={$this->modelid} ,order={$data['order']}\n";
			$msg .= "SQL:" . implode("\n", $getLastSql);
			\Think\Log::record($msg, \Think\Log::DEBUG);
		}
		return $return;
	}

	/**
	 * 排行榜标签
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 否	 null	 调用栏目ID，只支持单栏目
	 * where	 否	 null	 sql语句的where部分
	 * modelid 否              null              模型ID
	 * day	 否	 0	 调用多少天内的排行
	 * order	 否	 null	 排序类型（本月排行- monthviews DESC 、本周排行 - weekviews DESC、今日排行 - dayviews DESC）
	 * num	 是	 null	 数据调用数量
	 * @param $data
	 */
	public function hits($data) {
		$catid = intval($data['catid']);
		$modelid = intval($data['modelid']);
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $array = S($cacheID)) {
			return $array;
		}
		$getLastSql = array();
		//初始化模型
		if ($modelid) {
			$this->set_modelid($modelid, true);
		} elseif ($catid) {
			$this->set_modelid($catid);
		} else {
			return false;
		}
		if ($this->db == false) {
			return false;
		}
		$where = $array = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		//排序
		$order = $data['order'];
		if (!$order) {
			$order = array('views' => 'DESC');
		}
		//条数
		$num = (int) $data['num'];
		if ($num < 1) {
			$num = 10;
		}
		if ($catid) {
			$where['catid'] = array('EQ', $catid);
		}
		//如果调用的栏目是存在子栏目的情况下
		if ($catid && getCategory($catid, 'child')) {
			$catids_str = getCategory($catid, 'arrchildid');
			$pos = strpos($catids_str, ',') + 1;
			$catids_str = substr($catids_str, $pos);
			$where['catid'] = array('IN', $catids_str);
		}
		//调用多少天内
		if (isset($data['day'])) {
			$updatetime = time() - (intval($data['day']) * 86400);
			$where['updatetime'] = array('GT', $updatetime);
		}
		$dataList = $this->db->relation($data['moreinfo'] ? true : false)->where($where)->order($order)->limit($num)->select();
		$getLastSql[] = $this->db->getLastSql();
		//是否经过ContentOutput处理
		if ($data['output']) {
			CMS()->ContentOutput->setModelid($this->modelid);
		}
		foreach ($dataList as $r) {
			$array[$r['id']] = $r;
			//调用副表的数据
			if (isset($data['moreinfo']) && intval($data['moreinfo']) == 1) {
				$this->db->dataMerger($array[$r['id']]);
			}
			if ($data['output']) {
				$_original = $array[$r['id']];
				$array[$r['id']] = CMS()->ContentOutput->get($array[$r['id']]);
				$array[$r['id']]['_original'] = $_original;
			}
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $array, $cache);
		}
		if (APP_DEBUG) {
			$msg = "Content标签->hits：参数：catid={$catid} ,modelid={$modelid} ,order={$data['order']}\n";
			$msg .= "SQL:" . implode("\n", $getLastSql);
			\Think\Log::record($msg, \Think\Log::DEBUG);
		}
		return $array;
	}

	/**
	 * 相关文章标签
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 否	 null	 调用栏目ID
	 * where	 否	 null	 sql语句的where部分
	 * nid	 否	 null	 排除id 一般是 $id，排除当前文章
	 * relation	 否	 $relation	 无需更改
	 * keywords	 否	 null	 内容页面取值：$rs[keywords]
	 * num	 是	 null	 数据调用数量
	 * @param $data
	 */
	public function relation($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $key_array = S($cacheID)) {
			return $key_array;
		}
		$getLastSql = array();
		$catid = intval($data['catid']);
		if (!$catid) {
			return false;
		}
		if (!$this->set_modelid($catid)) {
			return false;
		}
		//调用数量
		$data['num'] = (int) $data['num'];
		if (!$data['num']) {
			$data['num'] = 10;
		}
		$where = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		$where['status'] = array("EQ", 99);
		$order = $data['order'];
		$limit = $data['nid'] ? $data['num'] + 1 : $data['num'];
		//数据
		$key_array = array();
		$number = 0;
		//是否经过ContentOutput处理
		if ($data['output']) {
			CMS()->ContentOutput->setModelid($this->modelid);
		}
		//根据手动添加的相关文章
		if ($data['relation']) {
			//跨模型
			if (strpos($data['relation'], ',')) {
				$relations = explode('|', $data['relation']);
				$newRela = array();
				$i = 1;
				foreach ($relations as $rs) {
					if ($i >= $limit) {
						break;
					}
					if (strpos($rs, ',')) {
						$rs = explode(',', $rs);
					} else {
						$rs = array($this->modelid, $rs);
					}
					$newRela[$rs[0]][] = $rs[1];
					$i++;
				}
				$_key_array = array();
				foreach ($newRela as $modelid => $catidList) {
					$where['id'] = array('IN', $catidList);
					$_list = \Content\Model\ContentModel::getInstance($modelid)->relation($data['moreinfo'] ? true : false)->where($where)->order($order)->select();
					if (!empty($_list)) {
						$_key_array = array_merge($_key_array, $_list);
					}
					$getLastSql[] = \Content\Model\ContentModel::getInstance($modelid)->getLastSql();
				}
			} else {
				$relations = explode('|', $data['relation']);
				$relations = array_diff($relations, array(null));
				$where['id'] = array('IN', $relations);
				$_key_array = $this->db->relation($data['moreinfo'] ? true : false)->where($where)->limit($limit)->order($order)->select();
				$getLastSql[] = $this->db->getLastSql();
			}
			foreach ($_key_array as $r) {
				$key = $r['catid'] . '_' . $r['id'];
				$key_array[$key] = $r;
				//调用副表的数据
				if (isset($data['moreinfo']) && intval($data['moreinfo']) == 1) {
					$this->db->dataMerger($key_array[$key]);
				}
				if ($data['output']) {
					$_original = $key_array[$key];
					$key_array[$key] = CMS()->ContentOutput->get($key_array[$key]);
					$key_array[$key]['_original'] = $_original;
				}
			}
			$number = count($key_array);
			//删除id条件
			if (isset($where['id'])) {
				unset($where['id']);
			}
		}
		//根据关键字，进行标题匹配
		if ($data['keywords'] && $limit > $number) {
//根据关键字的相关文章
			$limit = ($limit - $number <= 0) ? 0 : ($limit - $number);
			$keywords_arr = $data['keywords'];
			if ($keywords_arr && !is_array($keywords_arr)) {
				if (strpos($data['keywords'], ',') === false) {
					$keywords_arr = explode(' ', $data['keywords']);
				} else {
					$keywords_arr = explode(',', $data['keywords']);
				}
			}
			$i = 1;
			foreach ($keywords_arr as $_k) {
				$_k = str_replace('%', '', $_k);
				$where['keywords'] = array("LIKE", '%' . $_k . '%');
				$_r = $this->db->relation($data['moreinfo'] ? true : false)->where($where)->limit($limit)->order($order)->select();
				$getLastSql[] = $this->db->getLastSql();
				//数据重组
				$r = array();
				foreach ($_r as $rs) {
					$r[$rs['id']] = $rs;
					//调用副表的数据
					if (isset($data['moreinfo']) && intval($data['moreinfo']) == 1) {
						$this->db->dataMerger($r[$rs['id']]);
					}
					if ($data['output']) {
						$_original = $r[$rs['id']];
						$r[$rs['id']] = CMS()->ContentOutput->get($r[$rs['id']]);
						$r[$rs['id']]['_original'] = $_original;
					}
				}
				$number += count($r);
				foreach ($r as $id => $v) {
					$key = $v['catid'] . '_' . $v['id'];
					if ($i <= $data['num'] && !in_array($id, $key_array)) {
						$key_array[$key] = $v;
					}
					$i++;
				}
				if ($data['num'] < $number) {
					break;
				}

			}
			unset($where['keywords']);
		}
		//去除排除信息
		if ($data['nid']) {
			$key = $data['catid'] . '_' . $data['nid'];
			unset($key_array[$key]);
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $key_array, $cache);
		}
		if (APP_DEBUG) {
			$msg = "Content标签->relation：参数：catid={$catid} ,modelid={$modelid} ,order={$data['order']}\n";
			$msg .= "SQL:" . implode("\n", $getLastSql);
			\Think\Log::record($msg, \Think\Log::DEBUG);
		}
		return $key_array;
	}

	/**
	 * 栏目列表（category）
	 * 参数名	 是否必须	 默认值	 说明
	 * catid	 否	 0	 调用该栏目下的所有栏目 ，默认0，调用一级栏目
	 * order	 否	 null	 排序方式、一般按照listorder ASC排序，即栏目的添加顺序
	 * @param $data
	 */
	public function category($data) {
		//缓存时间
		$cache = (int) $data['cache'];
		$cacheID = to_guid_string($data);
		if ($cache && $array = S($cacheID)) {
			return $array;
		}
		$data['catid'] = intval($data['catid']);
		$where = $array = array();
		//设置SQL where 部分
		if (isset($data['where']) && $data['where']) {
			$where['_string'] = $data['where'];
		}
		$db = M('Category');
		$num = (int) $data['num'];
		if (isset($data['catid'])) {
			$where['ismenu'] = 1;
			$where['parentid'] = $data['catid'];
		}
		//如果条件不为空，进行查库
		if (!empty($where)) {
			if ($num) {
				$categorys = $db->where($where)->limit($num)->order($data['order'])->select();
			} else {
				$categorys = $db->where($where)->order($data['order'])->select();
			}
		}
		//结果进行缓存
		if ($cache) {
			S($cacheID, $categorys, $cache);
		}
		return $categorys;
	}

}
