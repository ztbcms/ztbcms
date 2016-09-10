<?php

// +----------------------------------------------------------------------
// | Tag模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class TagsModel extends Model {

	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('tag', 'require', 'Tags不能为空！', 1, 'regex', 3),
		array('tag', '', '该Tags已经存在！', 0, 'unique', 3),
	);

	/**
	 * 添加tags
	 * @param type $tagname tags名称 可以是数组
	 * @param type $id 信息id
	 * @param type $catid 栏目Id
	 * @param type $modelid 模型id
	 * @param type $data 附加数据
	 * @return boolean
	 */
	public function addTag($tagname, $id, $catid, $modelid, $data = array()) {
		if (!$tagname || !$id || !$catid || !$modelid) {
			return false;
		}
		$time = time();
		$newdata = array();
		if (is_array($tagname)) {
			foreach ($tagname as $v) {
				if (empty($v) || $v == '') {
					continue;
				}
				if ($this->where(array("tag" => $v))->find()) {
					$this->where(array("tag" => $v))->setInc('usetimes');
				} else {
					$this->add(array(
						"tag" => $v,
						"usetimes" => 1,
						"lastusetime" => $time,
						"lasthittime" => $time,
					));
				}
				$newdata[] = array(
					'tag' => $v,
					"url" => $data['url'],
					"title" => $data['title'],
					"modelid" => $modelid,
					"contentid" => $id,
					"catid" => $catid,
					"updatetime" => $time,
				);
			}
			M('TagsContent')->addAll($newdata);
		} else {
			if (empty($tagname) || $tagname == '') {
				return false;
			}
			if ($this->where(array("tag" => $tagname))->find()) {
				$this->where(array("tag" => $tagname))->setInc('usetimes');
			} else {
				$this->add(array(
					"tag" => $tagname,
					"usetimes" => 1,
					"lastusetime" => $time,
					"lasthittime" => $time,
				));
			}
			M("TagsContent")->add(array(
				'tag' => $tagname,
				"url" => $data['url'],
				"title" => $data['title'],
				"modelid" => $modelid,
				"contentid" => $id,
				"catid" => $catid,
				"updatetime" => $time,
			));
		}
	}

	/**
	 * 根据指定的条件更新tags数据
	 * @param type $tagname
	 * @param type $id
	 * @param type $catid
	 * @param type $modelid
	 * @param type $data
	 * @return boolean
	 */
	public function updata($tagname, $id, $catid, $modelid, $data = array()) {
		if (!$tagname || !$id || !$catid || !$modelid) {
			return false;
		}
		$time = time();
		$tags = M("TagsContent")->where(array(
			"modelid" => $modelid,
			"contentid" => $id,
			"catid" => $catid,
		))->select();
		foreach ($tags as $key => $value) {
			//如果在新的关键字数组找不到，说明已经去除
			if (!in_array($value['tag'], $tagname)) {
				//删除不存在的tag
				$this->deleteTagName($value['tag'], $id, $catid, $modelid);
			} else {
				//更新URL
				M("TagsContent")->where(array("tag" => $value['tag'], "modelid" => $value['modelid'], "contentid" => $value['contentid'], "catid" => $value['catid']))->save(array("url" => $data['url'], 'title' => $data['title']));
				foreach ($tagname as $k => $v) {
					if ($value['tag'] == $v) {
						unset($tagname[$k]);
					}
				}
			}
		}
		//新增的tags
		if (count($tagname) > 0) {
			$this->addTag($tagname, $id, $catid, $modelid, $data);
		}
	}

	/**
	 * 根据信息id删除全部的tags记录
	 * @param type $id
	 * @param type $catid
	 * @param type $modelid
	 * @return boolean
	 */
	public function deleteAll($id, $catid, $modelid) {
		if (!$id || !$catid || !$modelid) {
			return false;
		}
		$db_tags_content = M("TagsContent");
		$where = array('modelid' => $modelid, 'contentid' => $id, "catid" => $catid);
		//取得对应tag数据
		$tagslist = $db_tags_content->where($where)->select();
		if (empty($tagslist)) {
			return true;
		}
		//全部-1
		foreach ($tagslist as $k => $value) {
			$this->where(array("tag" => $value['tag']))->setDec('usetimes');
		}
		//删除tags数据
		$db_tags_content->where($where)->delete();
		return true;
	}

	/**
	 * 删除tag
	 * @param type $tagname
	 * @param type $id
	 * @param type $catid
	 * @param type $modelid
	 * @return boolean
	 */
	public function deleteTagName($tagname, $id, $catid, $modelid) {
		if (!$id || !$catid || !$modelid || !$tagname) {
			return false;
		}
		$db_tags_content = M("TagsContent");
		if (is_array($tagname)) {
			foreach ($tagname as $name) {
				$r = $this->where(array("tag" => $name))->find();
				if ($r) {
					if ($r['usetimes'] > 0) {
						$this->where(array("tag" => $name))->setDec('usetimes');
					}
					//删除tags数据
					$db_tags_content->where(array("tag" => $name, 'contentid' => $id, "catid" => $catid))->delete();
				}
			}
		} else {
			$r = $this->where(array("tag" => $tagname))->find();
			if ($r) {
				if ($r['usetimes'] > 0) {
					$this->where(array("tag" => $tagname))->setDec('usetimes');
				}
				//删除tags数据
				$db_tags_content->where(array("tag" => $r['tag'], 'contentid' => $id, "catid" => $catid))->delete();
			}
		}
		return true;
	}

}
