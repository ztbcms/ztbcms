<?php

// +----------------------------------------------------------------------
// | 信息审核后的行为调用
// +----------------------------------------------------------------------

namespace Member\Behavior;

class ContentCheckEndBehavior {

	public function run(&$params) {
		//参数是审核文章的数据
		if (!empty($params) && isset($params['sysadd']) && $params['sysadd'] == 0 && $params['sysadd'] != 99) {
			//标识审核状态
			M('MemberContent')->where(array('catid' => $params['catid'], 'content_id' => $params['id']))->save(array('status' => 1));
		}
	}

}
