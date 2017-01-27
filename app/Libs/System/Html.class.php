<?php

// +----------------------------------------------------------------------
// | 生成静态页面
// +----------------------------------------------------------------------

namespace Libs\System;

use Common\Controller\Base;
use Content\Model\ContentModel;

class Html extends Base {

	//数据
	protected $data = array();
	//错误信息
	protected $error = NULL;

	//初始化
	protected function _initialize() {
		define('APP_SUB_DOMAIN_NO', 1);
		parent::_initialize();
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
	 * @return Html
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
			E('数据类型错误！');
		}
		$this->data = $data;
		return $this;
	}

	/**
	 * 生成内容页
	 * @param array $data 数据
	 * @param int $array_merge 是否合并
     * @return boolean
	 */
	public function show($data = '', $array_merge = 1) {
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else {
				$this->error = '没有数据';
				return false;
			}
		}
		//初始化一些模板分配变量
		$this->assignInitialize();
		//取得信息ID
		$id = $data['id'];
		//栏目ID
		$catid = $data['catid'];
		//获取当前栏目数据
		$category = getCategory($catid);
		//扩展配置
		$setting = $category['setting'];
		//是否生成静态
		if (empty($setting['content_ishtml'])) {
			return false;
		}
		//模型ID
		$this->modelid = $category['modelid'];
		//检查数据是否合并
		if (!$array_merge) {
			ContentModel::getInstance($this->modelid)->dataMerger($data);
		}
		//原始数据
		$this->assign('_original', $data);
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
		//tag
		tag('html_shwo_buildhtml', $data);
		$content_output = new \content_output($this->modelid);
		//获取字段类型处理以后的数据
		$output_data = $content_output->get($data);
		//SEO
		$seo_keywords = '';
		if (!empty($output_data['keywords'])) {
			$seo_keywords = implode(',', $output_data['keywords']);
		}
		$seo = seo($catid, $output_data['title'], $output_data['description'], $seo_keywords);
		//内容页模板
		$template = $output_data['template'] ? $output_data['template'] : $category['setting']['show_template'];
		//去除模板文件后缀
		$newstempid = explode('.', $template);
		$template = "Show/{$newstempid[0]}";
		//获取模板文件地址
		$templatePath = parseTemplateFile($template);
		//分页处理
		$pages = $titles = '';
		//分配解析后的文章数据到模板
		$this->assign($output_data);
		//seo分配到模板
		$this->assign('SEO', $seo);
		//分页方式 0不分页 1自动分页 2手动分页
		if ($data['paginationtype'] > 0) {
			//分页表情出现次数
			$content_page_pos = strpos($output_data['content'], '[page]');
			if ($content_page_pos !== false) {
				$contents = array_filter(explode('[page]', $output_data['content']));
				$pagenumber = count($contents);
				for ($i = 1; $i <= $pagenumber; $i++) {
					//URL地址处理
					$urlrules = $this->generateShowUrl($data, $i);
					//用于分页导航
					if (!isset($pageurl['index'])) {
						$pageurl['index'] = $urlrules['page']['index'];
						$pageurl['list'] = $urlrules['page']['list'];
					}
					$pageurls[$i] = $urlrules;
				}
				$pages = '';
				//生成分页
				foreach ($pageurls as $page => $urls) {
					//$pagenumber 分页总数
					$_GET[C('VAR_PAGE')] = $page;
					$pages = page($pagenumber, 1, $page, array('isrule' => true, 'rule' => $pageurl))->show();
					$content = $contents[$page - 1];
					//分页
					$this->assign('pages', $pages);
					$this->assign('content', $content);
					$this->buildHtml($urls['path'], SITE_PATH, $templatePath);
				}
				return true;
			}
		}
		//对pages进行赋值null，解决由于上一篇有分页下一篇无分页的时候，会把上一篇的分页带到下一篇！
		$this->assign("pages", null);
		$this->assign("content", $output_data['content']);
		//当没有启用内容页分页时候（如果内容字段有启用分页，不会执行到此步骤），判断其他支持分页的标签进行分页处理
		unset($GLOBALS["Total_Pages"]);
		$page = 1;
		$j = 1;
		//开始生成列表
		do {
			$this->assign(C("VAR_PAGE"), $page);
			//生成路径
			$category_url = $this->generateShowUrl($data, $page);
			$GLOBALS['URLRULE'] = $category_url['page'];
			//生成
			$this->buildHtml($category_url["path"], SITE_PATH, $templatePath);
			$page++;
			$j++;
			$total_number = isset($_GET['total_number']) ? (int) $_GET['total_number'] : (int) $GLOBALS["Total_Pages"];
		} while ($j <= $total_number);
		return true;
	}

	/**
	 * 根据页码生成栏目
	 * @param string $catid 栏目id
     * @return boolean
	 */
	public function category($catid) {
		//获取栏目数据
		$category = getCategory($catid);
		if (empty($category)) {
			return false;
		}
		//栏目扩展配置信息
		$setting = $category['setting'];
		//检查是否生成列表
		if (!$category['sethtml']) {
			return true;
		}
		//初始化一些模板分配变量
		$this->assignInitialize();
		//父目录
		$parentdir = $category['parentdir'];
		//目录
		$catdir = $category['catdir'];
		//生成类型为0的栏目
		if ($category['type'] == 0) {
			//栏目首页模板
			$template = $setting['category_template'] ? $setting['category_template'] : 'category';
			//栏目列表页模板
			$template_list = $setting['list_template'] ? $setting['list_template'] : 'list';
			//判断使用模板类型，如果有子栏目使用频道页模板，终极栏目使用的是列表模板
			$template = $category['child'] ? "Category/{$template}" : "List/{$template_list}";
			//去除后缀开始
			$tpar = explode('.', $template, 2);
			//去除完后缀的模板
			$template = $tpar[0];
			//模板文件路径
			$template = parseTemplateFile($template);
		} else if ($category['type'] == 1) {
            //单页
			$db = D('Content/Page');
			$template = $setting['page_template'] ? $setting['page_template'] : 'page';
			//判断使用模板类型，如果有子栏目使用频道页模板，终极栏目使用的是列表模板
			$template = "Page/{$template}";
			//去除后缀开始
			$tpar = explode('.', $template, 2);
			//去除完后缀的模板
			$template = $tpar[0];
			//模板文件路径
			$template = parseTemplateFile($template);
			$info = $db->getPage($catid);
			$this->assign($category['setting']['extend']);
			$this->assign($info);
		}
		//分配变量到模板
		$this->assign($category);
		//seo分配到模板
		$seo = seo($catid, $setting['meta_title'], $setting['meta_description'], $setting['meta_keywords']);
		$this->assign('SEO', $seo);

		$page = 1;
		$j = 1;
		unset($GLOBALS["Total_Pages"]);
		do {
			$category_url = $this->generateCategoryUrl($catid, $page);
			$urls = $category_url['page'];
			$GLOBALS['URLRULE'] = $urls;
			//生成静态分页数
			$repagenum = (int) $setting['repagenum'];
			if ($repagenum && !$GLOBALS['dynamicRules']) {
				//设置动态访问规则给page分页使用
				$GLOBALS['Rule_Static_Size'] = $repagenum;
				$GLOBALS['dynamicRules'] = CONFIG_SITEURL_MODEL . "index.php?a=lists&catid={$catid}&page=*";
			}
			if ($repagenum && $page > $repagenum) {
				unset($GLOBALS['dynamicRules']);
				return true;
			}
			//把分页分配到模板
			$this->assign(C("VAR_PAGE"), $page);
			//生成
			$this->buildHtml($category_url["path"], SITE_PATH, $template);
			$page++;
			$j++;
			//如果GET有total_number参数则直接使用GET的，如果没有则根据$GLOBALS["Total_Pages"]获取分页总数
			$total_number = isset($_GET['total_number']) ? (int) $_GET['total_number'] : $GLOBALS["Total_Pages"];
		} while ($j <= $total_number);
	}

	/**
	 * 生成首页
	 * @return boolean
	 */
	public function index() {
		$config = cache('Config');
		if (empty($config['generate'])) {
			return false;
		}
		//初始化一些模板分配变量
		$this->assignInitialize();
		//模板处理
		$tp = explode('.', $config['indextp'], 2);
		$template = parseTemplateFile("Index/{$tp[0]}");
		$seo = seo('', '', $config['siteinfo'], $config['sitekeywords']);
		//seo分配到模板
		$this->assign('SEO', $seo);
		unset($GLOBALS["Total_Pages"]);
		$j = 1;
		$page = 1;
		//分页生成
		do {
			//把分页分配到模板
			$this->assign(C("VAR_PAGE"), $page);
			//生成路径
			$urls = $this->generateIndexUrl($page);
			$GLOBALS['URLRULE'] = $urls['page'];
			$filename = $urls['path'];
			//判断是否生成和入口文件同名，如果是，不生成！
			if ($filename != '/index.php') {
				$this->buildHtml($filename, SITE_PATH, $template);
			}
			//如果GET有total_number参数则直接使用GET的，如果没有则根据$GLOBALS["Total_Pages"]获取分页总数
			$total_number = isset($_GET['total_number']) ? (int) $_GET['total_number'] : $GLOBALS["Total_Pages"];
			$page++;
			$j++;
		} while ($j <= $total_number);
	}

	/**
	 * 生成父栏目列表
	 * @param string $catid
     * @return boolean
	 */
	public function createRelationHtml($catid) {
		if (empty($catid)) {
			return false;
		}
		//检查当前栏目的父栏目，如果存在则生成
		$arrparentid = getCategory($catid, 'arrparentid');
		if ($arrparentid) {
			$arrparentid = explode(',', $arrparentid);
			foreach ($arrparentid as $cid) {
				if ($cid) {
					$this->category($cid);
				}
			}
		}
		return true;
	}

	/**
	 * 生成自定义页面
	 * @param array|string|int $data
	 * @return boolean
	 */
	public function createHtml($data = '') {
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else {
				$this->error = '没有数据';
				return false;
			}
		} else if (is_integer($data)) {
			$data = M('Customtemp')->where(array('tempid' => $data))->find();
			if (empty($data)) {
				$this->error = '没有数据';
				return false;
			}
		}
		//模板内容
		$temptext = $data['temptext'];
		if (empty($temptext)) {
			return true;
		}
		//初始化一些模板分配变量
		$this->assignInitialize();
		//生成文件名，包含后缀
		$filename = $data['tempname'];
		//生成路径
		$htmlpath = SITE_PATH . $data['temppath'] . $filename;
		// 页面缓存
		ob_start();
		ob_implicit_flush(0);
		parent::show($temptext);
		// 获取并清空缓存
		$content = ob_get_clean();
		//检查目录是否存在
		if (!is_dir(dirname($htmlpath))) {
			// 如果静态目录不存在 则创建
			mkdir(dirname($htmlpath), 0777, true);
		}
		//写入文件
		if (false === file_put_contents($htmlpath, $content)) {
			E("自定义页面生成失败：{$htmlpath}");
		}
		return true;
	}

	/**
	 * 生成自定义列表页面
	 * @param  $data
	 * @return boolean
	 */
	public function createListHtml($data = '') {
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				// 重置数据
				$this->data = array();
			} else {
				$this->error = '没有数据';
				return false;
			}
		} else if (is_integer($data)) {
			$data = D('Template/Customlist')->where(array('id' => $data))->find();
			if (empty($data)) {
				$this->error = '没有数据';
				return false;
			}
		}
		$model = D('Template/Customlist');
		//初始化一些模板分配变量
		$this->assignInitialize();
		//计算总数
		$countArray = $model->query($data['totalsql']);
		if (!empty($countArray)) {
			$count = $countArray[0]['total'];
		} else {
			return false;
		}
		//seo分配到模板
		$seo = seo(0, $data['title'], $data['description'], $data['keywords']);
		$this->assign("SEO", $seo);
		//分页总数
		$paging = ceil($count / $data['lencord']);
		$pagehao = 1;
		do {
			//生成路径
			$customlistUrl = $this->Url->createListUrl($data, $pagehao);
			if ($customlistUrl == false) {
				return false;
			}
			//取得URL规则
			$urls = $customlistUrl['page'];
			$page = page($count, $data['lencord'], $pagehao, array(
				'isrule' => true,
				'rule' => $urls,
			));
			$listData = $model->query($data['listsql'] . " LIMIT {$page->firstRow},{$page->listRows}");
			//把分页分配到模板
			$this->assign(C("VAR_PAGE"), $pagehao);
			$this->assign('listData', $listData);
			$this->assign("pages", $page->show());

			if (empty($data['listpath'])) {
				//生成路径
				$htmlpath = SITE_PATH . $customlistUrl["path"];
				// 页面缓存
				ob_start();
				ob_implicit_flush(0);
				//渲染模板
				parent::show($data['template']);
				// 获取并清空缓存
				$content = ob_get_clean();
				//检查目录是否存在
				if (!is_dir(dirname($htmlpath))) {
					// 如果静态目录不存在 则创建
					mkdir(dirname($htmlpath), 0777, true);
				}
				//写入文件
				if (false === file_put_contents($htmlpath, $content)) {
					throw_exception("自定义列表生成失败：" . $htmlpath);
				}
			} else {
				//去除完后缀的模板
				$template = TEMPLATE_PATH . (empty(self::$Cache["Config"]['theme']) ? "Default" : self::$Cache["Config"]['theme']) . "/Content/List/{$data['listpath']}";
				//模板检测
				$template = parseTemplateFile($template);
				//生成
				$this->buildHtml($customlistUrl['path'], SITE_PATH, $template);
			}

			$pagehao++;
		} while ($pagehao <= $paging);
		return true;
	}

	/**
	 * 获取首页页URL规则处理后的
	 * @param int|string $page 分页号
	 * @return string
	 */
	protected function generateIndexUrl($page = 1) {
		return $this->Url->index($page);
	}

	/**
	 * 获取内容页URL规则处理后的
	 * @param array $data 数据
	 * @param int $page 分页号
	 * @return string
	 */
	protected function generateShowUrl($data, $page = 1) {
		return $this->Url->show($data, $page);
	}

	/**
	 * 获取栏目页URL规则处理后的
	 * @param string $catid 栏目ID
	 * @param string $page 分页号
	 * @return string
	 */
	protected function generateCategoryUrl($catid, $page = 1) {
		return $this->Url->category_url($catid, $page);
	}

	/**
	 * 另类的销毁分配给模板的变量
	 * 防止生成不同类型的页面，造成参数乱窜！
	 */
	protected function assignInitialize() {
		//栏目ID
		$this->assign('catid', NULL);
		//分页号
		$this->assign(C('VAR_PAGE'), NULL);
		//seo分配到模板
		$this->assign('SEO', NULL);
		$this->assign('content', NULL);
		$this->assign('pages', NULL);
	}

}
