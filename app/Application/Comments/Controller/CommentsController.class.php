<?php

// +----------------------------------------------------------------------
// | 后台评论管理
// +----------------------------------------------------------------------

namespace Comments\Controller;

use Common\Controller\AdminBase;

class CommentsController extends AdminBase {

	public $setting;
	protected $db;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->setting = cache('Comments_setting');
		$this->db = D('Comments/Comments');
	}

	//显示全部评论
	public function index() {
		if (IS_POST) {
			$ids = I('post.ids');
			if (empty($ids)) {
				$this->error("没有信息被选择！");
			}
			if (false !== $this->db->deleteComments($ids)) {
				$this->success("删除评论成功！");
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$keyword = I('get.keyword', '', 'trim');
			$searchtype = I('get.searchtype', 0, 'intval');
			$type = array(
				0 => "content", //评论内容
				1 => "author", //评论作者
				2 => "comment_id", //所属文章id
			);
			$where = array();
			$where["approved"] = array("EQ", 1);
			if (!empty($keyword) && isset($type[$searchtype])) {
				$where[$type[$searchtype]] = array("LIKE", "%" . $keyword . "%");
			}
			$count = $this->db->where($where)->count();
			$page = $this->page($count, 20);
			$data = $this->db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "DESC"))->select();
			foreach ($data as $k => $v) {
				//取得副表记录
				$r = M($this->db->viceTableName($v['stb']))->where(array("id" => $v['id']))->find();
				$aid = explode("-", $v['comment_id']);
				//栏目id
				$catid = $aid[1];
				//信息id
				$id = $aid[2];
				//取得对应文章信息
				$title = \Content\Model\ContentModel::getInstance(getCategory($catid, 'modelid'))->where(array("id" => $id))->find();
				$title['article_id'] = $title['id'];
				unset($title['id']);
				//替换表情
				if ($r['content']) {
					$this->db->replaceExpression($r['content']);
				}
				$data[$k] = array_merge($title, $data[$k], $r);
			}
			$this->assign("Page", $page->show());
			$this->assign("data", $data);
			$this->display();
		}
	}

	//待审核评论列表
	public function check() {
		if (IS_POST) {
			$ids = I('post.ids');
			if (empty($ids)) {
				$this->error("没有信息被选择！");
			}
			if (false !== $this->db->checkComments($ids)) {
				$this->success("审核成功！");
			} else {
				$this->error("审核失败！");
			}
		} else {
			//查看单条评论
			$id = I('get.id', 0, 'intval');
			if ($id) {

			} else {
				$where = array();
				$where["approved"] = array("EQ", 0);
				$count = $this->db->where($where)->count();
				$page = $this->page($count, 20);
				$data = $this->db->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "DESC"))->select();
				foreach ($data as $k => $v) {
					//取得副表记录
					$r = M($this->db->viceTableName($v['stb']))->where(array("id" => $v['id']))->find();
					$aid = explode("-", $v['comment_id']);
					//栏目id
					$catid = $aid[1];
					//信息id
					$id = $aid[2];
					//取得对应文章信息
					$title = \Content\Model\ContentModel::getInstance(getCategory($catid, 'modelid'))->where(array("id" => $id))->find();
					$title['article_id'] = $title['id'];
					unset($title['id']);
					//替换表情
					if ($r['content']) {
						$this->db->replaceExpression($r['content']);
					}
					$data[$k] = array_merge($title, $data[$k], $r);
				}
				$this->assign("Page", $page->show());
				$this->assign("data", $data);
				$this->display();
			}
		}
	}

	//评论编辑
	public function edit() {
		if (IS_POST) {
			$post = I('post.');
			if (!$post) {
				$this->error('参数有误！');
			}
			if (false !== $this->db->editComments($post)) {
				$this->success('评论更新成功！', U('Comments/index'));
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$id = I('get.id', 0, 'intval');
			if (!$id) {
				$this->error("参数有误！");
			}
			$r = $this->db->where(array("id" => $id))->find();
			if ($r) {
				$r2 = M($this->db->viceTableName($r['stb']))->where(array("id" => $id))->find();
				$data = array_merge($r, $r2);
				$data['content'] = \Input::forTarea($data['content']);
				//取得自定义字段
				$field = $this->db->sideTablesField();
				$this->assign("data", $data);
				$this->assign("field", $field);
				$this->display();
			} else {
				$this->error("该评论不存在！");
			}
		}
	}

	//删除评论
	public function delete() {
		$id = I('get.id', 0, 'intval');
		if (!$id) {
			$this->error("参数有误！");
		}
		if (false !== $this->db->deleteComments($id)) {
			$this->success("评论删除成功！");
		} else {
			$this->error("评论删除失败！");
		}
	}

	//垃圾评论也就是取消审核
	public function spamcomment() {
		$id = I('get.id', 0, 'intval');
		if (!$id) {
			$this->error("参数有误！");
		}
		$r = $this->db->where(array("id" => $id))->find();
		if ($r) {
			$approved = ((int) $r['approved'] == 1) ? 0 : 1;
			if (false !== $this->db->checkComments($id, $approved)) {
				if ($approved) {
					$this->success("评论审核成功！");
				} else {
					$this->success("评论取消审核成功！");
				}
			} else {
				$this->error('操作失败！');
			}
		} else {
			$this->error("该评论不存在！");
		}
	}

	//回复评论
	public function replycomment() {
		if (IS_POST) {
			$post = I('post.', '', '');
			if (!$post) {
				$this->error('提交信息有误！');
			}
			$catid = I('post.comment_catid', 0, 'intval');
			$id = I('post.comment_id', 0, 'intval');
			$post['comment_id'] = "c-{$catid}-{$id}";

			$commentsId = $this->db->addComments($post);
			if (false !== $commentsId) {
				if ($commentsId === -1) {
					$this->error($this->db->getError());
				} else {
					$this->success("评论发表成功！", U('Comments/Comments/index'));
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$id = I('get.id', 0, 'intval');
			if (!$id) {
				$this->error("参数有误！");
			}
			$r = $this->db->where(array("id" => $id))->find();
			if ($r) {
				$r2 = M($this->db->viceTableName($r['stb']))->where(array("id" => $id))->find();
				$data = array_merge($r, $r2);
				$data['content'] = \Input::forTarea($data['content']);
				//取得自定义字段
				$field = M("CommentsField")->where(array("system" => 0))->order(array("fid" => "DESC"))->select();
				$ca = explode("-", $data["comment_id"]);
				//如果是回复评论，也就是approved不等于0的评论
				if (!$r['parent'] == 0) {
					$this->assign("parent", $data['parent']);
				} else {
					$this->assign("parent", $data['id']);
				}
				$this->assign("author_url", self::$Cache['Config']['siteurl']);
				$this->assign("author_email", \Admin\Service\User::getInstance()->email);
				$this->assign("author", \Admin\Service\User::getInstance()->username);
				$this->assign("data", $data);
				$this->assign("catid", $ca[1]);
				$this->assign("commentid", $ca[2]);
				$this->assign("field", $field);
				$this->display();
			} else {
				$this->error("该评论不存在！");
			}
		}
	}

	//评论配置
	public function config() {
		$db = M("CommentsSetting");
		if (IS_POST) {
			$guest = isset($_POST['guest']) && intval($_POST['guest']) ? intval($_POST['guest']) : 0;
			$check = isset($_POST['check']) && intval($_POST['check']) ? intval($_POST['check']) : 0;
			$code = isset($_POST['code']) && intval($_POST['code']) ? intval($_POST['code']) : 0;
			$stb = isset($_POST['stb']) && intval($_POST['stb']) ? intval($_POST['stb']) : 1;
			$order = isset($_POST['order']) && $_POST['order'] ? $_POST['order'] : "id ASC";
			$strlength = isset($_POST['strlength']) && intval($_POST['strlength']) ? intval($_POST['strlength']) : 0;
			$status = isset($_POST['status']) && intval($_POST['status']) ? intval($_POST['status']) : 0;
			$expire = isset($_POST['expire']) && intval($_POST['expire']) ? intval($_POST['expire']) : 0;
			$data = array(
				"guest" => $guest,
				"check" => $check,
				"code" => $code,
				"stb" => $stb,
				"order" => $order,
				"strlength" => $strlength,
				"status" => $status,
				"expire" => $expire,
			);
			$where = $db->find();
			if ($db->where($where)->save($data) !== false) {
				$this->db->comments_cache();
				$this->success("更新成功！", U("Comments/Comments/config"));
			} else {
				$this->error("更新失败！", U("Comments/Comments/config"));
			}
		} else {
			$data = $db->find();
			$this->assign("data", $data);
			$this->display();
		}
	}

	//分表
	public function fenbiao() {
		$db = M("CommentsSetting");
		$r = $db->find();
		$stbsum = $r['stbsum'];
		for ($i = 1; $i <= $stbsum; $i++) {
			$d = M("Comments_data_" . $i);
			$data[] = array(
				"id" => $i,
				"count" => $d->count(),
				"tablename" => C("DB_PREFIX") . "comments_data_" . $i,
			);
		}
		$this->assign("data", $data);
		$this->assign("r", $r);
		$this->display();
	}

	//创建一张新的分表
	public function addfenbiao() {
		if (D("Comments/CommentsField")->addfenbiao()) {
			$this->success("分表创建成功！");
		} else {
			$this->error("创建分表失败！");
		}
	}

}
