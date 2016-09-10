<?php

// +----------------------------------------------------------------------
// | 内容删除完成时行为调用
// +----------------------------------------------------------------------

namespace Member\Behavior;

class ContentDeleteEndBehavior {

	public function run(&$params) {
		//参数是审核文章的数据
		if (!empty($params) && isset($params['sysadd']) && $params['sysadd'] == 0) {
			//删除对应的会员投稿记录信息
			M("MemberContent")->where(array("content_id" => $params['id'], "catid" => $params['catid']))->delete();
		}
	}

}
