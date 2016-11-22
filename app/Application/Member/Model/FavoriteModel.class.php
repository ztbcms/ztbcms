<?php

// +----------------------------------------------------------------------
// | 会员收藏模型
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class FavoriteModel extends Model {

	//数据表
	protected $tableName = 'member_favorite';
	//自动验证
	protected $_validate = array(
		array('userid', 'require', '用户ID不能为空！', 1, 'regex', 1),
		//array('modelid', 'require', '模型ID不能为空！', 1, 'regex', 1),
		//array('catid', 'require', '栏目ID不能为空！', 1, 'regex', 1),
		//array('id', 'require', '信息ID不能为空！', 1, 'regex', 1),
		array('title', 'require', '标题不能为空！', 1, 'regex', 1),
		array('url', 'require', '地址不能为空！', 1, 'regex', 1),
	);
	//自动完成
	protected $_auto = array(
		array('datetime', 'time', 1, 'function'),
	);
	//是否进行时间间隔判断
	protected $isWriteTimeInterval = false;

	/**
	 * 取得用户是否有收藏权限
	 * @param string $userid
	 * @return boolean
	 */
	public function isFavoriteCompetence($userid) {
		if (empty($userid)) {
			return false;
		}
		//取得用户配置信息
		$userConfig = D('Member/Member')->getUserConfig((int) $userid);
		if (empty($userConfig)) {
			return false;
		}
		return $userConfig['expand']['isfavorite'] ? true : false;
	}

	/**
	 * 检查是否收藏过
	 * @param string $userid 用户ID
	 * @param string $catid 栏目ID
	 * @param string $id 信息ID
	 * @return boolean
	 */
	public function isFavorlte($userid, $catid, $id) {
		if (empty($catid) || empty($id) || empty($userid)) {
			return false;
		}
		$count = $this->where(array('userid' => $userid, 'catid' => $catid, 'id' => $id))->count();
		return $count ? true : false;
	}

	/**
	 * 添加收藏
	 * @param array $data
	 * @return boolean
	 */
	public function favoriteAdd($data) {
		if (empty($data)) {
			$this->error = '数据不能为空！';
			return false;
		}
		//栏目ID
		$catid = $data['catid'];
		//信息ID
		$id = $data['id'];
		//取得模型ID
		$modelid = getCategory($catid, 'modelid');
		//取得信息
		$info = \Content\Model\ContentModel::getInstance($modelid)->where(array('id' => $id, 'catid' => $catid, 'status' => 99))->field('id,catid,title,url,description,thumb')->find();
		if (empty($info)) {
			$this->error = '信息为空！';
		}
		$data['title'] = $info['title'];
		$data['url'] = $info['url'];
		$data['modelid'] = $modelid;
		//创建数据
		$data = $this->create($data, 1);
		if ($data) {
			if (false === $this->isFavoriteCompetence($data['userid'])) {
				//$this->error = '当前用户组没有收藏权限！';
				//return false;
			}
			if ($this->isFavorlte($data['userid'], $catid, $id)) {
				$this->error = '该信息已经收藏过了！';
				return false;
			}
			$fid = $this->add($data);
			if ($fid) {
				return $fid;
			} else {
				$this->error = '入库失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 删除收藏
	 * @param string $userid 用户UID
	 * @param string $fid 收藏ID
	 * @return boolean
	 */
	public function favoriteDel($userid, $fid) {
		if (empty($userid)) {
			$this->error = '用户ID不能为空！';
			return false;
		}
		if (empty($fid)) {
			$this->error = '请指定需要删除的收藏记录！';
			return false;
		}

		return $this->where(array('userid' => $userid, 'fid' => $fid))->delete();
	}

}
