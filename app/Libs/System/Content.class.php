<?php

// +----------------------------------------------------------------------
// | 内容处理
// +----------------------------------------------------------------------

namespace Libs\System;

use Content\Model\ContentModel;

class Content extends Components {

	//数据
	protected $data = array();
	//错误信息
	protected $error = NULL;
	protected $id = 0;
	protected $catid = 0;
	protected $modelid = 0;

	/**
	 * 连接内容处理服务
	 * @access public
	 * @param array $options 配置数组
	 * @return Content
	 */
	static public function getInstance($options = array()) {
		static $systemHandier;
		if (empty($systemHandier)) {
			$systemHandier = new self($options);
		}
		return $systemHandier;
	}

	/**
	 * 获取错误提示
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 设置数据对象值
	 * @access public
	 * @param mixed $data 数据
	 * @return Content|array
	 */
	public function data($data = '') {
		if ('' === $data && !empty($this->data)) {
			return $this->data;
		}
		if (is_object($data)) {
			$data = get_object_vars($data);
		} elseif (is_string($data)) {
			parse_str($data, $data);
		} elseif (!is_array($data)) {
			E(L('_DATA_TYPE_INVALID_'));
		}
		$this->data = $data;
		return $this;
	}

	/**
	 * 添加内容
	 * @param array $data 数据
	 * @return boolean
	 */
	public function add($data = []) {
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}
		$this->catid = (int) $data['catid'];
		$this->modelid = getCategory($this->catid, 'modelid');
		//取得表单令牌验证码
		$data[C("TOKEN_NAME")] = $_POST[C("TOKEN_NAME")];
		//标签
		tag('content_add_begin', $data);
		//栏目数据
		$catidinfo = getCategory($data['catid']);
		if (empty($catidinfo)) {
			$this->error = '获取不到栏目数据！';
			return false;
		}
		//setting配置
		$catidsetting = $catidinfo['setting'];
		//前台投稿状态判断
		if (!defined('IN_ADMIN') || (defined('IN_ADMIN') && IN_ADMIN == false)) {
			//前台投稿，根据栏目配置和用户配置
			$Member_group = cache("Member_group");
			$groupid = service('Passport')->groupid;
			//如果会员组设置中设置，投稿不需要审核，直接无视栏目设置
			if ($Member_group[$groupid]['allowpostverify']) {
				$data['status'] = 99;
			} else {
				//前台投稿是否需要审核
				if ($catidsetting['member_check']) {
					$data['status'] = 1;
				} else {
					$data['status'] = 99;
				}
			}
			//添加用户名
            if(empty($data['username'])){
                $data['username'] = service('Passport')->username;
                $data['sysadd'] = 0;
            }
		} else {
            if(empty($data['username'])){
                $data['username'] = \Admin\Service\User::getInstance()->username;
                $data['sysadd'] = 1;
            }
		}
		//检查真实发表时间，如果有时间转换为时间戳
		if ($data['inputtime'] && !is_numeric($data['inputtime'])) {
			$data['inputtime'] = strtotime($data['inputtime']);
		} elseif (!$data['inputtime']) {
			$data['inputtime'] = time();
		}
		//更新时间处理
		if ($data['updatetime'] && !is_numeric($data['updatetime'])) {
			$data['updatetime'] = strtotime($data['updatetime']);
		} elseif (!$data['updatetime']) {
			$data['updatetime'] = time();
		}
		//自动提取摘要，如果有设置自动提取，且description为空，且有内容字段才执行
		$this->description($data);
		$model = ContentModel::getInstance($this->modelid);
		$content_input = new \content_input($this->modelid);
		//保存一份旧数据
		$oldata = $data;
		$data = $content_input->get($data, 1);
		if ($data) {
			$data = $model->relation(true)->create($data, 1);
			if (false == $data) {
				$this->error = $model->getError();
				$this->tokenRecovery($oldata);
				return false;
			}
		} else {
			$this->error = $content_input->getError();
			$this->tokenRecovery($oldata);
			return false;
		}
		//自动提取缩略图，从content 中提取
		$this->getThumb($data);
		$oldata['thumb'] = $data['thumb'];
		//添加内容
		$this->id = $id = $data['id'] = $oldata['id'] = $model->relation(true)->add($data);
		if (false == $id) {
			$this->error = $model->getError();
			$this->tokenRecovery($oldata);
			return false;
		}
		//转向地址
		$urls = array();
		if ($data['islink'] == 1) {
			$urls['url'] = $_POST['linkurl'];
		} else {
			//生成该篇地址
			$urls = $this->generateUrl($data);
		}
		$oldata['url'] = $data['url'] = $urls['url'];
		//更新url
		$model->token(false)->where(array('id' => $id))->save(array('url' => $data['url']));
		$content_update = new \content_update($this->modelid);
		$status = $content_update->update($oldata);
		//发布到其他栏目,只能后台发布才可以使用该功能
		if (defined('IN_ADMIN') && IN_ADMIN) {
			if (is_array($_POST['othor_catid'])) {
				foreach ($_POST['othor_catid'] as $classid => $v) {
					if ($this->catid == $classid) {
						continue;
					}
					$othor_catid[] = $classid;
				}
				//去除重复
				$othor_catid = array_unique($othor_catid);
				$this->othor_catid($othor_catid, $urls['url'], $data, $this->modelid);
			}
		}
		//字段合并
		$model->dataMerger($data);
		//更新附件状态，把相关附件和文章进行管理
		$attachment = service('Attachment');
		$attachment->api_update('', 'c-' . $data['catid'] . '-' . $id, 2);
		//标签
		tag('content_add_end', $data);
		//生成相关
		$generatelish = 0;
		if (defined('IN_ADMIN') && IN_ADMIN) {
			//是否生成内容页
			if ($catidsetting['generatehtml']) {
				//生成静态
				if ($catidsetting['content_ishtml'] && $data['status'] == 99) {
					$this->Html->show($data);
				}
			}
			//生成列表
			if ((int) $catidsetting['generatelish'] > 0) {
				$generatelish = (int) $catidsetting['generatelish'];
			}
		}
		switch ($generatelish) {
			//生成当前栏目
			case 1:
				$this->Html->category($data['catid']);
				break;
			//生成首页
			case 2:
				$this->Html->index();
				break;
			//生成父栏目
			case 3:
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				break;
			//生成当前栏目与父栏目
			case 4:
				$this->Html->category($data['catid']);
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				break;
			//生成父栏目与首页
			case 5:
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				$this->Html->index();
				break;
			//生成当前栏目、父栏目与首页
			case 6:
				$this->Html->category($data['catid']);
				$this->Html->createRelationHtml($data['catid']);
				$this->Html->index();
				break;
		}
		//生成上一篇下一篇
		if ($data['status'] == 99) {
			$this->relatedContent($this->catid, $this->id, 'add');
		}
		return $id;
	}

	/**
	 * 修改内容
	 * @param array $data
	 * @return boolean
	 */
	public function edit($data = []) {
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}
		$this->id = (int) $data['id'];
		$this->catid = (int) $data['catid'];
		$this->modelid = getCategory($this->catid, 'modelid');
		//取得表单令牌验证码
		$data[C("TOKEN_NAME")] = $_POST[C("TOKEN_NAME")];
		//标签
		tag('content_edit_begin', $data);
		//栏目数据
		$catidinfo = getCategory($this->catid);
		if (empty($catidinfo)) {
			$this->error = '获取不到栏目数据！';
			return false;
		}
		//setting配置
		$catidsetting = $catidinfo['setting'];
		//前台投稿状态判断
		if (!defined('IN_ADMIN') || (defined('IN_ADMIN') && IN_ADMIN == false)) {
			//前台投稿编辑是否需要审核
			if ($catidsetting['member_editcheck']) {
				$data['status'] = 1;
			}
		}
		$model = ContentModel::getInstance($this->modelid);
		//真实发布时间
		$data['inputtime'] = $inputtime = $model->where(array("id" => $this->id))->getField('inputtime');
		//更新时间处理
		if ($data['updatetime'] && !is_numeric($data['updatetime'])) {
			$data['updatetime'] = strtotime($data['updatetime']);
		} elseif (!$data['updatetime']) {
			$data['updatetime'] = time();
		}
		//自动提取摘要，如果有设置自动提取，且description为空，且有内容字段才执行
		$this->description($data);
		//转向地址
		if ($data['islink'] == 1) {
			$urls["url"] = $_POST['linkurl'];
		} else {
			//生成该篇地址
			$urls = $this->generateUrl($data);
		}
		$data['url'] = $urls["url"];
		$content_input = new \content_input($this->modelid);
		//保存一份旧数据
		$oldata = $data;
		$data = $content_input->get($data, 2);
		if ($data) {
			//数据验证
			$data = $model->relation(true)->create($data, 2);
			if (false == $data) {
				$this->error = $model->getError();
				$this->tokenRecovery($data);
				return false;
			}
		} else {
			$this->error = $content_input->getError();
			return false;
		}
		//自动提取缩略图，从content 中提取
		$this->getThumb($data);
		$oldata['thumb'] = $data['thumb'];
		//数据修改，这里使用关联操作
		$status = $model->relation(true)->where(array('id' => $this->id))->save($data);
		if (false === $status) {
			$this->error = $model->getError();
			$this->tokenRecovery($data);
			return false;
		}
		//字段合并
		$model->dataMerger($data);
		//调用回调更新
		$content_update = new \content_update($this->modelid);
		$content_update->update($oldata);
		//更新附件状态，把相关附件和文章进行管理
		$attachment = service('Attachment');
		$attachment->api_update('', 'c-' . $data['catid'] . '-' . $this->id, 2);
		//标签
		tag('content_edit_end', $data);
		//生成相关
		$generatelish = 0;
		if (defined('IN_ADMIN') && IN_ADMIN) {
			//是否生成内容页
			if ($catidsetting['generatehtml']) {
				//生成静态
				if ($catidsetting['content_ishtml'] && $data['status'] == 99) {
					$this->Html->show($data);
				}
			}
			//如果是未审核，删除已经生成
			if ($catidsetting['content_ishtml'] && !$data['islink'] && $data['status'] != 99) {
				$this->data($data)->deleteHtml();
			}
			//生成列表
			if ((int) $catidsetting['generatelish'] > 0) {
				$generatelish = (int) $catidsetting['generatelish'];
			}
		} else {
			//投稿内容页生成，直接审核通过的直接生成内容页
			if ($data['status'] == 99) {
				//生成静态
				if ($catidsetting['content_ishtml']) {
					$this->Html->show($data);
				}
			} else {
				if ($catidsetting['content_ishtml'] && !$data['islink']) {
					$this->data($data)->deleteHtml();
				}
			}
			//列表生成
			if ((int) $catidsetting['member_generatelish'] > 0) {
				$generatelish = (int) $catidsetting['member_generatelish'];
			}
		}
		//列表生成
		switch ($generatelish) {
			//生成当前栏目
			case 1:
				$this->Html->category($data['catid']);
				break;
			//生成首页
			case 2:
				$this->Html->index();
				break;
			//生成父栏目
			case 3:
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				break;
			//生成当前栏目与父栏目
			case 4:
				$this->Html->category($data['catid']);
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				break;
			//生成父栏目与首页
			case 5:
				if ($catidinfo['parentid']) {
					$this->Html->category($catidinfo['parentid']);
				}
				$this->Html->index();
				break;
			//生成当前栏目、父栏目与首页
			case 6:
				$this->Html->category($data['catid']);
				$this->Html->createRelationHtml($data['catid']);
				$this->Html->index();
				break;
		}
		//生成上一篇下一篇
		$this->relatedContent($this->catid, $this->id, 'edit');
		return true;
	}

	/**
	 * 信息审核
	 * @param string $catid 栏目ID
	 * @param string $id 信息ID
	 * @param int $status 1为未审核,99为审核通过,0为审核不通过
	 * @return boolean
	 */
	public function check($catid = '', $id = '', $status = 99) {
		if (empty($catid) && empty($id)) {
			if (!empty($this->data)) {
				$data = $this->data;
				$catid = $data['catid'];
				$id = $data['id'];
				//模型ID
				$this->modelid = getCategory($catid, 'modelid');
				// 重置数据
				$this->data = array();
			} else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		} else if (is_array($catid)) {
			$data = $catid;
			$catid = $data['catid'];
			$id = $data['id'];
			//模型ID
			$this->modelid = getCategory($catid, 'modelid');
		} else {
			//模型ID
			$this->modelid = getCategory($catid, 'modelid');
			$data = ContentModel::getInstance($this->modelid)->relation(true)->where(array('id' => $id, 'catid' => $catid))->find();
		}
		ContentModel::getInstance($this->modelid)->dataMerger($data);
		C('TOKEN_ON', false);
		//是否生成HTML
		$sethtml = getCategory($catid, 'sethtml');
		//栏目配置信息
		$setting = getCategory($catid, 'setting');
		$content_ishtml = $setting['content_ishtml'];
		$model = ContentModel::getInstance($this->modelid);
		tag('content_check_begin', $data);
		$data['status'] = $status;
		if ($data) {
			if ($model->where(array('id' => $id, 'catid' => $catid))->save(array('status' => $status)) !== false) {
				//判断是否前台投稿
				if ($data['sysadd'] == 0 && $status == 99 && isModuleInstall('Member')) {
					//检查是否已经赠送过积分
					$integral = M('MemberContent')->where(array('content_id' => $id, 'catid' => $catid))->getField('integral');
					if (!$integral) {
						if (service('Passport')->userIntegration($data['username'], $setting['member_addpoint'])) {
							M('MemberContent')->where(array('content_id' => $id, 'catid' => $catid))->save(array('integral' => 1));
						}
					}
				}
				//生成内容页
				if ($content_ishtml && !$data['islink'] && $status == 99) {
					$this->Html->data($data)->show();
					//生成上下篇
					$this->relatedContent($catid, $id);
				}
				//如果是取消审核
				if ($content_ishtml && $status != 99) {
					//则删除生成静态的文件
					$this->data($data)->deleteHtml();
					//删除tags
					D('Content/Tags')->deleteAll($data['id'], $data['catid'], $this->modelid);
				} elseif ($status == 99) {
					//更新tags
					if (strpos($data['tags'], ',') === false) {
						$tags = explode(' ', $data['tags']);
					} else {
						$tags = explode(',', $data['tags']);
					}
					$tags = array_unique($tags);
					D('Content/Tags')->updata(
						$tags, $data['id'], $data['catid'], $this->modelid, array(
							'url' => $data['url'],
							'title' => $data['title'],
						)
					);
				}
			}
		}
		tag('content_check_end', $data);
		return true;
	}

	/**
	 * 删除信息
	 * @param string $id 数组/信息id
	 * @param string $catid 栏目id
	 * @return boolean
	 */
	public function delete($id = '', $catid = '') {
		if (empty($id) || empty($catid)) {
			if (!empty($this->data)) {
				$data = $this->data;
				$id = $data['id'];
				$this->catid = $catid = $data['catid'];
				//模型ID
				$this->modelid = getCategory($this->catid, 'modelid');
				// 重置数据
				$this->data = array();
			} else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		} else if (is_array($id)) {
			$data = $id;
			$id = $data['id'];
			$this->catid = $catid = $data['catid'];
			//模型ID
			$this->modelid = getCategory($this->catid, 'modelid');
		} else {
			$this->catid = $catid;
			//模型ID
			$this->modelid = getCategory($this->catid, 'modelid');
			$model = ContentModel::getInstance($this->modelid);
			$data = $model->relation(true)->where(array('id' => $id))->find();
		}
		ContentModel::getInstance($this->modelid)->dataMerger($data);
		if (getCategory($this->catid) == false) {
			$this->error = '获取不到栏目信息！';
			return false;
		}
		//栏目配置信息
		$setting = getCategory($this->catid, 'setting');
		//内容页是否生成静态
		$content_ishtml = $setting['content_ishtml'];
		if (empty($data)) {
			$this->error = '该信息不存在！';
			return false;
		}
		tag('content_delete_begin', $data);
		if ($content_ishtml && !$data['islink']) {
			$this->data($data)->deleteHtml();
		}
		//调用 content_delete
		$content_update = new \content_delete($this->modelid);
		$content_update->get($data);
		//删除内容
		ContentModel::getInstance($this->modelid)->relation(true)->where(array('id' => $id))->delete();
		//删除附件
		$Attachment = service('Attachment');
		$Attachment->api_delete('c-' . $this->catid . '-' . $id);
		//删除推荐位的信息
		if (!empty($data['posid'])) {
			D('Content/PositionData')->deleteByModeId($this->modelid, $id);
		}
		//标签
		tag('content_delete_end', $data);
		return true;
	}

	/**
	 * 删除静态生成的文章文件
	 * @param string $catid 栏目ID,可以是信息数组
	 * @param string $id 信息ID
	 * @param string $inputtime 真实发布时间
	 * @param string $prefix 自定义文件名
	 * @return boolean
	 */
	public function deleteHtml($catid = '', $id = '', $inputtime = '', $prefix = '') {
		if (empty($catid) && empty($id) && empty($inputtime)) {
			if (!empty($this->data)) {
				$data = $this->data;
				$id = $data['id'];
				$inputtime = $data['inputtime'];
				$prefix = $data['prefix'];
				$this->catid = $catid = $data['catid'];
				$this->modelid = getCategory($this->catid, 'modelid');
				// 重置数据
				$this->data = array();
			} else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		} else if (is_array($catid)) {
			$data = $catid;
			$id = $data['id'];
			$inputtime = $data['inputtime'];
			$prefix = $data['prefix'];
			$this->catid = $catid = $data['catid'];
			$this->modelid = getCategory($this->catid, 'modelid');
		} else {
			$this->catid = $catid;
			$this->modelid = getCategory($this->catid, 'modelid');
			$model = ContentModel::getInstance($this->modelid);
			$data = $model->relation(true)->where(array('id' => $id, 'catid' => $catid))->find();
		}
		ContentModel::getInstance($this->modelid)->dataMerger($data);
		//获取信息生成地址和url
		$urls = $this->generateUrl($data);
		$fileurl = $urls['path'];
		//删除静态文件
		$lasttext = strrchr($fileurl, '.');
		$len = -strlen($lasttext);
		$path = substr($fileurl, 0, $len);
		$path = ltrim($path, '/');
		$filelist = glob(SITE_PATH . $path . '*');
		foreach ($filelist as $delfile) {
			$lasttext = strrchr($delfile, '.');
			if (!in_array($lasttext, array('.htm', '.html', '.shtml'))) {
				continue;
			}
			@unlink($delfile);
		}
		return true;
	}

	/**
	 * 同步发布
	 * @param string $othor_catid 需要同步发布到的栏目ID
	 * @param string $linkurl 原信息地址
	 * @param array $data 原数据，以关联表的数据格式
	 * @param string $modelid 原信息模型ID
	 * @return boolean
	 */
	public function othor_catid($othor_catid, $linkurl, $data, $modelid) {
		//数据检测
		if (!$linkurl || !$othor_catid || !$data || !$modelid) {
			return false;
		}
		C('TOKEN_ON', false);
		//去除ID
		unset($data['id']);
		$model = ContentModel::getInstance($modelid);
		//循环需要同步发布的栏目
		foreach ($othor_catid as $cid) {
			//获取需要同步栏目所属模型ID
			$mid = getCategory($cid, 'modelid');
			//判断模型是否相同
			if ($modelid == $mid) {
                //相同
				$data['catid'] = $cid;
				$_categorys = getCategory($data['catid']);
				//修复当被推送的文章是推荐位的文章时，推送后会把相应属性也推送过去
				$data['posid'] = 0;
				$newid = $model->relation(true)->add($data);
				if (!$newid) {
					continue;
				}
				$othordata = $data;
				$othordata['id'] = $newid;
				if (isset($othordata[$model->getRelationName()]['id'])) {
					$othordata[$model->getRelationName()]['id'] = $newid;
				}
				//更新URL地址
				if ((int) $othordata['islink'] == 1) {
					$nurls = $othordata['url'];
					//更新地址
					$model->where(array('id' => $newid))->save(array('url' => $nurls));
					$othordata['url'] = $nurls;
				} else {
					$nurls = $this->generateUrl($othordata);
					//更新地址
					$model->where(array('id' => $newid))->save(array('url' => $nurls['url']));
					$othordata['url'] = $nurls['url'];
				}
				if (is_array($nurls) && $_categorys['setting']['content_ishtml'] && $othordata['status'] == 99) {
					//生成静态
					$this->Html->show($othordata);
				}
			} else {
				//不同模型，则以链接的形式添加，也就是转向地址
				$dataarray = array('title' => $data['title'],
					'style' => $data['style'],
					'thumb' => $data['thumb'],
					'keywords' => $data['keywords'],
					'description' => $data['description'],
					'status' => $data['status'],
					'catid' => $cid,
					'url' => $linkurl,
					'sysadd' => 1,
					'username' => $data['username'],
					'inputtime' => $data['inputtime'],
					'updatetime' => $data['updatetime'],
					'islink' => 1,
				);
				$newid = ContentModel::getInstance($mid)->relation(true)->add($dataarray);
			}
		}
		return true;
	}

	/**
	 * 生成上下篇
	 * @param string $catid 栏目ID
	 * @param string $id 信息ID
	 * @param string $action 新增还是修改
	 * @return boolean
	 */
	public function relatedContent($catid, $id, $action = 'edit') {
		if (!$catid || !$id) {
			return false;
		}
		$modelid = getCategory($catid, 'modelid');
		$db = ContentModel::getInstance($modelid);
		$where = array();
		$where['catid'] = $catid;
		$where['status'] = 99;
		$where['id'] = array('LT', $id);
		$data[] = $db->relation(true)->where($where)->order(array('id' => 'DESC'))->find();
		if ($action == 'edit') {
			$where['id'] = array('GT', $id);
			$data[] = $db->relation(true)->where($where)->find();
		}
		foreach ($data as $r) {
			if ($r['islink'] || empty($r)) {
				continue;
			}
			$db->dataMerger($r);
			$setting = getCategory($r['catid'], 'setting');
			$content_ishtml = $setting['content_ishtml'];
			if (!$content_ishtml) {
				continue;
			}
			$this->Html->data($r)->show();
		}
		return true;
	}

	/**
	 * 获取URL规则处理后的URL
	 * @param array $data
	 * @return string
	 */
	protected function generateUrl($data) {
		return $this->Url->show($data);
	}

	/**
	 * 获取标题图片
	 * @param array $data
	 */
	protected function getThumb(&$data) {
		$model = ContentModel::getInstance($this->modelid);
		//取得副表下标
		$getRelationName = $model->getRelationName();
		//自动提取缩略图，从content 中提取
		if (empty($data['thumb'])) {
			$isContent = isset($data['content']) ? 1 : 0;
			$content = $isContent ? $data['content'] : $data[$getRelationName]['content'];
			$auto_thumb_no = I('.auto_thumb_no', 1, 'intval') - 1;
			if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
				$data['thumb'] = $matches[3][$auto_thumb_no];
			}
		}
	}

	/**
	 * 自动获取简介
	 * @param array $data
	 */
	protected function description(&$data) {
		//自动提取摘要，如果有设置自动提取，且description为空，且有内容字段才执行
		if (isset($_POST['add_introduce']) && $data['description'] == '' && isset($data['content'])) {
			$content = $data['content'];
			$introcude_length = intval($_POST['introcude_length']);
			$data['description'] = str_cut(str_replace(array("\r\n", "\t", '[page]', '[/page]', '&ldquo;', '&rdquo;', '&nbsp;'), '', strip_tags($content)), $introcude_length);
		}
	}

	/**
	 * 对验证过的token进行复原
	 * @param array $data 数据
     * @return boolean
	 */
	protected function tokenRecovery($data = array()) {
		if (empty($data)) {
			$data = $_POST;
		}
		//TOKEN_NAME
		$tokenName = C('TOKEN_NAME');
		if (empty($data[$tokenName])) {
			return false;
		}
		list($tokenKey, $tokenValue) = explode('_', $data[$tokenName]);
		//如果验证失败，重现对TOKEN进行复原生效
		$_SESSION[$tokenName][$tokenKey] = $tokenValue;
		return true;
	}

}
