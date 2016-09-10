<?php

// +----------------------------------------------------------------------
// | 评论卸载脚本
// +----------------------------------------------------------------------

namespace Comments\Uninstall;

use Libs\System\UninstallBase;

class Uninstall extends UninstallBase {

	public function run() {
		$db = M('CommentsSetting');
		$info = $db->find();
		if (!empty($info)) {
			for ($i = 1; $i <= $info['stbsum']; $i++) {
				$db->execute('DROP TABLE IF EXISTS `' . C("DB_PREFIX") . 'comments_data_' . $i . '`;');
			}
		}
		return true;
	}

}
