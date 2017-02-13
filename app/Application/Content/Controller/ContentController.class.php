<?php

// +----------------------------------------------------------------------
// | 内容管理
// +----------------------------------------------------------------------

namespace Content\Controller;

use Admin\Service\User;
use Common\Controller\AdminBase;
use Content\Model\ContentModel;

class ContentController extends AdminBase {

	//当前栏目id
	public $catid = 0;
	//模型缓存
	protected $model = array();

	//初始化
	protected function _initialize() {
		parent::_initialize();
		$this->catid = I('request.catid', $_POST['info']['catid'], 'intval');
		$this->model = cache('Model');
		//权限验证
		if (User::getInstance()->isAdministrator() !== true) {
			//如果是public_开头的方法通过验证
			if (strpos(ACTION_NAME, 'public_') === false && ACTION_NAME != 'index') {
				//操作
				$action = getCategory($this->catid, 'type') == 0 ? ACTION_NAME : 'init';
				if ($action == 'classlist') {
					$action = 'init';
				}
				$priv_datas = M('CategoryPriv')->where(array('catid' => $this->catid, 'is_admin' => 1, 'roleid' => User::getInstance()->role_id, 'action' => $action))->find();
				if (empty($priv_datas)) {
					$this->error('您没有操作该项的权限！');
				}
			}
		}
	}

	//显示内容管理首页
	public function index() {
		$this->display();
	}

	//显示栏目菜单列表
	public function public_categorys() {
		//是否超级管理员
		$isAdministrator = User::getInstance()->isAdministrator();
		$priv_catids = array();
		//栏目权限 超级管理员例外
		if ($isAdministrator !== true) {
			$role_id = User::getInstance()->role_id;
			$priv_result = M('CategoryPriv')->where(array('roleid' => $role_id, 'action' => 'init'))->select();
			foreach ($priv_result as $_v) {
				$priv_catids[] = $_v['catid'];
			}
		}
		$json = array();
		$categorys = cache('Category');
		foreach ($categorys as $rs) {
			$rs = getCategory($rs['catid']);
			if ($rs['type'] == 2 && $rs['child'] == 0) {
				continue;
			}
			//只显示有init权限的，超级管理员除外
			if ($isAdministrator !== true && !in_array($rs['catid'], $priv_catids)) {
				$arrchildid = explode(',', $rs['arrchildid']);
				$array_intersect = array_intersect($priv_catids, $arrchildid);
				if (empty($array_intersect)) {
					continue;
				}
			}
			$data = array(
				'catid' => $rs['catid'],
				'parentid' => $rs['parentid'],
				'catname' => $rs['catname'],
				'type' => $rs['type'],
			);
			//终极栏目
			if ($rs['child'] == 0) {
				$data['target'] = 'right';
				$data['url'] = U('Content/classlist', array('catid' => $rs['catid']));
				//设置图标
				$data['icon'] = self::$Cache['Config']['siteurl'] . 'statics/js/zTree/zTreeStyle/img/diy/10.png';
			} else {
				$data['isParent'] = true;
			}
			//单页
			if ($rs['type'] == 1 && $rs['child'] == 0) {
				$data['url'] = U('Content/add', array('catid' => $rs['catid']));
				//设置图标
				$data['icon'] = self::$Cache['Config']['siteurl'] . 'statics/js/zTree/zTreeStyle/img/diy/2.png';
			}
			$json[] = $data;
		}
		$this->assign('json', json_encode($json));
		$this->display();
	}

