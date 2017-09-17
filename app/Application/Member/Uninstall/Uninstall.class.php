<?php

// +----------------------------------------------------------------------
// | 会员模块，卸载脚本
// +----------------------------------------------------------------------

namespace Member\Uninstall;

use Libs\System\UninstallBase;

class Uninstall extends UninstallBase {

	//会员模型类型
	private $modelType = 2;
	//固定会员模型相关表
	private $modelTabList = array(
		'member',
		'connect',
		'member_dongtai',
		'member_album',
		'member_album_love',
		'member_album_comment',
		'member_favorite',
		'member_friends',
		'member_friendsgroup',
		'member_message',
		'member_weibo',
		'member_weibo_comment',
		'member_wall',
		'member_praiselog',
		'member_theme',
		'member_visitors',
		'member_online',
		'member_msg',
		'member_msg_text',
		'member_content',
		'member_detail',
		'member_group',
	);

	//卸载
	public function run() {
		$db = M();
		//删除对应模型数据表
		if (!empty($this->modelTabList)) {
			foreach ($this->modelTabList as $tablename) {
				if (!empty($tablename)) {
					$tablename = C("DB_PREFIX") . $tablename;
					$db->execute("DROP TABLE IF EXISTS `{$tablename}`;");
				}
			}
		}
		//查询出有多少会员模型
		$model = M('Model');
		$modelList = $model->where(array('type' => $this->modelType))->select();
		if (!empty($modelList)) {
			foreach ($modelList as $r) {
				if (!empty($r['tablename'])) {
					//取得会员模型完整表名
					$tablename = C("DB_PREFIX") . $r['tablename'];
					//删除
					$db->execute("DROP TABLE IF EXISTS `{$tablename}`;");
				}
				//删除字段
				if ($r['modelid']) {
					M('ModelField')->where(array('modelid' => $r['modelid']))->delete();
				}
			}
			$model->where(array('type' => $this->modelType))->delete();
		}
		//删除通行证
		CMS()->Dir->delDir(PROJECT_PATH . 'Libs/Driver/Passport/');
		//删除uc api
		CMS()->Dir->delDir(SITE_PATH . 'api/');
		//删除文件UserController.class.php
		@unlink(APP_PATH . 'Api/Controller/UserController.class.php');
		return true;
	}

}
