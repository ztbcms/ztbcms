<?php

// +----------------------------------------------------------------------
// | 分享模型
// +----------------------------------------------------------------------

namespace Member\Model;

use Common\Model\Model;

class ShareModel extends Model {

	//数据表
	protected $tableName = 'member_content';
	//模型缓存
	protected $modelCache = array();

	/**
	 * 添加分享（添加投稿）
	 * @param string $userid 用户名
	 * @param string $catid 栏目ID
	 * @param array $data 提交数据
	 * @return boolean
	 */
	public function shareAdd($userid, $catid, $data) {
		if (empty($userid)) {
			$this->error = '投稿人不能为空！';
			return false;
		}
		//取得用户信息
		$userInfo = service("Passport")->getLocalUser((int) $userid);
		if (empty($userInfo)) {
			$this->error = '该用户不存在！';
			return false;
		}
		if (empty($data)) {
			$this->error = '数据为空！';
			return false;
		}
		//获取当前栏目配置
		$category = getCategory($catid);
		//setting 配置
		$setting = $category['setting'];
		define("GROUP_MODULE", "Content");
		$id = CMS()->Content->add($data);
		if ($id) {
			//前台投稿，根据栏目配置和用户配置
			$Member_group = cache("Member_group");
			$groupid = $userInfo['groupid'];
			//如果会员组设置中设置，投稿不需要审核，直接无视栏目设置
			if ($Member_group[$groupid]['allowpostverify'] || (int) $setting['member_check'] == 0) {
				$status = 1;
				if (service("Passport")->userIntegration($userid, (int) $setting['member_addpoint'])) {
					$integral = 1;
				} else {
					$integral = 0;
				}
			} else {
				$status = 0;
				$integral = 0;
			}
			if ($status) {
				$info = \Content\Model\ContentModel::getInstance($category['modelid'])->where(array('id' => $id))->getField('id,catid,title,url,description,thumb');
			}
			//添加记录
			$this->shareContentLog($catid, $id, $userid, $integral, $status);
			return $id;
		} else {
			$this->error = CMS()->Content->getError();
			return false;
		}
	}

	/**
	 * 修改分享（修改投稿）
	 * @param string $userid 用户名
	 * @param string $catid 栏目ID
	 * @param string $id 信息ID
	 * @param array $data 提交数据
	 * @return boolean
	 */
	public function shareEdit($userid, $catid, $id, $data) {
		if (empty($userid)) {
			$this->error = '投稿人不能为空！';
			return false;
		}
		//取得用户信息
		$userInfo = service("Passport")->getLocalUser((int) $userid);
		if (empty($userInfo)) {
			$this->error = '该用户不存在！';
		}
		if (empty($data)) {
			$this->error = '数据为空！';
			return false;
		}
		//获取当前栏目配置
		$category = getCategory($catid);
		//setting 配置
		$setting = $category['setting'];
		define("GROUP_MODULE", "Contents");
		$data['catid'] = $catid;
		$data['id'] = $id;
		//编辑信息是否需要审核
		if ($setting['member_editcheck']) {
			$data['status'] = 1;
		}
		$id = CMS()->Content->edit($data);
		if ($id) {
			return $id;
		} else {
			$this->error = CMS()->Content->getError();
			return false;
		}
	}

	/**
	 *  删除分享
	 * @param string $userid
	 * @param string $id
	 * @return boolean
	 */
	public function shareDel($userid, $id) {
		if (empty($userid)) {
			$this->error = '用户ID不能为空！';
			return false;
		}
		if (empty($id)) {
			$this->error = '请指定需要删除的信息！';
			return false;
		}
		//信息
		$info = $this->where(array('userid' => $userid, 'id' => $id))->find();
		if (empty($info)) {
			$this->error = '需要删除的信息不存在！';
			return false;
		}
		define("GROUP_MODULE", "Contents");
		if (CMS()->Content->delete($info['content_id'], $info['catid'])) {
			$this->where(array('userid' => $userid, 'id' => $id))->delete();
			return true;
		} else {
			$this->error = CMS()->Content->getError() ?: '信息删除失败！';
			return false;
		}
	}

	/**
	 * 添加投稿记录
	 * @param string $catid 栏目ID
	 * @param string $id 信息ID
	 * @param string $userid 用户ID
	 * @param string $integral 是否已经赠送积分
     * @param int $status 审核状态
     * @return int|boolean
	 */
	protected function shareContentLog($catid, $id, $userid, $integral, $status = 0) {
		//添加投稿记录
		return $this->add(array(
			"catid" => $catid,
			"content_id" => $id,
			"userid" => $userid,
			'integral' => $integral,
			'status' => $status,
			"time" => time(),
		));
	}

}