	//栏目信息列表
	public function classlist() {
		if (IS_POST) {
			$this->redirect('classlist', $_POST);
		}
		//当前栏目信息
		$catInfo = getCategory($this->catid);
		if (empty($catInfo)) {
			$this->error('该栏目不存在！', U('Admin/Main/index'));
		}
		//查询条件
		$where = array();
		$where['catid'] = array('EQ', $this->catid);
		$where['status'] = array('EQ', 99);
		//栏目所属模型
		$modelid = $catInfo['modelid'];
		//栏目扩展配置
		$setting = $catInfo['setting'];
		//检查模型是否被禁用
		if (getModel($modelid, 'disabled')) {
			$this->error('模型被禁用！');
		}
		//实例化模型
		$model = ContentModel::getInstance($modelid);
		//数量统计
		$sum = $model->where($where)->count();
		$checkSum = $model->where(array_merge($where, array('status' => 1)))->count();
        $uncheckSum = $model->where(array_merge($where, array('status' => 0)))->count();
		$this->assign('sum', $sum)->assign('checkSum', $checkSum)->assign('uncheckSum', $uncheckSum);
		//搜索
		$search = I('get.search');
		if (!empty($search)) {
			$this->assign("search", $search);

			//状态
			$status = I('get.status', 99, 'intval');
            $where['status'] = array("EQ", $status);

            $filter = I('get._filter');
            $operator = I('get._operator');
            $value = I('get._value');

            if (is_array($filter)) {
                foreach ($filter as $index => $k){
                    if( $value[$index] != '' ){
                        $filter[$index] = trim($filter[$index]);
                        $operator[$index] = trim($operator[$index]);
                        $value[$index] = trim($value[$index]);

                        if(empty($where[$filter[$index]])){
                            $where[$filter[$index]] = [];
                        }
                        if(strtolower($operator[$index]) == 'like'){
                            $condition = array($operator[$index], '%' . $value[$index] . '%');
                        }else{
                            $condition = array($operator[$index], $value[$index]);
                        }
                        $where[$filter[$index]][] = $condition;
                    }
                }
                $this->assign('_filter', $filter);
                $this->assign('_operator', $operator);
                $this->assign('_value', $value);
            }
		}
		//信息总数
		if (empty($search)) {
			$count = $sum;
		} else {
			$count = $model->where($where)->count();
		}
		$page = $this->page($count, 20);
		$data = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array("id" => "DESC"))->select();

		//模板处理
		$template = '';

