<?php

// +----------------------------------------------------------------------
// | 会员公共模型
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class MemberCommonModel extends Model {

	//写库操作时间间隔
	protected $lastWriteTime = 10;
	//是否进行时间间隔判断
	protected $isWriteTimeInterval = true;

	// 设置最后对数据库写入操作时间戳
	protected function _after_insert($data, $options) {
		if (false === $this->isWriteTimeInterval) {
			return true;
		}
		parent::_after_insert($data, $options);
		cookie('lastWriteTime', time(), $this->lastWriteTime);
	}

	// 限制操作频率
	protected function _before_insert(&$data, $options) {
		parent::_before_insert($data, $options);
		if (false === $this->isWriteTimeInterval) {
			return true;
		}
		$lastWriteTime = cookie('lastWriteTime');
		if (!empty($lastWriteTime)) {
			$this->error = '您操作过快，请稍后操作！';
			return false;
		}
	}

}
