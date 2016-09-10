<?php

// +----------------------------------------------------------------------
// | 会员我的分享
// +----------------------------------------------------------------------

namespace Member\Controller;

class ShareController extends MemberbaseController {

	//会员投稿记录数据对象
	protected $memberContent = NULL;
	//内容数据对象
	protected $content = NULL;
	//模型缓存
	protected $modelCache = array();
	//分享模型
	protected $share = NULL;

	protected function _initialize() {
		parent::_initialize();
		$this->memberContent = D('Member/MemberContent');
		$this->modelCache = cache('ModelField');
		$this->share = D('Member/Share');
	}

	//分享首页
	public function index() {
		$type = I('get.type', '');
		$where = array('userid' => $this->userid);
		if ('check' == $type) {
			$where['status'] = 1;
		}
		if ('checking' == $type) {
			$where['status'] = 0;
		}
		$count = $this->share->where($where)->count();
		$page = $this->page($count, 10);
		$shareData = $this->share->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "DESC"))->select();
		$share = array();
		foreach ($shareData as $r) {
			$modelid = getCategory($r['catid'], 'modelid');
			$tablename = ucwords(getModel($modelid, 'tablename'));
			$info = M($tablename)->where(array("id" => $r['content_id'], "sysadd" => 0))->find();
			$info["_setting"] = getCategory($r['catid'], 'setting');
			$info['_shareid'] = $r['id'];
			$share[$info['id']] = $info;
		}

		$this->assign("Page", $page->show('Admin'));
		$this->assign('share', $share);
		$this->assign('type', $type);
		$this->display();
	}

	//发布
	public function add() {
		//投稿权限模型
		$CategoryPriv = M("CategoryPriv");
		if (IS_POST) {
			//栏目ID
			$catid = $_POST['info']['catid'] = intval($_POST['info']['catid']);
			if (empty($catid)) {
				$this->error("请指定栏目ID！");
			}
			$catidPrv = $CategoryPriv->where(array("catid" => $catid, "roleid" => $this->userinfo['groupid'], "is_admin" => 0, "action" => "add"))->find();
			if (empty($catidPrv)) {
				$this->error("您没有该栏目投稿权限！");
			}
			if (trim($_POST['info']['title']) == '') {
				$this->error("标题不能为空！");
			}
			//获取当前栏目配置
			$category = getCategory($catid);
			//栏目类型为0
			if ($category['type'] == 0) {
				//模型ID
				$modelid = $category['modelid'];
				//检查模型是否被禁用
				if ($this->modelCache[$modelid]['disabled'] == 1) {
					$this->error("模型被禁用！");
				}
				//提交数据
				$id = $this->share->shareAdd($this->userid, $catid, $_POST['info']);
				if ($id) {
					$this->success('分享资讯成功！', U('Share/index'));
				} else {
					$this->error($this->share->getError());
				}
			} else {
				$this->error("该栏目类型无法发布！");
			}
		} else {
			$step = I('get.step', 1, 'intval');
			if ($step == 1) {
				$this->display('declaration');
				exit;
			}
			$catid = I('get.catid', 0, 'intval');
			$tree = new \Tree();
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$str = "<option value='\$catidurl' \$selected \$disabled>\$spacer \$catname</option>";
			//查询出全部权限
			$Prv = $CategoryPriv->where(array("is_admin" => 0, "action" => "add"))->select();
			$array = cache("Category");
			foreach ($array as $k => $v) {
				$array[$k] = $v = getCategory($v['catid']);
				if ($v['catid'] == $catid) {
					$array[$k]['selected'] = "selected";
				}
				if (in_array(array("catid" => $v['catid'], "roleid" => $this->userinfo['groupid'], "is_admin" => 0, "action" => "add"), $Prv)) {
					//只有终极栏目可以发表
					if ($v['child'] == 1) {
						$array[$k]['disabled'] = "disabled";
						$array[$k]['catidurl'] = U('Share/add', array('step' => 2));
					} else {
						$array[$k]['disabled'] = "";
						$array[$k]['catidurl'] = U('Share/add', array('step' => 2, 'catid' => $v['catid']));
					}
				} else {
					$array[$k]['disabled'] = "disabled";
				}
			}
			$tree->init($array);
			$categoryselect = $tree->get_tree(0, $str, 0);
			//如果有选择栏目的情况下
			if ($catid) {
				//取得栏目数据
				$category = getCategory($catid);
				if (empty($category)) {
					$this->error('该栏目不存在！', U('Share/add', array('step' => 2)));
				}
				//判断是否终极栏目
				if ($category['child']) {
					$this->error("该栏目不允许投稿！", U('Share/add', array('step' => 2)));
				}
				//检查用户组投稿权限
				$catidPrv = $CategoryPriv->where(array("catid" => $catid, "roleid" => $this->userinfo['groupid'], "is_admin" => 0, "action" => "add"))->find();
				if (empty($catidPrv)) {
					$this->error("您没有该栏目投稿权限！", U('Share/add', array('step' => 2)));
				}
				//模型ID
				$modelid = (int) $category['modelid'];
				//检查模型是否被禁用
				if ((int) $this->modelCache[$modelid]['disabled'] == 1) {
					$this->error("该栏目暂时无法投稿！", U('Share/add', array('step' => 2)));
				}
				//实例化表单类 传入 模型ID 栏目ID 栏目数组
				$content_form = new \content_form($modelid, $catid);
				//生成对应字段的输入表单
				$forminfos = $content_form->get();
				//合并，不分基本字段
				if (empty($forminfos['senior'])) {
					$forminfos['senior'] = array();
				}
				$forminfos = array_merge($forminfos['base'], $forminfos['senior']);
				//生成对应的JS验证规则
				$formValidateRules = $content_form->formValidateRules;
				//js验证不通过提示语
				$formValidateMessages = $content_form->formValidateMessages;
				//js
				$formJavascript = $content_form->formJavascript;

				$this->assign("forminfos", $forminfos);
				$this->assign("formValidateRules", $formValidateRules);
				$this->assign("formValidateMessages", $formValidateMessages);
				$this->assign("formJavascript", $formJavascript);
			}

			$this->assign('catid', $catid);
			$this->assign("categoryselect", $categoryselect);
			$this->display();
		}
	}

	//发布
	public function edit() {
		//投稿权限模型
		$CategoryPriv = M("CategoryPriv");
		if (IS_POST) {
			//栏目ID
			$catid = intval($_POST['info']['catid']);
			unset($_POST['info']['catid'], $_POST['info']['id']);
			if (empty($catid)) {
				$this->error("请指定栏目ID！");
			}
			$content_id = I('post.content_id', 0, 'intval');
			$info = $this->share->where(array('userid' => $this->userid, 'content_id' => $content_id, 'catid' => $catid))->find();
			if (empty($info)) {
				$this->error('该分享信息不存在！');
			}
			$catidPrv = $CategoryPriv->where(array("catid" => $catid, "roleid" => $this->userinfo['groupid'], "is_admin" => 0, "action" => "add"))->find();
			if (empty($catidPrv)) {
				$this->error("您没有该栏目投稿权限！");
			}
			if (trim($_POST['info']['title']) == '') {
				$this->error("标题不能为空！");
			}
			//获取当前栏目配置
			$category = getCategory($catid);
			//栏目配置
			$setting = $category['setting'];
			if ($setting['member_admin']) {
				if ($info['status']) {
					if (!in_array((int) $setting['member_admin'], array(4, 5))) {
						$this->error('对不起，你没有权限进行该操作！');
					}
				} else {
					if (!in_array((int) $setting['member_admin'], array(1, 2, 4, 5))) {
						$this->error('对不起，你没有权限进行该操作！');
					}
				}
			} else {
				$this->error('对不起，你没有权限进行该操作！');
			}
			//栏目类型为0
			if ($category['type'] == 0) {
				//模型ID
				$modelid = $category['modelid'];
				//检查模型是否被禁用
				if ($this->modelCache[$modelid]['disabled'] == 1) {
					$this->error("模型被禁用！");
				}
				//提交数据
				$id = $this->share->shareEdit($this->userid, $catid, $content_id, $_POST['info']);
				if ($id) {
					$this->success('修改成功！', U('Share/index'));
				} else {
					$this->error($this->share->getError());
				}
			} else {
				$this->error("该栏目类型无法发布！");
			}
		} else {
			$id = I('get.id', 0, 'intval');
			$info = $this->share->where(array('userid' => $this->userid, 'id' => $id))->find();
			if (empty($info)) {
				$this->error('该分享信息不存在！');
			}
			$catid = $info['catid'];
			//检查用户组权限
			$catidPrv = $CategoryPriv->where(array("catid" => $catid, "roleid" => $this->userinfo['groupid'], "is_admin" => 0, "action" => "add"))->find();
			if (empty($catidPrv)) {
				$this->error("您没有该栏目投稿权限！");
			}
			//取得栏目数据
			$category = getCategory($catid);
			if (empty($category)) {
				$this->error('该栏目不存在！', U('Share/add', array('step' => 2)));
			}
			//栏目配置
			$setting = $category['setting'];
			if ($setting['member_admin']) {
				if ($info['status']) {
					if (!in_array((int) $setting['member_admin'], array(4, 5))) {
						$this->error('对不起，你没有权限进行该操作！');
					}
				} else {
					if (!in_array((int) $setting['member_admin'], array(1, 2, 4, 5))) {
						$this->error('对不起，你没有权限进行该操作！');
					}
				}
			} else {
				$this->error('对不起，你没有权限进行该操作！');
			}
			//模型ID
			$modelid = (int) $category['modelid'];
			//判断是否终极栏目
			if ($category['child']) {
				$this->error("该栏目不允许投稿！", U('Share/add', array('step' => 2)));
			}
			//取得文章数据
			$ContentModel = \Content\Model\ContentModel::getInstance($modelid);
			$data = $ContentModel->relation(true)->where(array("id" => $info['content_id'], 'username' => $this->username))->find();
			if (empty($data)) {
				$this->error('暂时无法进行编辑！');
			}
			$ContentModel->dataMerger($data);
			//实例化表单类 传入 模型ID 栏目ID 栏目数组
			$content_form = new \content_form($modelid, $catid);
			//生成对应字段的输入表单
			$forminfos = $content_form->get($data);
			//合并，不分基本字段
			if (empty($forminfos['senior'])) {
				$forminfos['senior'] = array();
			}
			$forminfos = array_merge($forminfos['base'], $forminfos['senior']);
			//生成对应的JS验证规则
			$formValidateRules = $content_form->formValidateRules;
			//js验证不通过提示语
			$formValidateMessages = $content_form->formValidateMessages;
			//js
			$formJavascript = $content_form->formJavascript;

			$this->assign("forminfos", $forminfos);
			$this->assign("formValidateRules", $formValidateRules);
			$this->assign("formValidateMessages", $formValidateMessages);
			$this->assign("formJavascript", $formJavascript);
			$this->assign('content_id', $info['content_id']);
			$this->assign('catid', $catid);
			$this->display();
		}
	}

	//删除
	public function del() {
		$id = I('post.id', 0, 'intval');
		if (empty($id)) {
			$this->error('请指定需要删除的信息！');
		}
		//信息
		$info = $this->share->where(array('id' => $id))->find();
		if ($info) {
			if ($info['userid'] != $this->userid) {
				$this->message(array(
					'info' => '对不起，你无权删除！',
					'error' => 20002,
				));
			}
			//取得栏目信息
			$category = getCategory($info['catid']);
			$setting = $category['setting'];
			if ($setting['member_admin']) {
				if ($info['status']) {
					if (in_array((int) $setting['member_admin'], array(4, 6))) {
						if ($this->share->shareDel($this->userid, $id)) {
							$this->message(10000, array(), true);
						} else {
							$this->error($this->share->getError());
						}
					} else {
						$this->message(array(
							'info' => '对不起，你无权删除！',
							'error' => 20002,
						));
					}
				} else {
					if (in_array((int) $setting['member_admin'], array(1, 3, 6))) {
						if ($this->share->shareDel($this->userid, $id)) {
							$this->message(10000, array(), true);
						} else {
							$this->error($this->share->getError());
						}
					} else {
						$this->message(array(
							'info' => '对不起，你无权删除！',
							'error' => 20002,
						));
					}
				}
			} else {
				$this->message(array(
					'info' => '对不起，你无权删除！',
					'error' => 20002,
				));
			}
		} else {
			$this->error('该分享记录不存在！');
		}
	}

}
