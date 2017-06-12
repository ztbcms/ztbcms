<?php

// +----------------------------------------------------------------------
// | 栏目模型
// +----------------------------------------------------------------------

namespace Content\Model;

use Common\Model\Model;

class CategoryModel extends Model {

	//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
	protected $_validate = array(
		array('catname', 'require', '栏目名称不能为空！', 1, 'regex', 3),
		array('catdir', 'require', '英文目录不能为空！', 1, 'regex', 3),
		array('catdir', 'checkCatdir', '目录名称已存在！', 1, 'callback', 1),
		array('setting', 'checkSetting', 'Setting配置信息有误！', 1, 'callback', 1),
		//验证栏目类型
		array('type', array(0, 1, 2), '栏目类型错误！', 1, 'in', 3),
	);
	//自动完成
	protected $_auto = array(
		//array(填充字段,填充内容,填充条件,附加规则)
		array('module', 'content'),
	);

	/**
	 * 验证setting配置信息
	 * @param string $setting
	 * @return boolean
	 */
	public function checkSetting($setting, $type = "") {
		$type = $type ? $type : (int) $_REQUEST['type'];
		if ($type == 2) {
			return true;
		}
		if (!$setting) {
			return true;
		}
		$setting = unserialize($setting);
		if ((!$setting['category_ruleid'] || !$setting['category_ruleid']) && (int) $type != 2) {
			return false;
		}
		return true;
	}

	/**
	 * 检查目录是否存在
	 * @param string $catdir 栏目目录
	 * @param int $catid 栏目ID
	 * @param int $parentid 父栏目ID
	 * @param boolean $old_catdir 原来的栏目目录
	 * @param int|boolean $type 栏目类型
	 * @return boolean
	 */
	public function checkCatdir($catdir, $catid = 0, $parentid = 0, $old_catdir = false, $type = false) {
		return true;
	}

