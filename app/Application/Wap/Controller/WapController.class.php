<?php

// +----------------------------------------------------------------------
// |  3G手机版管理
// +----------------------------------------------------------------------

namespace Wap\Controller;

use Content\Controller\CategoryController;

class WapController extends CategoryController {

	//模板文件夹
	private $filepath;
	//频道模板路径
	protected $tp_category;
	//列表页模板路径
	protected $tp_list;
	//内容页模板路径
	protected $tp_show;
	//单页模板路径
	protected $tp_page;

	//初始化
	protected function _initialize() {
		parent::_initialize();
		load('Content/iconvfunc');
		//取得当前内容模型模板存放目录
		$this->filepath = TEMPLATE_PATH . (empty(self::$Cache["Config"]['theme']) ? "Default" : self::$Cache["Config"]['theme']) . "/Wap/";
		//取得栏目频道模板列表
		$this->tp_category = str_replace($this->filepath . "Category/", '', glob($this->filepath . 'Category/category*'));
		//取得栏目列表模板列表
		$this->tp_list = str_replace($this->filepath . "List/", '', glob($this->filepath . 'List/list*'));
		//取得内容页模板列表
		$this->tp_show = str_replace($this->filepath . "Show/", '', glob($this->filepath . 'Show/show*'));
		//取得单页模板
		$this->tp_page = str_replace($this->filepath . "Page/", '', glob($this->filepath . 'Page/page*'));
	}

	//3G首页
	public function index() {
		if (IS_POST) {
			parent::index();
		}
		$models = cache('Model');
		$categorys = array();
		//栏目数据，可以设置为缓存的方式
		$result = cache('Category');
		$siteurl = parse_url(self::$Cache['Config']['siteurl']);
		$types = array(0 => '内部栏目', 1 => '<font color="blue">单网页</font>', 2 => '<font color="red">外部链接</font>');
		if (!empty($result)) {
			foreach ($result as $r) {
				$r = getCategory($r['catid']);
				$r['modelname'] = $models[$r['modelid']]['name'];
				$r['str_manage'] = '';
				if ($r['child']) {
					$r['yesadd'] = '';
				} else {
					$r['yesadd'] = 'blue';
				}

				$r['str_manage'] .= '<a href="' . U("Wap/edit", array("catid" => $r['catid'])) . '">修改</a> ';
				$r['typename'] = $types[$r['type']];

				if ($r['url']) {
					$list_url = "index.php?g=Wap&a=lists&catid=$r[catid]";
					$r['url'] = "<a href='$list_url' target='_blank'>访问</a>";
				} else {
					$r['url'] = "<a href='" . U("Wap/public_cache") . "'><font color='red'>更新缓存</font></a>";
				}
				$categorys[$r['catid']] = $r;
			}
		}
		$str = "<tr>
	<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
	<td align='center'><font color='\$yesadd'>\$id</font></td>
	<td >\$spacer\$catname\$display_icon</td>
	<td  align='center'>\$typename</td>
	<td>\$modelname</td>
	<td align='center'>\$url</td>
	<td align='center' >\$str_manage</td>
	</tr>";
		if (!empty($categorys) && is_array($categorys)) {
			$this->Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$this->Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$this->Tree->init($categorys);
			$categorydata = $this->Tree->get_tree(0, $str);
		} else {
			$categorydata = '';
		}
		$this->assign("categorys", $categorydata);
		$this->display();
	}

	//3G版编辑栏目
	public function edit() {
		if (IS_POST) {
			$catid = I("post.catid", "", "intval");
			if (empty($catid)) {
				$this->error('请选择需要修改的栏目！');
			}
			$post = I('post.', array(), '');
			$Category = D("Content/Category");
			$info = $Category->where(array('catid' => $catid))->find();
			//相关配置
			$info['setting'] = $setting = unserialize($info['setting']) ? unserialize($info['setting']) : array();
			//栏目首页模板
			$info['setting']['wapcategory_template'] = $post['setting']['wapcategory_template'];
			//栏目列表模板
			$info['setting']['waplist_template'] = $post['setting']['waplist_template'];
			//栏目内容页模板
			$info['setting']['wapshow_template'] = $post['setting']['wapshow_template'];
			//单文章模板
			$info['setting']['wappage_template'] = $post['setting']['wappage_template'];
			$info['setting'] = serialize($info['setting']);
			$info['url'] = "";
			//dump($info);exit;
			$status = $Category->where(array('catid' => $catid))->save($info);
			if ($status !== false) {
				//更新缓存
				cache('Category', NULL);
				getCategory($catid, '', true);
				$this->success("3G版栏目配置更新成功！记得点击右上角的清除缓存！", U("Wap/index"));
			} else {
				$error = $Category->getError();
				$this->error($error ? $error : '栏目修改失败！');
			}
		} else {
			$catid = I('get.catid', 0, 'intval');
			$array = cache("Category");
			foreach ($array as $k => $v) {
				$array[$k] = getCategory($v['catid']);
				if ($v['child'] == "0") {
					$array[$k]['disabled'] = "disabled";
				} else {
					$array[$k]['disabled'] = "";
				}
			}
			$data = getCategory($catid);
			$setting = $data['setting'];
			//输出可用模型
			$modelsdata = cache("Model");
			$models = array();
			foreach ($modelsdata as $v) {
				if ($v['disabled'] == 0 && $v['type'] == 0) {
					$models[] = $v;
				}
			}
			if (!empty($array) && is_array($array)) {
				$this->Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
				$this->Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
				$this->Tree->init($array);
				$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
				$categorydata = $this->Tree->get_tree(0, $str, $data['parentid']);
			} else {
				$categorydata = '';
			}

			$this->assign("tp_category", $this->tp_category);
			$this->assign("tp_list", $this->tp_list);
			$this->assign("tp_show", $this->tp_show);
			$this->assign("tp_comment", $this->tp_comment);
			$this->assign("tp_page", $this->tp_page);
			$this->assign("category", $categorydata);
			$this->assign("models", $models);
			$this->assign("data", $data);
			$this->assign("setting", $setting);

			if ($data['type'] == 1) {
//单页栏目
				$this->display("singlepage_edit");
			} else if ($data['type'] == 2) {
//外部栏目
				$this->display("wedit");
			} else {
				$this->display();
			}
		}
	}

	//更新栏目缓存并修复
	public function public_cache() {
		$db = D("Content/Category");
		//当前
		$number = I('get.number', 1, 'intval');
		//每次处理多少栏目
		$handlesum = 100;
		//计算栏目总数
		$count = I('get.count', $db->count(), 'intval');
		//需要处理几次
		$handlecount = ceil($count / $handlesum);
		if ($number > $handlecount) {
			cache('Category', NULL);
			$this->success("缓存更新成功！", U("Wap/index"));
			return true;
		}
		parent::public_cache();
	}

}
