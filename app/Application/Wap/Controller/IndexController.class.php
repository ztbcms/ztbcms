<?php

// +----------------------------------------------------------------------
// | 3G手机版
// +----------------------------------------------------------------------

namespace Wap\Controller;

use Common\Controller\Base;

class IndexController extends Base {

	//3G首页
	public function index() {
		$SEO = seo('', '', self::$Cache['Config']['siteinfo'], self::$Cache['Config']['sitekeywords']);
		$this->assign("SEO", $SEO);
		$template = "index";
		$this->display($template);
	}

	//3G列表
	public function lists() {
		$catid = I('get.catid', 0, 'intval');
		$page = isset($_GET[C("VAR_PAGE")]) ? $_GET[C("VAR_PAGE")] : 1;
		$category = getCategory($catid);
		if (empty($category)) {
			send_http_status(404);
			exit;
		}
		$setting = $category['setting'];
		$urls = "index.php?g=Wap&a=lists&catid={$catid}~index.php?g=Wap&a=lists&catid={$catid}&page=*";
		//栏目
		if ($category['type'] == 0) {
           //获取栏目的模型 
            $model=M('Model')->find($category['modelid']);
            //栏目首页模板
            $template = $setting['category_template'] ? $setting['category_template'] : ($model['category_template'] ? $model['category_template']:'category');
            //栏目列表页模板
            $template_list = $setting['list_template'] ? $setting['list_template'] : ($model['list_template'] ? $model['list_template']:'list');
			//判断使用模板类型，如果有子栏目使用频道页模板，终极栏目使用的是列表模板
			$template = $category['child'] ? "Category/{$template}" : "List/{$template_list}";
			//去除后缀开始
			$tpar = explode(".", $template, 2);
			//去除完后缀的模板
			$template = $tpar[0];
			unset($tpar);
			$GLOBALS['URLRULE'] = $urls;
		} else if ($category['type'] == 1) {
//单页
			$db = D('Content/Page');
			$template = $setting['wappage_template'] ?: 'page';
			//判断使用模板类型，如果有子栏目使用频道页模板，终极栏目使用的是列表模板
			$template = "Page/{$template}";
			//去除后缀开始
			$tpar = explode(".", $template, 2);
			//去除完后缀的模板
			$template = $tpar[0];
			unset($tpar);
			$GLOBALS['URLRULE'] = $urls;
			$info = $db->getPage($catid);
			$this->assign($category['setting']['extend']);
			$this->assign($info);
		}
		//把分页分配到模板
		$this->assign(C("VAR_PAGE"), $page);
		$this->assign($category);
		$seo = seo($catid, $setting['meta_title'], $setting['meta_description'], $setting['meta_keywords']);
		$this->assign("SEO", $seo);
		$this->display($template);
	}

	//内容页
	public function shows() {
		$catid = I('get.catid', 0, 'intval');
		$id = I('get.id', 0, 'intval');
		$page = max(intval($_GET[C("VAR_PAGE")]), 1);
		//获取当前栏目数据
		$category = getCategory($catid);
		if (empty($category)) {
			send_http_status(404);
			exit;
		}
		//反序列化栏目配置
		$category['setting'] = $category['setting'];
		//模型ID
		$modelid = (int) getCategory($catid, 'modelid');
		if (empty($modelid)) {
			exit;
		}
		$data = \Content\Model\ContentModel::getInstance($modelid)->relation(true)->where(array("id" => $id, 'status' => 99))->find();
		if (empty($data)) {
			send_http_status(404);
			exit;
		}
		\Content\Model\ContentModel::getInstance($modelid)->dataMerger($data);
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
		unset($newstempid);
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
			$urlrules = "index.php?g=Wap&a=shows&catid={$catid}&id={$id}";
			$fenye = array(
				"index" => $urlrules,
				"list" => $urlrules . '&page=*',
			);
			//手动分页
			$CONTENT_POS = strpos($output_data['content'], '[page]');
			if ($CONTENT_POS !== false) {
				$contents = array_filter(explode('[page]', $output_data['content']));
				$pagenumber = count($contents);
				$pages = page($pagenumber, 1, $page, array(
					'isrule' => true,
					'rule' => $fenye,
				))->show("default");
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
		$this->display("Show/{$template}");
	}

}
