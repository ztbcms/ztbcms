<?php

// +----------------------------------------------------------------------
// | 评论
// +----------------------------------------------------------------------

namespace Comments\Controller;

use Common\Controller\Base;

class IndexController extends Base {

	public $setting;
	protected $db;

	/**
	 * 生成树型结构所需要的2维数组
	 * @var array
	 */
	protected $arr = array();

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->db = D('Comments/Comments');
		$this->setting = cache('Comments_setting');
	}

	//显示信息评论,json格式
	public function json() {
		//信息ID
		$id = I('get.id', 0, 'intval');
		//栏目ID
		$catid = I('get.catid', 0, 'intval');
		//评论标识id
		$comment_id = "c-$catid-$id";
		if (!$id || !$catid) {
			$this->error('参数错误！');
		}
		//每页显示评论信息量
		$pageSize = I('get.size', 20, 'intval');
		//当前分页号
		$page = I('get.page', 1, 'intval');
		//条件
		$where = array(
			'comment_id' => $comment_id,
			'approved' => 1,
			'parent' => 0, //非回复类评论
		);

		$commentCount = $this->db->where($where)->count();
		$pages = page($commentCount, $pageSize, $page);
		//评论主表数据
		$commentData = $this->db->where($where)->order($this->setting['order'])->limit($pages->firstRow . ',' . $pages->listRows)->select();
		foreach ($commentData as $r) {
			$this->getParentComment($r['id']);
			$this->arr[] = $r;
		}
		//取详细数据
		$listComment = array();
		foreach ($this->arr as $r) {
			$listArr[$r['stb']][] = $r['id'];
		}
		foreach ($listArr as $stb => $ids) {
			if ((int) $stb > 0) {
				$list = M($this->db->viceTableName($stb))->where(array('id' => array('IN', $ids)))->select();
				foreach ($list as $r) {
					//替换表情
					if ($r['content']) {
						$this->db->replaceExpression($r['content']);
					}
					$listComment[$r['id']] = $r;
				}
			}
		}
		//评论主表数据和副表数据合并
		foreach ($this->arr as $k => $r) {
			if ((int) $r['id']) {
				$this->arr[$k] = array_merge($r, $listComment[$r['id']]);
			}
		}
		//取得树状结构数组
		$treeArray = $this->get_tree_array();
		//最终返回数组
		$return = array(
			//配置
			'config' => array(
				'guest' => $this->setting['guest'],
				'code' => $this->setting['code'],
				'strlength' => $this->setting['strlength'],
				'expire' => $this->setting['expire'],
				'noallow' => (int) $this->setting['status'] ? $this->db->noAllowComments($catid, $id) : false,
			),
			//当前登录会员信息
			'users' => array(
				'user_id' => service("Passport")->userid ?: 0,
				'name' => service("Passport")->username ?: '',
				'email' => service("Passport")->email ?: '',
				'avatar' => service("Passport")->userid ? service("Passport")->getUserAvatar(service("Passport")->userid) : '',
			),
			//评论列表 去除键名，不然json输出会影响排序
			'response' => array_values($treeArray),
			//分页相关
			'cursor' => array(
				'pagetotal' => $pages->Total_Pages, //总页数
				'total' => $commentCount, //总信息数
				'size' => $pageSize, //每页显示多少
				C("VAR_PAGE") => $page, //当前分页号
			),
		);
		// jsonp callback
		$callback = I('get.callback');
		$this->ajaxReturn(array(
			'data' => $return,
			'info' => '',
			'status' => true,
		), (isset($_GET['callback']) && $callback ? 'JSONP' : 'JSON'));
	}

	//取得某条评论下的全部回复评论
	public function json_reply() {
		//信息ID
		$parent = I('get.parent', 0, 'intval');
		if (!$parent) {
			$this->error('参数错误！');
		}
		//条件
		$where = array(
			'parent' => $parent,
			'approved' => 1,
		);
		$commentData = $this->db->where($where)->order(array('date' => 'ASC'))->select();
		//取详细数据
		$listComment = array();
		foreach ($commentData as $r) {
			$listArr[$r['stb']][] = $r['id'];
		}
		foreach ($listArr as $stb => $ids) {
			if ((int) $stb > 0) {
				$list = M($this->db->viceTableName($stb))->where(array('id' => array('IN', $ids)))->select();
				foreach ($list as $r) {
					//替换表情
					if ($r['content']) {
						$this->db->replaceExpression($r['content']);
					}
					$listComment[$r['id']] = $r;
				}
			}
		}
		//评论主表数据和副表数据合并
		foreach ($commentData as $k => $r) {
			if ((int) $r['id']) {
				$commentData[$k] = array_merge($r, $listComment[$r['id']]);
			}
		}
		//最终返回数组
		$return = array(
			//评论列表 去除键名，不然json输出会印象排序
			'response' => array_values($commentData),
		);
		// jsonp callback
		$callback = I('get.callback');
		$this->ajaxReturn(array(
			'data' => $return,
			'info' => '',
			'status' => true,
		), (isset($_GET['callback']) && $callback ? 'JSONP' : 'JSON'));
	}

	//获取评论表情
	public function json_emote() {
		$cacheReplaceExpression = S('cacheReplaceExpression');
		if (empty($cacheReplaceExpression)) {
			$cacheReplaceExpression = D('Comments/Emotion')->cacheReplaceExpression();
		}
		// jsonp callback
		$callback = I('get.callback');
		$this->ajaxReturn(array(
			'data' => $cacheReplaceExpression,
			'info' => '',
			'status' => true,
		), (isset($_GET['callback']) && $callback ? 'JSONP' : 'JSON'));
	}

	//显示某篇信息的评论页面
	public function comment() {
		//所属文章id
		$comment_id = I('get.commentid', '', '');
		//评论
		$id = I('get.id', 0, 'intval');
		if (!$comment_id && !$id) {
			$this->error('缺少参数！');
		}
	}

	//添加评论
	public function add() {
		if (IS_POST) {
			//栏目id
			$catid = I('post.comment_catid', 0, 'intval');
			//信息id
			$id = I('post.comment_id', 0, 'intval');
			//回复评论id
			$parent = I('post.parent', 0);
			if (!$catid || !$id) {
				$this->error("请指定需要评论的信息id！");
			}
			//评论功能是否开启
			if ((int) $this->setting['status'] !== 1) {
				$this->error("评论功能已经关闭！");
			}
			if (false === $this->db->noAllowComments($catid, $id)) {
				$this->error("该信息不允许评论！");
			}
			//转换html为实体
			$post = array_map('htmlspecialchars', $_POST);
			$post['comment_id'] = "c-{$catid}-{$id}";
			//如果是登录状态，强制使用会员帐号和会员邮箱
			if (service("Passport")->userid) {
				$post['user_id'] = service("Passport")->userid;
				$post['author'] = service("Passport")->username;
				$post['author_email'] = service("Passport")->email;
			}

			//检查评论间隔时间
			$co = cookie($post['comment_id']);
			if ($co && (int) $this->setting['expire']) {
				$this->error("评论发布间隔为" . $this->setting['expire'] . "秒！");
			}

			//判断游客是否有发表权限
			if ((int) $this->setting['guest'] < 1) {
				if (!isset(service("Passport")->userid) && empty(service("Passport")->userid)) {
					$this->error("游客不允许参与评论！");
				}
			}

			//验证码判断开始
			if ($this->setting['code'] == 1) {
				$verify = I('post.verify');
				if (empty($verify) || !$this->verify($verify, 'comment')) {
					if (IS_AJAX) {
						$this->ajaxReturn(array(
							'info' => '验证码错误，请重新输入！',
							'focus' => 'verify',
							'status' => 0,
						));
					} else {
						$this->error("验证码错误，请重新输入！");
					}
				}
			}

			//评论内容长度验证
			$content = I('post.content');
			if (false === $this->db->check($content, '0,' . (int) $this->setting['strlength'], 'length')) {
				$info = "评论内容超出系统设置允许的最大长度" . $this->setting['strlength'] . "字节！";
				if (IS_AJAX) {
					$this->ajaxReturn(array(
						'info' => $info,
						'status' => 0,
						'focus' => 'content',
					));
				} else {
					$this->error($info);
				}
			}

			//检查回复的评论是否存在
			if ($parent) {
				$parentInfo = $this->db->where(array('id' => $parent))->find();
				if (!$parentInfo) {
					$this->error('回复的评论不存在！');
				} else {
					$post['content'] = "@{$parentInfo['author']}，" . $post['content'];
				}
			}

			$commentsId = $this->db->addComments($post);
			if (false !== $commentsId) {
				//设置评论间隔时间
				if ($this->setting['expire']) {
					cookie($post['comment_id'], '1', array('expire' => (int) $this->setting['expire']));
				}

				if ($commentsId === -1) {
					//待审核
					$error = $this->db->getError();
					if (empty($error)) {
						$error = '评论发表成功，但需要审核通过后才显示！';
					}
					if (IS_AJAX) {
						$this->ajaxReturn(array(
							'info' => $error,
							'status' => $commentsId,
						));
					} else {
						$this->error($error);
					}
				} else {
					$this->success("评论发表成功！");
				}
			} else {
				$this->error($this->db->getError());
			}
		} else {
			$this->error("请使用post方式新增评论！");
		}
	}

	/**
	 * 使用递归的方式查询出回复评论...效率如何俺也不清楚，能力限制了。。
	 * @param type $id
	 * @return boolean
	 */
	protected function getParentComment($id) {
		if (!$id) {
			return false;
		}
		$where = array(
			'parent' => $id,
			'approved' => 1,
		);
		$count = $this->db->where($where)->count();
		//如果大于5条以上，只显示最久的第一条，和最新的3条
		if ($count > 5) {
			$oldData = $this->db->where($where)->order(array('date' => 'ASC'))->find();
			$newsData = $this->db->where($where)->limit(2)->order(array('date' => 'DESC'))->select();
			//数组从新排序
			sort($newsData);
			array_unshift($newsData, $oldData, array(
				'id' => 'load',
				'display' => 'none', //标识这条评论不显示
				'comment_id' => $oldData['comment_id'],
				'parent' => $oldData['parent'],
				'info' => '已经省略中间部分...',
			));
			$data = $newsData;
		} else {
			$data = $this->db->where($where)->order(array('date' => 'ASC'))->select();
		}
		if ($data) {
			foreach ($data as $r) {
				$this->getParentComment((int) $r['id']);
				$this->arr[] = $r;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 得到子级数组
	 * @param int
	 * @return array
	 */
	protected function get_child($myid) {
		$a = $newarr = array();
		if (is_array($this->arr)) {
			foreach ($this->arr as $id => $a) {
				if ($a['parent'] == $myid) {
					$newarr[$id] = $a;
				}

			}
		}
		return $newarr ? $newarr : false;
	}

	/**
	 * 得到树型结构数组
	 * @param int $myid，开始父id
	 */
	protected function get_tree_array($myid = 0) {
		$retarray = array();
		//一级栏目数组
		$child = $this->get_child($myid);
		if (is_array($child)) {
			//数组长度
			$total = count($child);
			foreach ($child as $id => $value) {
				@extract($value);
				$retarray[$value['id']] = $value;
				$retarray[$value['id']]["child"] = $this->get_tree_array($id, '');
			}
		} else {
			return false;
		}
		return array_values($retarray);
	}

}
