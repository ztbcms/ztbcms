<?php

// +----------------------------------------------------------------------
// | 评论行为Api
// +----------------------------------------------------------------------

namespace Comments\Behavior;

class CommentsApi {

	//信息删除行为标签
	public function content_delete_end($data) {
		if (empty($data)) {
			return false;
		}
		$comment_id = "c-{$data['catid']}-{$data['id']}";
		D('Comments/Comments')->deleteCommentsMark($comment_id);
		return true;
	}

}