	/**
	 * 添加栏目
	 * @param array $post 页面提交数据
	 * @return boolean
	 */
	public function addCategory($post) {
		if (empty($post)) {
			$this->error = '添加栏目数据不能为空！';
			return false;
		}
		$data = $post['info'];
		//栏目类型
		$data['type'] = (int) $post['type'];
		//表单令牌
		$data[C("TOKEN_NAME")] = $post[C("TOKEN_NAME")];
		$data['setting'] = $post['setting'];
		//终极栏目设置
		if (!isset($data['child'])) {
			$data['child'] = 1;
		}
		//栏目生成静态配置
		if ($data['setting']['ishtml']) {
			$data['setting']['category_ruleid'] = $post['category_html_ruleid'];
		} else {
			$data['setting']['category_ruleid'] = $post['category_php_ruleid'];
		}
		//栏目是否生成静态
		$data['sethtml'] = $data['setting']['ishtml'] ? 1 : 0;
		//内容生成静态配置
		if ($data['setting']['content_ishtml']) {
			$data['setting']['show_ruleid'] = $post['show_html_ruleid'];
		} else {
			$data['setting']['show_ruleid'] = $post['show_php_ruleid'];
		}
		//判断URL规则是否有设置
		if ((int) $data['type'] == 0) {
            //内部栏目
			if (empty($data['setting']['category_ruleid'])) {
				$this->error = '栏目URL规则没有设置！';
				return false;
			}
			if (empty($data['setting']['show_ruleid']) && $data['child']) {
				$this->error = '栏目内容页URL规则没有设置！';
				return false;
			}
			//添加modelid自动验证规则
			array_unshift($this->_validate, array('modelid', 'require', '所属模型不能为空！', 1, 'regex', 3));
		} else if ((int) $data['type'] == 1) {
            //单页栏目
			if (empty($data['setting']['category_ruleid'])) {
				$this->error = '栏目URL规则没有设置！';
				return false;
			}
		}
		load('Content/iconvfunc');
		//栏目拼音
		$catname = iconv('utf-8', 'gbk', $data['catname']);
		$letters = gbk_to_pinyin($catname);
		$data['letter'] = strtolower(implode('', $letters));
		//序列化setting数据
		$data['setting'] = serialize($data['setting']);
		$data = $this->create($data, 1);
		if ($data) {
			if ((int) $data['type'] != 2) {
				//绑定域名
				$data['domain'] = $data['url'];
			}
			//添加数据
			$catid = $this->add($data);
			if ($catid) {
				//更新缓存
				cache('Category', NULL);
				//更新附件状态
				if ($data['image']) {
					//更新附件状态，把相关附件和文章进行管理
					service("Attachment")->api_update('', 'catid-' . $catid, 1);
				}
				//扩展字段处理
				if ($post['extend']) {
					$this->extendField($catid, $post);
				}
				return $catid;
			} else {
				$this->error = '栏目添加失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 编辑栏目
	 * @param array $post 页面提交过来的数据
	 * @return boolean
	 */
	public function editCategory($post) {
		if (empty($post)) {
			$this->error = '添加栏目数据不能为空！';
			return false;
		}
		$catid = $post['catid'];
		$data = $post['info'];
		//查询该栏目是否存在
		$info = $this->where(array('catid' => $catid))->find();
		if (empty($info)) {
			$this->error = '该栏目不存在！';
			return false;
		}
		unset($data['catid'], $info['catid'], $data['module'], $data['child']);
		//表单令牌
		$data[C("TOKEN_NAME")] = $post[C("TOKEN_NAME")];
		$data['setting'] = $post['setting'];
		//内部栏目
		if ((int) $info['type'] != 2) {
			if ($data['setting']['ishtml']) {
				$data['setting']['category_ruleid'] = $post['category_html_ruleid'];
			} else {
				$data['setting']['category_ruleid'] = $post['category_php_ruleid'];
				$data['url'] = '';
			}
		}
		//栏目生成静态配置
		if ($data['setting']['ishtml']) {
			$data['setting']['category_ruleid'] = $post['category_html_ruleid'];
		} else {
			$data['setting']['category_ruleid'] = $post['category_php_ruleid'];
		}
		//内容生成静态配置
		if ($data['setting']['content_ishtml']) {
			$data['setting']['show_ruleid'] = $post['show_html_ruleid'];
		} else {
			$data['setting']['show_ruleid'] = $post['show_php_ruleid'];
		}
		//栏目是否生成静态
		$data['sethtml'] = $data['setting']['ishtml'] ? 1 : 0;
		//判断URL规则是否有设置
		if ((int) $info['type'] == 0) {
            //内部栏目
			if (empty($data['setting']['category_ruleid'])) {
				$this->error = '栏目URL规则没有设置！';
				return false;
			}
			if (empty($data['setting']['show_ruleid']) && $data['child']) {
				$this->error = '栏目内容页URL规则没有设置！';
				return false;
			}
			//添加modelid自动验证规则
			array_unshift($this->_validate, array('modelid', 'require', '所属模型不能为空！', 1, 'regex', 3));
		} else if ((int) $info['type'] == 1) {
            //单页栏目
			if (empty($data['setting']['category_ruleid'])) {
				$this->error = '栏目URL规则没有设置！';
				return false;
			}
		}
		load('Content/iconvfunc');
		//栏目拼音
		$catname = iconv('utf-8', 'gbk', $data['catname']);
		$letters = gbk_to_pinyin($catname);
		$data['letter'] = strtolower(implode('', $letters));
		//序列化setting数据
		$data['setting'] = serialize($data['setting']);
		$data = $this->create($data, 2);
		if ($data) {
			if ((int) $info['type'] != 2) {
				//绑定域名
				$data['domain'] = $data['url'];
			}
			if ($this->where(array("catid" => $catid))->save($data) !== false) {
				//更新缓存
				cache('Category', NULL);
				getCategory($catid, '', true);
				//更新附件状态
				if ($data['image']) {
					//更新附件状态，把相关附件和文章进行管理
					service("Attachment")->api_update('', 'catid-' . $catid, 1);
				}
				//扩展字段处理
				if ($post['extend']) {
					$this->extendField($catid, $post);
				}
				return true;
			} else {
				$this->error = '栏目修改失败！';
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 删除栏目，如果有子栏目，会删除对应的子目录
	 * @param array|string $catid 可以是数组，可以是栏目id
	 * @return boolean
	 */
	public function deleteCatid($catid) {
		if (!$catid) {
			return false;
		}
		$where = array();
		//取得子栏目
		if (is_array($catid)) {
			$where['catid'] = array("IN", $catid);
			$catList = $this->where($where)->select();
			foreach ($catList as $cat) {
				//是否存在子栏目
				if ($cat['child'] && $cat['type'] == 0) {
					$arrchildid = explode(",", $cat['arrchildid']);
					unset($arrchildid[0]);
					$catid = array_merge($catid, $arrchildid);
				}
			}
			$where['catid'] = array("IN", $catid);
		} else {
			$where['catid'] = $catid;
			$catInfo = $this->where($where)->find();
			//是否存在子栏目
			if ($catInfo['child'] && $catInfo['type'] == 0) {
				$arrchildid = explode(",", $catInfo['arrchildid']);
				unset($arrchildid[0]);
				$catid = array_merge($arrchildid, array($catid));
				$where['catid'] = array("IN", $catid);
			}
		}
		//检查是否存在数据，存在数据不执行删除
		if (is_array($catid)) {
			$modeid = array();
			foreach ($catid as $cid) {
				$catinfo = getCategory($cid);
				if ($catinfo['modelid'] && $catinfo['type'] == 0) {
					$modeid[$catinfo['modelid']] = $catinfo['modelid'];
				}
			}
			foreach ($modeid as $mid) {
				$tbname = ucwords(getModel($mid, 'tablename'));
				if (!$tbname) {
					return false;
				}
				if ($tbname && M($tbname)->where(array("catid" => array("IN", $catid)))->count()) {
					return false;
				}
			}
		} else {
			$catinfo = getCategory($catid);
			$tbname = ucwords(getModel($catInfo['modelid'], 'tablename'));
			if (!$tbname && $catinfo['type'] == 0) {
				return false;
			}
			if ($tbname && $catinfo['type'] == 0 && M($tbname)->where(array("catid" => $catid))->count()) {
				return false;
			}
		}
		$status = $this->where($where)->delete();
		//更新缓存
		cache('Category', NULL);
		if (false !== $status) {
			$this->extendFieldDel($where);
			//删除对应栏目的权限列表
			M("Category_priv")->where($where)->delete();
			if (is_array($catid)) {
				//删除附件
				foreach ($catid as $cid) {
					service("Attachment")->api_delete('catid-' . $cid);
				}
			} else {
				service("Attachment")->api_delete('catid-' . $catid);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取子栏目ID列表
	 * @staticvar type $categorys 静态变量 栏目数据
	 * @param string $catid 栏目id
	 * @return string 返回栏目子列表，以逗号隔开
	 */
	public function getArrchildid($catid) {
		if (!$this->categorys) {
			$this->categorys = cache('Category');
		}
		$arrchildid = $catid;
		if (is_array($this->categorys)) {
			foreach ($this->categorys as $id => $cat) {
				if ($cat['parentid'] && $id != $catid && $cat['parentid'] == $catid) {
					$arrchildid .= ',' . $this->getArrchildid($id);
				}
			}
		}
		return $arrchildid;
	}

	/**
	 * 获取父栏目ID列表
	 * @param integer $catid 栏目ID
	 * @param string $arrparentid 父目录ID
	 * @param integer $n 查找的层次
     * @return string
	 */
	public function getArrparentid($catid, $arrparentid = '', $n = 1) {
		if (empty($this->categorys)) {
			$this->categorys = cache('Category');
		}
		if ($n > 10 || !is_array($this->categorys) || !isset($this->categorys[$catid])) {
			return false;
		}
		//获取当前栏目的上级栏目ID
		$parentid = $this->categorys[$catid]['parentid'];
		//所有父ID
		$arrparentid = $arrparentid ? $parentid . ',' . $arrparentid : $parentid;
		if ($parentid) {
			$arrparentid = $this->getArrparentid($parentid, $arrparentid, ++$n);
		} else {
			$this->categorys[$catid]['arrparentid'] = $arrparentid;
		}
		return $arrparentid;
	}

	/**
	 * 获取扩展字段
	 * @param string $catid 栏目ID
	 * @return boolean
	 */
	public function getExtendField($catid) {
		if (empty($catid)) {
			return false;
		}
		$extendFieldLisr = M('CategoryField')->where(array('catid' => $catid))->select();
		foreach ($extendFieldLisr as $k => $rs) {
			$extendFieldLisr[$k]['setting'] = unserialize($rs['setting']);
		}
		return $extendFieldLisr;
	}

	/**
	 * 删除某栏目下的扩展字段
	 * @param array $where 删除条件
	 * @return boolean
	 */
	protected function extendFieldDel($where) {
		if (empty($where)) {
			return false;
		}
		return M('CategoryField')->where($where)->delete() !== false ? true : false;
	}

	/**
	 * 扩展字段处理
	 * @param string $catid 栏目ID
	 * @param array $post 数据
	 * @return boolean
	 */
	public function extendField($catid, $post) {
		if (empty($catid) || intval($catid) < 1 || empty($post)) {
			return false;
		}
		C('TOKEN_ON', false);
		//时间
		$time = time();
		//栏目信息
		$info = $this->where(array('catid' => $catid))->find();
		if (empty($info)) {
			return false;
		}
		$info['setting'] = unserialize($info['setting']);
		//删除不存在的选项
		if (!empty($post['extenddelete'])) {
			$extenddelete = explode('|', $post['extenddelete']);
			M('CategoryField')->where(array('fid' => array('IN', $extenddelete)))->delete();
		}
		//查询出该栏目扩展字段列表
		$extendFieldLisr = array();
		foreach (M('CategoryField')->where(array('catid' => $catid))->field('fieldname')->select() as $rs) {
			$extendFieldLisr[] = $rs['fieldname'];
		}
		//检查是否有新怎字段
		if (!empty($post['extend_config']) && is_array($post['extend_config'])) {
			$validate = array(
				array('catid', 'require', '栏目ID不能为空！', 1, 'regex', 3),
				array('fieldname', 'require', '键名不能为空！', 1, 'regex', 3),
				array('type', 'require', '类型不能为空！', 1, 'regex', 3),
				array('fieldname', '/^[a-z_0-9]+$/i', '键名只支持英文、数字、下划线！', 0, 'regex', 3),
			);
			foreach ($post['extend_config'] as $field => $rs) {
				//如果已经存在则跳过
				if (in_array($field, $extendFieldLisr)) {
					continue;
				}
				$rs['catid'] = $catid;
				$data = M('CategoryField')->validate($validate)->create($rs);
				if ($data) {
					$data['createtime'] = $time;
					$setting = $data['setting'];
					if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
						$option = array();
						$optionList = explode("\n", $setting['option']);
						if (is_array($optionList)) {
							foreach ($optionList as $rs) {
								$rs = explode('|', $rs);
								if (!empty($rs)) {
									$option[] = array(
										'title' => $rs[0],
										'value' => $rs[1],
									);
								}
							}
							$setting['option'] = $option;
						}
					}
					$data['setting'] = serialize($setting);
					$fieldId = M('CategoryField')->add($data);
					if ($fieldId) {
						$extendFieldLisr[] = $field;
					}
				} else {
					continue;
				}
			}
		}
		//值更新
		$extend = array();
		if (!empty($post['extend']) || is_array($post['extend'])) {
			foreach ($post['extend'] as $field => $value) {
				if (in_array($field, $extendFieldLisr)) {
					$extend[$field] = $value;
				}
			}
			$info['setting']['extend'] = $extend;
		}
		//更新栏目
		$status = $this->where(array('catid' => $catid))->save(
			array(
				'setting' => serialize($info['setting']),
			)
		);
		//删除缓存
		getCategory($catid, '', true);
		return $status !== false ? true : false;
	}

	//刷新栏目索引缓存
	public function category_cache() {
		$data = $this->order("listorder ASC")->select();
		$CategoryIds = array();
		foreach ($data as $r) {
			$CategoryIds[$r['catid']] = array(
				'catid' => $r['catid'],
				'parentid' => $r['parentid'],
//                'arrparentid' => $r['arrparentid'],
				//                'child' => $r['child'],
				//                'arrchildid' => $r['arrchildid'],
				//                'type' => $r['type'],
			);
		}
		cache("Category", $CategoryIds);
		return $CategoryIds;
	}

}
