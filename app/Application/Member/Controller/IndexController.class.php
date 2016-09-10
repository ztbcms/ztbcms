<?php

// +----------------------------------------------------------------------
// | 会员中心首页
// +----------------------------------------------------------------------

namespace Member\Controller;

class IndexController extends MemberbaseController {

	//会员中心首页
	public function index() {
		$this->redirect('Index/home');
	}

	//个人首页
	public function home() {
		$this->redirect('User/profile');
	}

	//登录页面
	public function login() {
		$forward = $_REQUEST['forward'] ? $_REQUEST['forward'] : cookie("forward");
		cookie("forward", null);
		if (!empty($this->userid)) {
			$this->success("您已经是登录状态！", $forward ? $forward : U("Index/index"));
		} else {
			$this->assign('forward', $forward);
			$this->display('Public:login');
		}
	}

	//注册页面
	public function register() {
		if (empty($this->memberConfig['allowregister'])) {
			$this->error("系统不允许新会员注册！");
		}
		$forward = $_REQUEST['forward'] ? $_REQUEST['forward'] : cookie("forward");
		cookie("forward", null);
		if ($this->userid) {
			$this->success("您已经是登录状态，无需注册！", $forward ? $forward : U("Index/index"));
		} else {
			$count = $this->memberDb->where(array('checked' => 1))->count('userid');
			//取出人气高的8位会员
			$heat = $this->memberDb->where(array('checked' => 1))->order(array('heat' => 'DESC'))->field('userid,username,heat')->limit(8)->select();

			$this->assign('heat', $heat);
			$this->assign('count', $count);
			$this->display('Public:register');
		}
	}

	//头像设置
	public function regavatar() {
		$user_avatar = service("Passport")->getUploadPhotosHtml($this->userid);
		$this->assign('user_avatar', $user_avatar);
		$this->display('Public:regavatar');
	}

	//保存用户头像
	public function uploadavatar() {
		$auth_data = \Libs\Util\Encrypt::authcode(str_replace(' ', '+', $_GET['auth_data']), 'DECODE');
		if ($auth_data != $this->userid) {
			exit(json_encode(array(
				'success' => false,
				'msg' => '身份验证失败！',
			)));
		}
		//头像保存目录
		$dir = C("UPLOADFILEPATH") . service("Passport")->getAvatarPath($this->userid);
		//实例化上传类
		$UploadFile = new \UploadFile(array(
			'allowExts' => array('jpg'),
			'uploadReplace' => true,
		));
		//上传列表 180,90,45,30
		$upList = array('__avatar1', '__avatar2', '__avatar3', '__avatar4');
		//保存文件名
		$upNameList = array('180x180', '90x90', '45x45', '30x30');
		foreach ($upList as $i => $key) {
			if (!isset($_FILES[$key])) {
				continue;
			}
			$_FILES[$key]['name'] .= '.jpg';
			//设置保存文件名
			$UploadFile->saveRule = $this->userid . '_' . $upNameList[$i];
			//上传头像
			$file = $UploadFile->uploadOne($_FILES[$key], $dir);
			if ($file === false) {
				exit(json_encode(array(
					'success' => false,
					'msg' => $UploadFile->getErrorMsg(),
				)));
				break;
			} else {
				service('Attachment')->movingFiles($file['savepath'] . $file['savename'], $file['savepath'] . $file['savename']);
			}
		}
		//上传结束
		exit(json_encode(array(
			'success' => true,
			'avatarUrls' => array(),
		)));
	}

	//退出
	public function logout() {
		service("Passport")->logoutLocal();
		session("connect_openid", NULL);
		session("connect_app", NULL);
		//注销在线状态
		D('Member/Online')->onlineDel();
		//tag 行为点
		tag('action_member_logout');
		$this->success("退出成功！", U("Member/Index/login"));
	}

	//忘记密码界面
	public function lostpassword() {
		$this->display('Public:lostpassword');
	}

	//重置密码
	public function resetpassword() {
		$getKey = I('get.key');
		if ($getKey) {
			$getKey = str_replace(array('+', '%23', '%2F', '%3F', '%26', '%3D', '%2B'), array(' ', '#', '/', '?', '&', '=', '+'), $getKey);
		}
		$key = \Libs\Util\Encrypt::authcode($getKey);
		if (empty($key)) {
			$this->error('验证失败，请从新提交密码找回申请！', U('Index/lostpassword'));
		}
		$userinfo = explode('|', $key);
		$this->assign('userinfo', array(
			'userid' => $userinfo[0],
			'username' => $userinfo[1],
			'email' => $userinfo[2],
		));
		$this->assign('key', $getKey);
		$this->display('Public:resetpassword');
	}

	//验证邮箱
	public function verifyemail() {
		$getKey = I('get.key');
		if ($getKey) {
			$getKey = str_replace(array('+', '%23', '%2F', '%3F', '%26', '%3D', '%2B'), array(' ', '#', '/', '?', '&', '=', '+'), $getKey);
		}
		$key = \Libs\Util\Encrypt::authcode($getKey);
		if (empty($key)) {
			$this->error('验证失败，请从新提交密码找回申请！', U('Index/login'));
		}
		$userinfo = explode('|', $key);
		//取得用户资料
		$userinfo = $this->memberDb->getUserInfo((int) $userinfo[0], 'userid,username,email,groupid,checked,point');
		if (empty($userinfo)) {
			$this->error('该帐号不存在，无法进行邮箱验证！', U('Member/Index/index'));
		}
		if ($userinfo['checked']) {
			$this->success('该帐号已经验证通过！', U('Member/Index/index'));
		}
		$data = array();
		$data['checked'] = 1;
		$data['groupid'] = $this->memberDb->get_usergroup_bypoint($userinfo['point']);
		if (false !== $this->memberDb->where(array('userid' => $userinfo['userid']))->save($data)) {
			$this->success('邮箱验证完成！', U('Member/Index/index'));
		} else {
			$this->error('邮箱验证失败！', U('Member/Index/index'));
		}
	}

}
