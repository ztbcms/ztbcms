<?php

// +----------------------------------------------------------------------
// | 搜索行为Api
// +----------------------------------------------------------------------

namespace Search\Behavior;

class SearchApi {

	private $config = array();

	public function __construct() {
		$this->config = cache('Search_config');
	}

	public function content_add_end($param) {
		$modelid = getCategory($param['catid'], 'modelid');
		if (empty($modelid)) {
			return false;
		}
		if (!in_array($modelid, $this->config['modelid'])) {
			return false;
		}
		return D('Search/Search')->search_api($param['id'], $param, $modelid, 'add');
	}

	public function content_edit_end($param) {
		$modelid = getCategory($param['catid'], 'modelid');
		if (empty($modelid)) {
			return false;
		}
		if (!in_array($modelid, $this->config['modelid'])) {
			return false;
		}
		return D('Search/Search')->search_api($param['id'], $param, $modelid, 'updata');
	}

	public function content_check_end($param) {
		$modelid = getCategory($param['catid'], 'modelid');
		if (empty($modelid)) {
			return false;
		}
		if (!in_array($modelid, $this->config['modelid'])) {
			return false;
		}
		if ($param['status'] == 99) {
			$action = 'updata';
		} else {
			$action = 'delete';
		}
		return D('Search/Search')->search_api($param['id'], $param, $modelid, $action);
	}

	public function content_delete_end($param) {
		$modelid = getCategory($param['catid'], 'modelid');
		if (empty($modelid)) {
			return false;
		}
		if (!in_array($modelid, $this->config['modelid'])) {
			return false;
		}
		return D('Search/Search')->search_api($param['id'], $param, $modelid, 'delete');
	}

}
