<?php

// +----------------------------------------------------------------------
// | 评论模型
// +----------------------------------------------------------------------

namespace Comments\Model;

use Common\Model\Model;

class CommentsModel extends Model {

	//审核状态
	const statusCheck = -1;
	//拒绝状态
	const statusRefuse = 0;
	//替换状态
	const statusReplace = -2;

	//默认存储副表id
	public $sideTables = 0;
	//评论附加字段
	public $sideTablesField = '';
	//主表字段
	public $mainField = array(
		'user_id' => array(),
	);
	//副表字段
	public $secondaryField = array();
	//副表名称
	public $viceTableName = "";
	//主表自动验证因子
	protected $_autoMain = array();
	//副表自动验证因子
	protected $_autoSecondary = array(
		array('content', 'require', '评论内容不能为空！', 1),
	);
	//评论状态，1为审核通过，0为待审核
	protected $commentsApproved = 1;
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('comment_id', 'require', '所属信息id不能为空！', 1),
		array('author', 'require', '评论作者不能为空！', 1),
		array('author_email', 'require', '评论作者联系邮箱不能为空！', 1),
		array('author_email', 'email', '邮箱输入不正确！', 1),
		array('user_id', 'number', '用户id必须为数字！'),
	);
	protected $_auto = array(
		// 新增的时候把date字段设置为当前时间
		array('date', 'time', 1, 'function'),
		// 新增时把agent字段设置为当前用户的浏览器信息
		array('agent', 'getAgent', 1, 'callback'),
		// 设置stb副表信息
		array('stb', 'getSideTables', 1, 'callback'),
		// 评论着ip
		array('author_ip', 'get_client_ip', 1, 'function'),
	);

	/**
	 * 增加评论
	 * @param type $data 评论数据
	 * @return boolean 成功返回评论id，失败返回false
	 */
	public function addComments($data) {
		if (!is_array($data)) {
			$this->error = '数据类型有误！';
			return false;
		}
		//增加行为标签
		tag('comment_add_begin', $data);
		//更新自动验证条件因子
		$this->autoAddSave();
		//主表字段内容映射
		$mainData = array();
		foreach ($this->mainField as $field => $v) {
			$mainData[$field] = $data[$field] ? $data[$field] : '';
		}
		//信息ID comment_id
		$mainData['comment_id'] = $data['comment_id'];
		//回复评论id
		$mainData['parent'] = $data['parent'];
		//如果是空值，直接赋值默认值
		if (empty($mainData['parent'])) {
			$mainData['parent'] = 0;
		}
		if (empty($mainData['user_id'])) {
			$mainData['user_id'] = 0;
		}
		$mainData = $this->token(false)->create($mainData);
		if (!$mainData) {
			return false;
		}

		//======副表======
		$secondaryField = array();
		foreach ($this->secondaryField as $field => $v) {
			$secondaryField[$field] = $data[$field] ? $data[$field] : '';
		}
		//评论内容
		$secondaryField['content'] = $data['content'];

		//敏感词过滤
		if (defined("IN_ADMIN") && IN_ADMIN == false) {
			$filterStatus = $this->commentsFilter($secondaryField['content']);
			if (self::statusRefuse === $filterStatus) {
				return false;
			} else if (self::statusCheck === $filterStatus) {
				//有审核关键字，所以评论设置为审核状态
				$mainData['approved'] = 0;
			}
		}
		//副表对象
		$secondaryDb = M($this->viceTableName());
		$secondaryField = $secondaryDb->token(false)->validate($this->_autoSecondary)->create($secondaryField);
		if (!$secondaryField) {
			//设置错误信息
			$this->error = $secondaryDb->getError();
			return false;
		}

		//评论状态
		if (!isset($mainData['approved'])) {
			$mainData['approved'] = $this->commentsApproved;
		}

		//添加信息到评论主表
		$commentsId = $this->add($mainData);
		if ($commentsId) {
			$secondaryField['id'] = $commentsId;
			$secondaryField['comment_id'] = $mainData['comment_id'];
			if ($secondaryDb->add($secondaryField)) {
				//状态码 -1 审核状态， 大于0 评论id， 0或false 评论发表失败。
				$status = $commentsId;
				if (isset($filterStatus) && true !== $filterStatus) {
					$status = $filterStatus;
				} else if (!$mainData['approved']) {
					$status = self::statusCheck;
				}
				//行为标签
				$tagData = array_merge($mainData, $secondaryField);
				tag('comment_add_end', $tagData);
				return $status;
			} else {
				$this->where(array('id' => $commentsId))->delete();
				$this->error = '评论入库失败！';
				return false;
			}
		} else {
			$this->error = '评论入库失败！';
			return false;
		}
	}

	/**
	 * 编辑评论
	 * @param type $data 评论数据
	 * @return boolean 成功返回评论id，失败返回false
	 */
	public function editComments($data) {
		if (!is_array($data)) {
			$this->error = '数据类型有误！';
			return false;
		}
		//增加行为标签
		tag('comment_edit_begin', $data);
		//原评论
		$info = $this->where(array("id" => $data['id']))->find();
		if (!$info) {
			$this->error = '该评论不存在！';
			return false;
		}
		//更新自动验证条件因子
		$this->autoAddSave();
		//主表字段内容映射
		$mainData = array();
		foreach ($this->mainField as $field => $v) {
			$mainData[$field] = $data[$field];
		}
		//信息ID comment_id
		$mainData['comment_id'] = $info['comment_id'];
		$mainData = $this->token(false)->create($mainData, 2);
		if (!$mainData) {
			return false;
		}

		//======副表======
		$secondaryField = array();
		foreach ($this->secondaryField as $field => $v) {
			$secondaryField[$field] = $data[$field];
		}
		//评论内容
		$secondaryField['content'] = $data['content'];

		//敏感词过滤
		if (defined("IN_ADMIN") && IN_ADMIN == false) {
			$filterStatus = $this->commentsFilter($secondaryField['content']);
			if (self::statusRefuse === $filterStatus) {
				return false;
			} else if ($filterStatus === self::statusCheck) {
				//有审核关键字，所以评论设置为审核状态
				$mainData['approved'] = 0;
			}
		}
		//副表对象
		$secondaryDb = D($this->viceTableName($info['stb']));
		$secondaryField = $secondaryDb->token(false)->validate($this->_autoSecondary)->create($secondaryField, 2);
		if (!$secondaryField) {
			//设置错误信息
			$this->error = $secondaryDb->getError();
			return false;
		}

		//编辑信息到评论主表
		$commentsId = $this->where(array("id" => $data['id']))->save($mainData);
		if (false !== $commentsId) {
			if (false !== $secondaryDb->where(array("id" => $data['id']))->save($secondaryField)) {
				//状态码 -1 审核状态， 大于0 评论id， 0或false 评论发表失败。
				$status = $commentsId;
				if (isset($filterStatus) && true !== $filterStatus) {
					$status = $filterStatus;
				} else if (!$mainData['approved']) {
					$status = self::statusCheck;
				}
				//行为标签
				$tagData = array_merge($mainData, $secondaryField);
				tag('comment_edit_end', $tagData);
				return $status;
			} else {
				$this->error = '更新数据库失败！';
				return false;
			}
		} else {
			$this->error = '评论更新失败！';
			return false;
		}
	}

	/**
	 * 删除评论
	 * @param type $ids 评论id，可以是数组
	 * @return boolean
	 */
	public function deleteComments($ids) {
		if (!$ids) {
			$this->error = '数据类型有误！';
			return false;
		}
		//增加行为标签
		tag('comment_delete_begin', $ids);
		//判断是批量删除还是单条删除
		if (is_array($ids)) {
			$list = $this->where(array('id' => array('IN', $ids)))->select();
			if (!$list) {
				$this->error = '评论不存在！';
				return false;
			}
			//取出需要删除副表id
			$sideId = array();
			foreach ($list as $r) {
				if ($r['id']) {
					$sideId[$r['stb']][] = $r['id'];
				}
			}
			//删除主表评论
			if (false !== $this->where(array('id' => array('IN', $ids)))->delete()) {
				//删除副表内容
				foreach ($sideId as $stb => $dss) {
					M($this->viceTableName($stb))->where(array('id' => array('IN', $dss)))->delete();
				}
				tag('comment_delete_end', $ids);
				return true;
			} else {
				$this->error = '删除失败！';
				return false;
			}
		} else {
			$info = $this->where(array('id' => $ids))->find();
			if (!$info) {
				$this->error = '评论不存在！';
				return false;
			}
			if (false !== $this->where(array('id' => $ids))->delete()) {
				M($this->viceTableName($stb))->where(array('id' => $ids))->delete();
				return true;
			} else {
				$this->error = '删除失败！';
				return false;
			}
		}
		return false;
	}

	/**
	 * 通过标识字段进行删除评论
	 * @param type $comment_id 标识 例如 c-15-3
	 * @return boolean
	 */
	public function deleteCommentsMark($comment_id) {
		if (!$comment_id) {
			$this->error = '数据类型有误！';
			return false;
		}
		//判断是批量删除还是单条删除
		if (is_array($comment_id)) {
			$list = $this->where(array('comment_id' => array('IN', $comment_id)))->select();
			if (!$list) {
				$this->error = '评论不存在！';
				return false;
			}
			//取出需要删除副表id
			$sideId = array();
			foreach ($list as $r) {
				if ($r['id']) {
					$sideId[$r['stb']][] = $r['id'];
				}
			}
			//删除主表评论
			if (false !== $this->where(array('comment_id' => array('IN', $comment_id)))->delete()) {
				//删除副表内容
				foreach ($sideId as $stb => $dss) {
					M($this->viceTableName($stb))->where(array('id' => array('IN', $dss)))->delete();
				}
				return true;
			} else {
				$this->error = '删除失败！';
				return false;
			}
		} else {
			$info = $this->where(array('comment_id' => $comment_id))->find();
			if (!$info) {
				$this->error = '评论不存在！';
				return false;
			}
			if (false !== $this->where(array('comment_id' => $comment_id))->delete()) {
				M($this->viceTableName($stb))->where(array('id' => $info['id']))->delete();
				return true;
			} else {
				$this->error = '删除失败！';
				return false;
			}
		}
	}

	/**
	 * 审核评论
	 * @param type $ids $ids $ids 评论id，可以是数组
	 * @param type $approved 状态
	 * @return boolean
	 */
	public function checkComments($ids, $approved = 1) {
		if (!$ids) {
			$this->error = '数据类型有误！';
			return false;
		}
		$tagArray = array(
			'ids' => $ids,
			'status' => $approved,
		);
		tag('comment_check_begin', $tagArray);
		//判断是批量审核还是单条审核
		if (is_array($ids)) {
			$status = $this->where(array('id' => array('IN', $ids)))->save(array("approved" => $approved));
			tag('comment_check_end', $tagArray);
			return $status;
		} else {
			$status = $this->where(array('id' => $ids))->save(array("approved" => $approved));
			tag('comment_check_end', $tagArray);
			return $status;
		}
		return false;
	}

	/**
	 * 内容过滤
	 * @param type $content 需要过滤的内容
	 * @return boolean
	 */
	public function commentsFilter(&$content) {
		$Filter = service("Filter");
		$status = $Filter->check($content);
		switch ($status) {
			case self::statusCheck: //表示需要审核
				$this->error = $Filter->getError();
				return self::statusCheck;
				break;
			case self::statusRefuse: //禁止发表
				$this->error = $Filter->getError();
				return self::statusRefuse;
				break;
			case self::statusReplace: //禁止发表
				$this->error = $Filter->getError();
				return self::statusReplace;
				break;
		}
		return true;
	}

	/**
	 * 动态生成自动验证条件
	 * @return type 自动验证条件数组
	 */
	public function autoAddSave() {
		$this->sideTablesField();
		foreach ($this->sideTablesField as $k => $v) {
			if ($v['issystem'] == 1) {
				$this->mainField[$v['f']] = $v;
				//非空验证
				if ($v['ismust'] == 1) {
					$this->_validate[] = array($v['f'], 'require', $v['fzs'] ? $v['fzs'] : $v['fname'] . '不能为空！', 1);
				}
				//正则验证
				if ($v['regular']) {
					$this->_validate[] = array($v['f'], $v['regular'], $v['fzs'] ? $v['fzs'] : $v['fname'] . '输入的信息有误！', 2, 'regex');
				}
			} else {
				$this->secondaryField[$v['f']] = $v;
				//非空验证
				if ($v['ismust'] == 1) {
					$this->_autoSecondary[] = array($v['f'], 'require', $v['fzs'] ? $v['fzs'] : $v['fname'] . '不能为空！', 1);
				}
				//正则验证
				if ($v['regular']) {
					$this->_autoSecondary[] = array($v['f'], $v['regular'], $v['fzs'] ? $v['fzs'] : $v['fname'] . '输入的信息有误！', 2, 'regex');
				}
			}
		}

		return $this->_auto;
	}

	/**
	 * 获取附加字段
	 * @return type
	 */
	public function sideTablesField() {
		if (empty($this->sideTablesField)) {
			$this->sideTablesField = M("CommentsField")->select();
		}
		return $this->sideTablesField;
	}

	/**
	 * 获取当前评论存储所在附表id
	 * @return int 副表编号
	 */
	public function getSideTables() {
		if (!$this->sideTables) {
			$setting = cache('Comments_setting');
			$this->sideTables = $setting['stb'] ? $setting['stb'] : 1;
			//设置评论审核
			if (defined("IN_ADMIN") && IN_ADMIN) {
				$this->commentsApproved = 1;
			} else {
				$this->commentsApproved = (int) $setting['check'] == 0 ? 1 : 0;
			}
		}
		return $this->sideTables;
	}

	/**
	 * 设置副表名称
	 * @param type $sideTables 副表id
	 * @return type
	 */
	public function viceTableName($sideTables = false) {
		$tab = "CommentsData_";
		if ($sideTables) {
			$this->viceTableName = $tab . (int) $sideTables;
		} else {
			if (!$this->sideTables) {
				$this->getSideTables();
			}
			$this->viceTableName = $tab . $this->sideTables;
		}
		return $this->viceTableName;
	}

	/**
	 * 获取浏览器ua信息
	 * @return type
	 */
	public function getAgent() {
		return substr($_SERVER['HTTP_USER_AGENT'], 0, 254);
	}

	/**
	 * 检查文章信息是否允许评论
	 * @param type $catid 栏目id
	 * @param type $id 信息id
	 * @return boolean 允许评论返回true，不允许评论返回false
	 */
	public function noAllowComments($catid, $id) {
		if (!$catid || !$id) {
			return false;
		}
		$modelid = getCategory($catid, 'modelid');
		$tablename = ucwords(getModel($modelid, 'tablename'));
		if (empty($tablename)) {
			return false;
		}
		$db = M("{$tablename}Data");
		$allow_comment = $db->where(array("id" => $id))->getField("allow_comment");
		if ((int) $allow_comment <= 0) {
			return false;
		} else {
			return true;
		}
		return false;
	}

	/**
	 * 评论表情替换
	 * @param type $content 评论内容
	 * @param string $emotionPath 表情存放路径，以'/'结尾
	 * @param type $classStyle 表情img附加样式
	 * @return boolean
	 */
	public function replaceExpression(&$content, $emotionPath = '', $classStyle = '') {
		if (!$content) {
			return false;
		}
		//表情存放路径
		if (empty($emotionPath)) {
			$emotionPath = CONFIG_SITEURL_MODEL . 'statics/images/emotion/';
		}
		$cacheReplaceExpression = S('cacheReplaceExpression');
		if ($cacheReplaceExpression) {
			$replace = $cacheReplaceExpression;
		} else {
			$replace = D('Comments/Emotion')->cacheReplaceExpression($emotionPath, $classStyle);
		}
		//替换表情
		$content = strtr($content, $replace);
		return true;
	}

	/**
	 * 评论防火墙
	 * @return boolean
	 */
	public function commentsFirewall() {
		return false;
	}

	/**
	 * 生成评论配置缓存
	 * @return type
	 */
	public function comments_cache() {
		$data = M("CommentsSetting")->find();
		//生成缓存
		cache("Comments_setting", $data);
		S('cacheReplaceExpression', NULL);
		return $data;
	}

}
