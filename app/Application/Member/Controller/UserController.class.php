<?php

// +----------------------------------------------------------------------
// | 会员设置管理
// +----------------------------------------------------------------------

namespace Member\Controller;

class UserController extends MemberbaseController {

	//会员设置界面
	public function profile() {
		//====基本资料表单======
		$modelid = $this->userinfo['modelid'];
		//会员模型数据表名
		$tablename = $this->memberModel[$modelid]['tablename'];
		//相应会员模型数据
		$modeldata = M(ucwords($tablename))->where(array("userid" => $this->userid))->find();
		if (!is_array($modeldata)) {
			$modeldata = array();
		}
		$data = array_merge($this->userinfo, $modeldata);
		$content_form = new \content_form($modelid);
		$data['modelid'] = $modelid;
		//字段内容
		$forminfos = $content_form->get($data);

		//====头像======
		$user_avatar = service("Passport")->getUploadPhotosHtml($this->userid);
		$this->assign('user_avatar', $user_avatar);
		$this->assign("forminfos", $forminfos);
		$this->assign('type', I('get.type', 'profile'));
		$this->assign("userinfo", $data);
		$this->display();
	}

	//保存基本信息
	public function doprofile() {
		if (IS_POST) {
			$post = $_POST;
			$info = $post['info'];
			//获取用户信息
			$userinfo = service("Passport")->getLocalUser($this->userid);
			if (empty($userinfo)) {
				$this->error('该会员不存在！');
			}
			//基本信息
			$data = $this->memberDb->create($post, 2);
			if (!$data) {
				$this->error($this->memberDb->getError());
			}
			//详细信息验证
			if (!empty($info)) {
				$ContentModel = \Content\Model\ContentModel::getInstance($this->userinfo['modelid']);
				$content_input = new \content_input($this->userinfo['modelid']);
				$info['userid'] = $this->userid;
				$inputinfo = $content_input->get($info, 3);
				if ($inputinfo) {
					//数据验证
					$inputinfo = $ContentModel->token(false)->create($inputinfo, 2);
					if (false == $inputinfo) {
						$ContentModel->tokenRecovery($post);
						$this->error($ContentModel->getError());
					}
					//检查详细信息是否已经添加过
					if ($ContentModel->where(array("userid" => $this->userid))->find()) {
						$status = $ContentModel->where(array("userid" => $this->userid))->save($inputinfo);
					} else {
						$inputinfo['userid'] = $this->userid;
						$status = $ContentModel->add($inputinfo);
					}
				} else {
					$ContentModel->tokenRecovery($post);
					$this->error($content_input->getError());
				}
			}
			//修改基本资料
			if ($userinfo['username'] != $data['username'] || !empty($data['password']) || $userinfo['email'] != $data['email']) {
				$edit = service("Passport")->userEdit($userinfo['username'], '', '', $data['email'], 1);
				if (empty($edit)) {
					$this->error(service("Passport")->getError() ?: '修改失败！');
				}
			}
			unset($data['username'], $data['password'], $data['email']);
			if (!empty($data)) {
				$this->memberDb->where(array("userid" => $this->userid))->save($data);
			}
			if (false !== $status) {
				$this->success("基本信息修改成功！");
			} else {
				$this->error('基本信息修改失败！');
			}
		} else {
			$this->error('基本信息修改失败！');
		}
	}

	//修改密码
	public function dopassword() {
		if (IS_POST) {
			$post = I('post.');
			//旧密码
			$oldPassword = $post['oldPassword'];
			//根据当前密码取得用户资料
			$userInfo = service("Passport")->getLocalUser($this->userid, $oldPassword);
			if (false == $userInfo) {
				$this->error('旧密码错误，请重新输入！');
			}
			//设置密码
			$password = $post['password'];
			if (empty($password)) {
				$this->error('请输入你的密码！');
			}
			if (false == isMin($password, 6)) {
				$this->error('密码长度不能小于6位！');
			}
			//再次密码确认
			$password2 = $post['password2'];
			if ($password != $password2) {
				$this->error('两次密码输入不一致！');
			}
			$edit = service("Passport")->userEdit($this->username, '', $password, '', 1);
			if ($edit) {
				//注销当前登录
				service("Passport")->logoutLocal();
				$this->success('密码修改成功！');
			} else {
				$this->error(service("Passport")->getError() ?: '密码修改失败！');
			}
		} else {
			$this->error('修改失败！');
		}
	}

}