		//查看模型上有没有设计后台模板
		$model_info=D("Model")->find($modelid);
		if($model_info['list_customtemplate']){
			$template="Listtemplate/".$model_info['list_customtemplate'];
		}
		//自定义列表
		if (!empty($setting['list_customtemplate'])) {
			$template = "Listtemplate:{$setting['list_customtemplate']}";
		}
		$this->assign($catInfo)
			->assign('Page', $page->show())
			->assign('catid', $this->catid)
			->assign('count', $count)
			->assign('data', $data);
		$this->display($template);
	}

	//添加信息
	public function add() {
		if (IS_POST) {
			//栏目ID
			$catid = intval($_POST['info']['catid']);
			if (empty($catid)) {
				$this->error("请指定栏目ID！");
			}
//			标题可以为空
//			if (trim($_POST['info']['title']) == '') {
//				$this->error("标题不能为空！");
//			}
			//获取当前栏目配置
			$category = getCategory($catid);
			//栏目类型为0
			if ($category['type'] == 0) {
				//模型ID
				$modelid = getCategory($catid, 'modelid');
				//检查模型是否被禁用
				if ($this->model[$modelid]['disabled'] == 1) {
					$this->error("模型被禁用！");
				}
				$status = $this->Content->data($_POST['info'])->add();
				if ($status) {
					$this->success("添加成功！");
				} else {
					$error = $this->Content->getError();
					$this->error($error ? $error : '添加失败！');
				}
			} else if ($category['type'] == 1) {
                //单页栏目
				$db = D('Content/Page');
				if ($db->savePage($_POST)) {
					//扩展字段处理
					if ($_POST['extend']) {
						D('Content/Category')->extendField($catid, $_POST);
					}
					$this->Html->category($catid);
					$this->success('操作成功！');
				} else {
					$error = $db->getError();
					$this->error($error ? $error : '操作失败！');
				}
			} else {
				$this->error("该栏目类型无法发布！");
			}
		} else {
			//取得对应模型
			$category = getCategory($this->catid);
			if (empty($category)) {
				$this->error('该栏目不存在！');
			}
			//判断是否终极栏目
			if ($category['child']) {
				$this->error('只有终极栏目可以发布文章！');
			}
			if ($category['type'] == 0) {
				//模型ID
				$modelid = $category['modelid'];
				//检查模型是否被禁用
				if (getModel($modelid, 'disabled') == 1) {
					$this->error('该模型已被禁用！');
				}
				//实例化表单类 传入 模型ID 栏目ID 栏目数组
				$content_form = new \content_form($modelid, $this->catid);
				//生成对应字段的输入表单
				$forminfos = $content_form->get();
				//生成对应的JS验证规则
				$formValidateRules = $content_form->formValidateRules;
				//js验证不通过提示语
				$formValidateMessages = $content_form->formValidateMessages;
				//js
				$formJavascript = $content_form->formJavascript;
				//取得当前栏目setting配置信息
				$setting = $category['setting'];

				$this->assign("catid", $this->catid);
				$this->assign("content_form", $content_form);
				$this->assign("forminfos", $forminfos);
				$this->assign("formValidateRules", $formValidateRules);
				$this->assign("formValidateMessages", $formValidateMessages);
				$this->assign("formJavascript", $formJavascript);
				$this->assign("setting", $setting);
				$this->assign("category", $category);
                //获取模板
                $tpl = getAdminTemplate($this->catid, 'add');
                if(empty($tpl)){
                    $this->display();
                }else{
                    $this->display('Addtemplate/' . $tpl);
                }
			} else if ($category['type'] == 1) {
                //单网页模型
				$info = D('Content/Page')->getPage($this->catid);
				if ($info && $info['style']) {
					$style = explode(';', $info['style']);
					$info['style_color'] = $style[0];
					if ($style[1]) {
						$info['style_font_weight'] = $style[1];
					}
				}
                $setting = $category['setting'];
				$extend = $category['setting']['extend'];

				$this->assign("catid", $this->catid);
				$this->assign("setting", $setting);
				$this->assign('extend', $extend);
				$this->assign('info', $info);
				$this->assign("category", $category);
				//栏目扩展字段
				$this->assign('extendList', D("Content/Category")->getExtendField($this->catid));
				$this->display('singlepage');
			}
		}
	}

	//编辑信息
	public function edit() {
		$this->catid = (int) $_POST['info']['catid'] ?: $this->catid;
		//信息ID
		$id = I('request.id', 0, 'intval');
		$Categorys = getCategory($this->catid);
		if (empty($Categorys)) {
			$this->error("该栏目不存在！");
		}
		//栏目setting配置
		$cat_setting = $Categorys['setting'];
		//模型ID
		$modelid = $Categorys['modelid'];
		//检查模型是否被禁用
		if ($this->model[$Categorys['modelid']]['disabled'] == 1) {
			$this->error("模型被禁用！");
		}
		$model = ContentModel::getInstance($modelid);
		//检查是否锁定
		if (false === $model->locking($this->catid, $id)) {
			$this->error($model->getError());
		}
		if (IS_POST) {
			// 标题可以为空
			// if (trim($_POST['info']['title']) == '') {
			// 	$this->error("标题不能为空！");
			// }
			$id = $id ?: $_POST['info']['id'];
			//取得原有文章信息
			$data = $model->where(array("id" => $id))->find();
			//如果有自定义文件名，需要删除原来生成的静态文件
			if ($_POST['info']['prefix'] != $data['prefix'] && $cat_setting['content_ishtml']) {
				//删除原来的生成的静态页面
				$this->Content->data($data)->deleteHtml();
			}
			$status = $this->Content->data($_POST['info'])->edit();
			if ($status) {
				//解除信息锁定
				M("Locking")->where(array("userid" => User::getInstance()->id, "catid" => $catid, "id" => $id))->delete();
				$this->success("修改成功！");
			} else {
				$this->error($this->Content->getError());
			}
		} else {
			//取得数据，这里使用关联查询
			$data = $model->relation(true)->where(array("id" => $id))->find();
			if (empty($data)) {
				$this->error("该信息不存在！");
			}
			$model->dataMerger($data);
			//锁定信息
			M("Locking")->add(
				array(
					"userid" => User::getInstance()->id,
					"username" => User::getInstance()->username,
					"catid" => $this->catid,
					"id" => $id,
					"locktime" => time(),
				)
			);
			//引入输入表单处理类
			$content_form = new \content_form($modelid, $this->catid);
			//字段内容
			$forminfos = $content_form->get($data);
			//生成对应的JS验证规则
			$formValidateRules = $content_form->formValidateRules;
			//js验证不通过提示语
			$formValidateMessages = $content_form->formValidateMessages;
			//js
			$formJavascript = $content_form->formJavascript;
			$this->assign("category", $Categorys);
			$this->assign("data", $data);
			$this->assign("catid", $this->catid);
			$this->assign("id", $id);
			$this->assign("content_form", $content_form);
			$this->assign("forminfos", $forminfos);
			$this->assign("formValidateRules", $formValidateRules);
			$this->assign("formValidateMessages", $formValidateMessages);
			$this->assign("formJavascript", $formJavascript);
            //获取模板
            $tpl = getAdminTemplate($this->catid, 'edit');
            if(empty($tpl)){
                $this->display();
            }else{
                $this->display('Edittemplate/' . $tpl);
            }
		}
	}

	//删除
	public function delete() {
		if (IS_POST) {
			$this->catid = I('get.catid', 0, 'intval');
			$Categorys = getCategory($this->catid);
			if (empty($Categorys)) {
				$this->error("该栏目不存在！");
			}
			//模型ID
			$modelid = $Categorys['modelid'];
			if (empty($_POST['ids'])) {
				$this->error('没有信息被选中！');
			}
			$model = ContentModel::getInstance($modelid);
			foreach ($_POST['ids'] as $id) {
				//检查是否锁定
				if (false === $model->locking($this->catid, $id)) {
					$this->error($model->getError());
				}
				$this->Content->delete($id, $this->catid);
			}
			$this->success('删除成功！');
		} else {
			$this->catid = I('get.catid', 0, 'intval');
			$id = I('get.id', 0, 'intval');
			$Categorys = getCategory($this->catid);
			if (empty($Categorys)) {
				$this->error('该栏目不存在！');
			}
			//模型ID
			$modelid = $Categorys['modelid'];
			$model = ContentModel::getInstance($modelid);
			//检查是否锁定
			if (false === $model->locking($this->catid, $id)) {
				$this->error($model->getError());
			}
			if ($this->Content->delete($id, $this->catid)) {
				$this->success('删除成功！');
			} else {
				$this->error('删除失败！');
			}
		}
	}

	//文章审核
	public function public_check() {
		if (IS_POST) {
			$ids = $_POST['ids'];
			if (!$ids) {
				$this->error('没有信息被选中！');
			}
			foreach ($ids as $id) {
				$this->Content->check($this->catid, $id, 99);
			}
			$this->success('审核成功！');
		} else {
			$id = I('get.id', 0, 'intval');
			if (!$id) {
				$this->error('没有信息被选中！');
			}
			if ($this->Content->check($this->catid, $id, 99)) {
				$this->success('审核成功！');
			} else {
				$this->error('审核失败！');
			}
		}
	}

	//取消审核
	public function public_nocheck() {
		if (IS_POST) {
			$ids = $_POST['ids'];
			if (!$ids) {
				$this->error('没有信息被选中！');
			}
			foreach ($ids as $id) {
				$this->Content->check($this->catid, $id, 1);
			}
			$this->success('取消审核成功！');
		} else {
			$id = I('get.id', 0, 'intval');
			if (!$id) {
				$this->error('没有信息被选中！');
			}
			if ($this->Content->check($this->catid, $id, 1)) {
				$this->success('取消审核成功！');
			} else {
				$this->error('取消审核失败！');
			}
		}
	}

	//排序
	public function listorder() {
		$listorders = $_POST['listorders'];
		if (is_array($listorders)) {
			$modelid = getCategory($this->catid, 'modelid');
			if (empty($modelid)) {
				$this->error('模型不存在！');
			}
			$db = ContentModel::getInstance($modelid);
			foreach ($listorders as $id => $v) {
				$db->where(array('id' => $id))->save(array('listorder' => $v));
			}
			$this->success('更新成功！', U('classlist', array('catid' => $this->catid)));
		} else {
			$this->error('参数错误！');
		}
	}

	//检测标题是否存在
	public function public_check_title() {
		$title = I('get.data');
		$catid = $this->catid;
		if (empty($title)) {
			$this->ajaxReturn(array('status' => 1, 'info' => '标题没有重复！'));
			return false;
		}
		$count = ContentModel::getInstance(getCategory($catid, 'modelid'))->where(array('title' => $title))->count();
		if ($count > 0) {
			$this->ajaxReturn(array('status' => 0, 'info' => '标题有重复！'));
		} else {
			$this->ajaxReturn(array('status' => 1, 'info' => '标题没有重复！'));
		}
	}

	//相关文章选择
	public function public_relationlist() {
		if (IS_POST) {
			$modelid = getCategory($this->catid, 'modelid');
			$_POST['modelid'] = $modelid;
			$this->redirect('public_relationlist', $_POST);
		}
		$modelid = I('get.modelid', 0, 'intval');
		if (empty($modelid)) {
			$this->error('缺少参数！');
		} else {
			$modelid = I('get.modelid', 0, 'intval');
			$model = ContentModel::getInstance($modelid);
			$where = array();
			$catid = $this->catid;
			if ($catid) {
				$where['catid'] = $catid;
			}
			$where['status'] = 99;
			if (isset($_GET['keywords'])) {
				$keywords = trim($_GET['keywords']);
				$field = $_GET['searchtype'];
				if (in_array($field, array('id', 'title', 'keywords', 'description'))) {
					if ($field == 'id') {
						$where['id'] = array('eq', $keywords);
					} else {
						$where[$field] = array('like', '%' . $keywords . '%');
					}
				}
			}
			$count = $model->where($where)->count();
			$page = $this->page($count, 12);
			$data = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order(array('id' => "DESC"))->select();
			$this->assign('Formcategory', \Form::select_category($catid, 'name="catid"', "不限栏目", 0, 0, 1));
			$this->assign('data', $data);
			$this->assign('Page', $page->show());
			$this->assign('modelid', $modelid);
			$this->display('relationlist');
		}
	}

	//加载相关文章列表
	public function public_getjson_ids() {
		$modelid = I('get.modelid', 0, 'intval');
		$id = I('get.id', 0, 'intval');
		$model = ContentModel::getInstance($modelid);
		if (empty($model)) {
			return false;
		}
		$r = $model->relation(true)->where(array('id' => $id))->find();
		$model->dataMerger($r);
		$where = array();
		if ($r['relation']) {
			if (strpos($r['relation'], ',')) {
				$relations = explode('|', $r['relation']);
				$newRela = array();
				foreach ($relations as $rs) {
					if (strpos($rs, ',')) {
						$rs = explode(',', $rs);
					} else {
						$rs = array($modelid, $rs);
					}
					$newRela[$rs[0]][] = $rs[1];
				}
				$datas = array();
				foreach ($newRela as $modelid => $catidList) {
					$where['id'] = array('IN', $catidList);
					$_list = \Content\Model\ContentModel::getInstance($modelid)->where($where)->select();
					if (!empty($_list)) {
						$datas = array_merge($datas, $_list);
					}
				}
			} else {
				$relation = str_replace('|', ',', $r['relation']);
				$where['id'] = array("in", $relation);
				$datas = $model->where($where)->select();
			}
			foreach ($datas as $_v) {
				$_v['sid'] = 'v' . getCategory($_v['catid'], 'modelid') . '_' . $_v['id'];
				$infos[] = $_v;
			}
		}
		$this->ajaxReturn(array('data' => $infos, 'status' => 1));
	}

	//文章预览
	public function public_preview() {
		$id = I('get.id', 0, 'intval');
		$catid = I('get.catid', 0, 'intval');
		$page = intval($_GET[C("VAR_PAGE")]);
		$page = max($page, 1);
		//获取当前栏目数据
		$category = getCategory($catid);
		if (empty($category)) {
			$this->error('该栏目不存在！');
		}
		//反序列化栏目配置
		$category['setting'] = $category['setting'];
		//模型ID
		$modelid = $category['modelid'];
		$data = ContentModel::getInstance($modelid)->relation(true)->where(array("id" => $id))->find();
		if (empty($data)) {
			$this->error('该信息不存在！');
		}
		ContentModel::getInstance($modelid)->dataMerger($data);
		//分页方式
		if (isset($data['paginationtype'])) {
			//分页方式
			$paginationtype = $data['paginationtype'];
			//自动分页字符数
			$maxcharperpage = (int) $data['maxcharperpage'];
		} else {
			//默认不分页
			$paginationtype = 0;
		}
		$content_output = new \content_output($modelid);
		//获取字段类型处理以后的数据
		$output_data = $content_output->get($data);
		$output_data['id'] = $id;
		$output_data['title'] = strip_tags($output_data['title']);
		//SEO
		$seo_keywords = '';
		if (!empty($output_data['keywords'])) {
			$seo_keywords = implode(',', $output_data['keywords']);
		}
		$seo = seo($catid, $output_data['title'], $output_data['description'], $seo_keywords);

		//内容页模板
		//获取栏目的模型
        $model=M('Model')->find($category['modelid']);
        $template = $output_data['template'] ? $output_data['template'] : $category['setting']['show_template'];
        //如果模型有设置，则template就是模型设置的模板
        $model['show_template'] ? $template = $model['show_template']:'';
        //栏目中有设置，则使用栏目的模板
        $category['setting']['show_template'] ? $template = $category['setting']['show_template']:'';
        //内容页模板有设置，则使用内容模板
        $output_data['template'] ? $template = $output_data['template'] : '';
        //如果都没有设置，则默认使用show.php
        $template ? '' : $template='show.php';
		//去除模板文件后缀
		$newstempid = explode(".", $template);
		$template = $newstempid[0];
		//分页处理
		$pages = $titles = '';
		//分配解析后的文章数据到模板
		$this->assign($output_data);
		//seo分配到模板
		$this->assign("SEO", $seo);
		//栏目ID
		$this->assign("catid", $catid);
		//分页生成处理
		//分页方式 0不分页 1自动分页 2手动分页
		if ($data['paginationtype'] > 0) {
			//手动分页
			$CONTENT_POS = strpos($output_data['content'], '[page]');
			if ($CONTENT_POS !== false) {
				$contents = array_filter(explode('[page]', $output_data['content']));
				$pagenumber = count($contents);
				$pages = page(
					$pagenumber, 1, $page, array(
						'isrule' => true,
						'rule' => array(
							'index' => 'index.php?m=Content&a=public_preview&catid=' . $catid . '&id=' . $id,
							'list' => 'index.php?m=Content&a=public_preview&catid=' . $catid . '&id=' . $id . '&page={$page}',
						),
					)
				)->show("default");
				//判断[page]出现的位置是否在第一位
				if ($CONTENT_POS < 7) {
					$content = $contents[$page];
				} else {
					$content = $contents[$page - 1];
				}
				//分页
				$this->assign("pages", $pages);
				$this->assign("content", $content);
			}
		} else {
			$this->assign("content", $output_data['content']);
		}
		$this->display(parseTemplateFile("Show/{$template}"));
	}

	//图片裁减
	public function public_imagescrop() {
		$picurl = I('get.picurl');
		$catid = I('get.catid', $this->catid, 'intval');
		if (!$catid) {
			$this->error('栏目不存在！');
		}
		if ($picurl) {
			$picurl = str_replace(urlDomain($picurl), '/', $picurl);
		}
		$module = I('get.module', MODULE_NAME);
		$this->assign('picurl', $picurl);
		$this->assign('catid', $catid);
		$this->assign('module', $module);
		$this->display('imagescrop');
	}

	//批量移动文章
	public function remove() {
		if (IS_POST && isset($_POST['fromtype'])) {
			$catid = I('get.catid', '', 'intval');
			if (!$catid) {
				$this->error("请指定栏目！");
			}
			//移动类型
			$fromtype = I('post.fromtype', '', 'intval');
			//需要移动的信息ID集合
			$ids = $_POST['ids'];
			//需要移动的栏目ID集合
			$fromid = $_POST['fromid'];
			//目标栏目
			$tocatid = I('post.tocatid', '', 'intval');
			if (!$tocatid) {
				$this->error("目标栏目不正确！");
			}
			switch ($fromtype) {
				//信息移动
				case 0:
					if ($ids) {
						if ($tocatid == $catid) {
							$this->error('目标栏目和当前栏目是同一个栏目！');
						}
						$modelid = getCategory($tocatid, 'modelid');
						if (!$modelid) {
							$this->error('该模型不存在！');
						}
						$model = ContentModel::getInstance($modelid);
						if (!$ids) {
							$this->error('请选择需要移动信息！');
						}
						$ids = array_filter(explode('|', $_POST['ids']), 'intval');
						//删除静态文件
						foreach ($ids as $sid) {
							$data = $model->where(array('catid' => $catid, 'id' => $sid))->find();
							$this->Content->data($data)->deleteHtml();
							$data['catid'] = $tocatid;
							$urls = $this->Url->show($data);
							$model->where(array('catid' => $catid, 'id' => $sid))->save(array('catid' => $tocatid, 'url' => $urls['url']));
							//更新推荐位
							$db =M('PositionData'); 
							$db->where(array('id' => $sid))->save(array('catid' => $tocatid));
						}
						$this->success('移动成功！', U('Createhtml/update_urls'));
					} else {
						$this->error('请选择需要移动的信息！');
					}
					break;
				//栏目移动
				case 1:
					if (!$fromid) {
						$this->error('请选择需要移动的栏目！');
					}
					$where = array();
					$where['catid'] = array('IN', $fromid);
					$modelid = getCategory($catid, 'modelid');
					if (!$modelid) {
						$this->error("该模型不存在！");
					}
					$model = ContentModel::getInstance($modelid);
					//进行栏目id更改
					if ($model->where($where)->save(array('catid' => $tocatid, 'url' => ''))) {
						$this->success('移动成功，请使用《批量更新URL》更新新的地址！！', U('Createhtml/update_urls'));
					} else {
						$this->error('移动失败！');
					}
					break;
				default:
					$this->error('请选择移动类型！');
					break;
			}
		} else {
			$ids = I('request.ids', '', '');
			$ids = is_array($ids) ? implode("|", $ids) : $ids;
			$catid = I('get.catid', '', 'intval');
			if (!$catid) {
				$this->error("请指定栏目！");
			}
			$modelid = getCategory($catid, 'modelid');
			$tree = new \Tree();
			$tree->icon = array('&nbsp;&nbsp;│ ', '&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;';
			$categorys = array();
			$categorysList = cache('Category');
			foreach ($categorysList as $cid => $r) {
				$r = getCategory($r['catid']);
				if ($r['type']) {
					continue;
				}

				if ($modelid && $modelid != $r['modelid'] && $r['child'] == 0) {
					continue;
				}

				$r['disabled'] = $r['child'] ? 'disabled' : '';
				$r['selected'] = $cid == $catid ? 'selected' : '';
				$categorys[$cid] = $r;
			}
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$tree->init($categorys);
			$string .= $tree->get_tree(0, $str);

			$str = "<option value='\$catid'>\$spacer \$catname</option>";
			$source_string = '';
			$tree->init($categorys);
			$source_string .= $tree->get_tree(0, $str);

			$this->assign("ids", $ids);
			$this->assign("string", $string);
			$this->assign("source_string", $source_string);
			$this->assign("catid", $catid);
			$this->display();
		}
	}

	//显示栏目列表，树状
	public function public_getsite_categorys() {
		$catid = $this->catid;
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$categorys = array();
		if (User::getInstance()->isAdministrator() !== true) {
			$this->priv_db = M('CategoryPriv');
			$priv_result = $this->priv_db->where(array('action' => 'add', 'roleid' => User::getInstance()->role_id, 'is_admin' => 1))->select();
			$priv_catids = array();
			foreach ($priv_result as $_v) {
				$priv_catids[] = $_v['catid'];
			}
			if (empty($priv_catids)) {
				return '';
			}

		}
		$categorysList = cache('Category');
		foreach ($categorysList as $r) {
			$r = getCategory($r['catid']);
			if ($r['type'] != 0) {
				continue;
			}

			if (User::getInstance()->role_id != 1 && !in_array($r['catid'], $priv_catids)) {
				$arrchildid = explode(',', $r['arrchildid']);
				$array_intersect = array_intersect($priv_catids, $arrchildid);
				if (empty($array_intersect)) {
					continue;
				}

			}
			$r['modelname'] = $this->model[$r['modelid']]['name'];
			$r['style'] = $r['child'] ? 'color:#8A8A8A;' : '';
			$r['click'] = $r['child'] ? '' : " id=\"cv" . $r['catid'] . "\" onclick=\"select_list(this,'" . \Input::forTag($r['catname']) . "'," . $r['catid'] . ")\" class='cu' title='" . \Input::forTag($r['catname']) . "'";
			$categorys[$r['catid']] = $r;
		}
		$str = "<tr \$click >
					<td align='center'>\$id</td>
					<td style='\$style'>\$spacer\$catname</td>
					<td align='center'>\$modelname</td>
				</tr>";
		$tree->init($categorys);
		$categorys = $tree->get_tree(0, $str);
		exit($categorys);
	}

	//文章推送
	public function push() {
		if (IS_POST) {
			$id = I('post.id');
			$modelid = I('post.modelid');
			$catid = I('post.catid');
			$action = I('get.action');
			if (!$id || !$action || !$modelid || !$catid) {
				$this->error('参数不正确');
			}
			switch ($action) {
				//推荐位
				case "position_list":
					$posid = $_POST['posid'];
					if ($posid && is_array($posid)) {
						$position_data_db = D('Content/Position');
						$ModelField = cache('ModelField');
						$fields = $ModelField[$modelid];
						$ids = explode('|', $id);
						$model = ContentModel::getInstance($modelid);
						foreach ($ids as $k => $aid) {
							//取得信息
							$re = $model->relation(true)->where(array('id' => $aid))->find();
							if ($re) {
								$model->dataMerger($re);
								//推送数据
								$textcontent = array();
								foreach ($fields AS $_value) {
									$field = $_value['field'];
									//判断字段是否入库到推荐位字段
									if ($_value['isposition']) {
										$textcontent[$field] = $re[$field];
									}
								}
								//样式进行特别处理
								$textcontent['style'] = $re['style'];
								//推送到推荐位
								$position_data_db->positionUpdate($aid, $modelid, $catid, $posid, $textcontent, 0, 1);
								$r = $re = null;
							}
						}
						$this->success('推送到推荐位成功！');
					} else {
						$this->error('请选择推荐位！');
					}
					break;
				//同步发布到其他栏目
				case 'push_to_category':
					$ids = explode('|', $id);
					$relation = I('post.relation');
					if (!$relation) {
						$this->error('请选择需要推送的栏目！');
					}
					$relation = explode('|', $relation);
					if (is_array($relation)) {
						//过滤相同栏目和自身栏目
						foreach ($relation as $k => $classid) {
							if ($classid == $catid) {
								$this->error('推送的栏目不能是当前栏目！');
							}
						}
						//去除重复
						$relation = array_unique($relation);
						if (count($relation) < 1) {
							$this->error('请选择需要推送的栏目！');
						}
						$model = ContentModel::getInstance($modelid);
						foreach ($ids as $k => $aid) {
							//取得信息
							$r = $model->relation(true)->where(array('id' => $aid))->find();
							$linkurl = $r['url'];
							if ($r) {
								$this->Content->othor_catid($relation, $linkurl, $r, $modelid);
							}
						}
						$this->success('推送其他栏目成功！');
					} else {
						$this->error('请选择需要推送的栏目！');
					}
					break;
				default:
					$this->error('请选择操作！');
					break;
			}
		} else {
			$id = I('get.id');
			$action = I('get.action');
			$modelid = I('get.modelid');
			$catid = I("get.catid");
			if (!$id || !$action || !$modelid || !$catid) {
				$this->error('参数不正确！');
			}
			$tpl = $action == 'position_list' ? 'push_list' : 'push_to_category';

			switch ($action) {
				//推荐位
				case 'position_list':
					$position = cache('Position');
					if (!empty($position)) {
						$array = array();
						foreach ($position as $_key => $_value) {
							//如果有设置模型，检查是否有该模型
							if ($_value['modelid'] && !in_array($modelid, explode(',', $_value['modelid']))) {
								continue;
							}
							//如果设置了模型，又设置了栏目
							if ($_value['modelid'] && $_value['catid'] && !in_array($catid, explode(',', $_value['catid']))) {
								continue;
							}
							//如果设置了栏目
							if ($_value['catid'] && !in_array($catid, explode(',', $_value['catid']))) {
								continue;
							}
							$array[$_key] = $_value['name'];
						}
						$this->assign('Position', $array);
					}
					break;
				//同步发布到其他栏目
				case 'push_to_category':
					break;
				default:
					$this->error('请选择操作！');
					break;
			}

			$this->assign('id', $id)
				->assign('action', $action)
				->assign('modelid', $modelid)
				->assign('catid', $catid)
				->assign('show_header', true)
				->display($tpl);
		}
	}

	//同时发布到其他栏目选择页面
	public function public_othors() {
		$catid = I('get.catid', 0, 'intval');
		$this->assign('catid', $catid)
			->display('add_othors');
	}

	//锁定时间续期
	public function public_lock_renewal() {
		$catid = I('get.catid', 0, 'intval');
		$id = I('get.id', 0, 'intval');
		$userid = User::getInstance()->id;
		$time = time();
		if ($catid && $id && $userid) {
			M('Locking')->where(array('id' => $id, 'catid' => $catid, 'userid' => $userid))->save(array('locktime' => $time));
		}
	}

}
