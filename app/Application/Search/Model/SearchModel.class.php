<?php

// +----------------------------------------------------------------------
// | 搜索模型
// +----------------------------------------------------------------------

namespace Search\Model;

use Common\Model\Model;

class SearchModel extends Model {

	/**
	 * 生成缓存
	 * @return boolean
	 */
	public function search_cache() {
		$Search = M('Module')->where(array('module' => 'Search'))->find();
		if (!$Search) {
			return false;
		}
		$Search['setting'] = unserialize($Search['setting']);
		//是否启用相关搜索
		$Search['setting']['relationenble'] = isset($Search['setting']['relationenble']) ? $Search['setting']['relationenble'] : 1;
		//是否启用PHP简易分词
		$Search['setting']['segment'] = isset($Search['setting']['segment']) ? $Search['setting']['segment'] : 1;
		//搜索结果每页显示条数
		$Search['setting']['pagesize'] = isset($Search['setting']['pagesize']) ? $Search['setting']['pagesize'] : 10;
		//搜索结果缓存时间
		$Search['setting']['cachetime'] = isset($Search['setting']['cachetime']) ? $Search['setting']['cachetime'] : 0;
		//是否使用DZ在线分词接口
		$Search['setting']['dzsegment'] = isset($Search['setting']['dzsegment']) && $Search['setting']['dzsegment'] ? true : false;
		//是否启用sphinx全文索引
		$Search['setting']['sphinxenable'] = isset($Search['setting']['sphinxenable']) ? $Search['setting']['sphinxenable'] : 0;
		//sphinx服务器主机地址
		$Search['setting']['sphinxhost'] = isset($Search['setting']['sphinxhost']) ? $Search['setting']['sphinxhost'] : '';
		//sphinx服务器端口号
		$Search['setting']['sphinxport'] = isset($Search['setting']['sphinxport']) ? $Search['setting']['sphinxport'] : '';
		cache('Search_config', $Search['setting']);
		return $Search['setting'];
	}

	/**
	 * 更新搜索配置
	 * @param type $config 配置数据
	 * @return boolean 成功返回true
	 */
	public function search_config($config) {
		if (!$config || !is_array($config)) {
			return false;
		}
		$status = M('Module')->where(array('module' => 'Search'))->save(array('setting' => serialize($config)));
		if ($status !== false) {
			$this->search_cache();
			return true;
		}
		return false;
	}

	//清空表
	public function emptyTable() {
		//删除旧的搜索数据
		$DB_PREFIX = C('DB_PREFIX');
		$this->execute("DELETE FROM `{$DB_PREFIX}search`");
		$this->execute("ALTER TABLE `{$DB_PREFIX}search` AUTO_INCREMENT=1");
	}

	/**
	 * DZ在线中文分词
	 * @param $title string 进行分词的标题
	 * @param $content string 进行分词的内容
	 * @param $encode string API返回的数据编码
	 * @return  array 得到的关键词数组
	 */
	public function discuzSegment($title = '', $content = '', $encode = 'utf-8') {
		if (empty($title)) {
			return false;
		}
		//标题处理
		$title = rawurlencode(strip_tags(trim($title)));
		//内容处理
		$content = str_replace(' ', '', strip_tags($content));
		//在线分词服务有长度限制
		if (strlen($content) > 2400) {
			$content = mb_substr($content, 0, 2300, $encode);
		}
		//进行URL编码
		$content = rawurlencode($content);
		//API地址
		$url = 'http://keyword.discuz.com/related_kw.html?title=' . $title . '&content=' . $content . '&ics=' . $encode . '&ocs=' . $encode;
		//将XML中的数据,读取到数组对象中
		$xml_array = simplexml_load_file($url);
		$result = $xml_array->keyword->result;
		//分词数据
		$data = array();
		foreach ($result->item as $key => $value) {
			array_push($data, (string) $value->kw);
		}
		if (count($data) > 0) {
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * 使用内置本地分词处理进行分词
	 * @param type $data
	 * @return boolean
	 */
	public function segment($data) {
		if (empty($data)) {
			return false;
		}
		import('Segment', APP_PATH . 'Search/Class/');
		$Segment = new \Segment();
		$fulltext_data = $Segment->get_keyword($Segment->split_result($data));
		$data = explode(' ', $fulltext_data);
		if (count($data) > 0) {
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * 搜索数据入库处理
	 * @param type $data 搜索数据
	 * @param type $text 附带数据，例如标题 关键字
	 * @return string
	 */
	private function dataHandle($data, $text = '') {
		if (!$data) {
			return $data;
		}
		$data = addslashes($data);
		$data = strip_tags($data);
		$data = str_replace(array(" ", "\r\t"), array(""), $data);
		$data = \Input::forSearch($data);
		$data = \Input::deleteHtmlTags($data);
		//搜索配置
		$config = cache('Search_config');
		//判断是否启用sphinx全文索引，如果不是，则进行php简易分词处理
		if ((int) $config['sphinxenable'] == 0 && $config['segment']) {
			//是否使用DZ在线分词
			if ($config['dzsegment']) {
				$fulltext_data = $this->discuzSegment($text ? $text : $data, $data);
			} else {
				$fulltext_data = $this->segment($data);
			}
			$data = $text . " " . implode(' ', $fulltext_data);
		}
		return $data;
	}

	/**
	 * 添加搜索数据
	 * @param type $id 信息id
	 * @param type $catid 栏目id
	 * @param type $modelid 模型id
	 * @param type $inputtime 发布时间
	 * @param type $data 数据
	 * @return boolean
	 */
	public function searchAdd($id, $catid, $modelid, $inputtime, $data, $text = '') {
		if (!$id || !$catid || !$modelid || !$data) {
			return false;
		}
		//发布时间
		$inputtime = $inputtime ? (int) $inputtime : time();
		$data = $this->dataHandle($data, $text);
		$searchid = $this->add(array(
			"id" => $id,
			"catid" => $catid,
			"modelid" => $modelid,
			"adddate" => $inputtime,
			"data" => $data,
		));
		if ($searchid !== false) {
			return $searchid;
		}
		return false;
	}

	/**
	 * 更新搜索数据
	 * @param type $id 信息id
	 * @param type $catid 栏目id
	 * @param type $modelid 模型id
	 * @param type $inputtime 发布时间
	 * @param type $data 数据
	 * @return boolean
	 */
	public function searchSave($id, $catid, $modelid, $inputtime, $data, $text = '') {
		if (!$id || !$catid || !$modelid || !$data) {
			return false;
		}
		$info = $this->where(array("id" => $id, "catid" => $catid, "modelid" => $modelid))->find();
		if (empty($info)) {
			return false;
		}
		//发布时间
		$inputtime = $inputtime ? (int) $inputtime : time();
		$data = $this->dataHandle($data, $text);
		$searchid = $this->where(array(
			"id" => $id,
			"catid" => $catid,
			"modelid" => $modelid,
		))->save(array(
			'adddate' => $inputtime,
			'data' => $data,
		));
		if ($searchid !== false) {
			return true;
		}
		return false;
	}

	/**
	 * 删除搜索数据
	 * @param type $id 信息id
	 * @param type $catid 栏目id
	 * @param type $modelid 模型id
	 * @return boolean
	 */
	public function searchDelete($id, $catid, $modelid) {
		if (!$id || !$catid || !$modelid) {
			return false;
		}
		$status = $this->where(array(
			"id" => $id,
			"catid" => $catid,
			"modelid" => $modelid,
		))->delete();
		return $status !== false ? true : false;
	}

	/**
	 * 更新搜索数据 api 接口
	 * @param type $id 信息id
	 * @param type $data 数据 数据分为 system，和model
	 * @param type $modelid 模型id
	 * @param type $action 动作
	 */
	public function search_api($id = 0, $data = array(), $modelid, $action = 'add') {
		$fulltextcontent = "";
		\Content\Model\ContentModel::getInstance($modelid)->dataMerger($data);
		//更新动作
		if ($action == 'add' || $action == 'updata') {
			//取得模型字段
			$modelField = cache('ModelField');
			$fulltext_array = $modelField[$modelid];
			if (!$fulltext_array) {
				$fulltext_array = array();
			}
			foreach ($fulltext_array AS $key => $value) {
				//作为全站搜索信息
				if ((int) $value['isfulltext']) {
					$fulltextcontent .= $data[$key];
				}
			}
			$fulltextcontent .= $data['title'] . $data['keywords'];
			//添加到搜索数据表
			$inputtime = (int) $data['inputtime'];
			$catid = (int) $data['catid'];
			if ($action == 'add') {
				$this->searchAdd($id, $catid, $modelid, $inputtime, $fulltextcontent, $data['title'] . $data['keywords']);
			} elseif ($action == 'updata') {
				//判断是否有数据，如果没有，变成add
				if ($this->searchSave($id, $catid, $modelid, $inputtime, $fulltextcontent, $data['title'] . $data['keywords']) !== true) {
					$this->searchAdd($id, $catid, $modelid, $inputtime, $fulltextcontent, $data['title'] . $data['keywords']);
				}
			}
		} elseif ($action == 'delete') {
//删除动作
			$catid = $data['catid'];
			$this->searchDelete($id, $catid, $modelid);
		}
	}

}
