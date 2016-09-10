<?php

// +----------------------------------------------------------------------
// | 内容模型地址处理
// +----------------------------------------------------------------------

namespace Libs\System;

class Url {

	//内容URL规则缓存
	private $urlrules;
	//数据
	protected $data = array();
	//错误信息
	protected $error = NULL;

	public function __construct() {
		//获取URL生成规则缓存
		$this->urlrules = cache('Urlrules');
	}

	/**
	 * 获取错误提示
	 * @return type
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 设置数据对象值
	 * @access public
	 * @param mixed $data 数据
	 * @return Model
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
	 * 首页链接
	 * @param type $page 页码
	 * @return array Array (
	 *    [url] => / 访问地址
	 *    [path] => /index.html 生存路径
	 *    [page] => Array (
	 *       [index] => /index.html
	 *       [list] => /index_{$page}.html
	 *    )
	 * )
	 */
	public function index($page = 1) {
		//网站配置
		$config = cache('Config');
		//取分页最大值
		$page = max($page, 1);
		//URL规则ID
		$index_ruleid = $config['index_urlruleid'];
		$urlrule = $this->urlrules[$index_ruleid]['urlrule'];
		//如果URL规则为空，为确保正常，默认一个动态URL规则
		if (!$urlrule) {
			$urlrule = 'index.php|index.php?page={$page}';
		}

		$urlrule = explode("|", $urlrule);
		$url = array(
			"url" => $config['siteurl'] . ($page > 1 ? $urlrule[1] : $urlrule[0]),
			"path" => "",
		);
		$url["url"] = str_replace('{$page}', $page, $url["url"]);
		$parse_url = parse_url($url['url']);
		$url['path'] = "/" . $parse_url['path'];
		$url["path"] = str_replace(array('\/', '//', '\\'), '/', $url["path"]);
		//用于分页使用
		$url['page'] = array(
			"index" => $config['siteurl'] . $urlrule[0],
			"list" => $config['siteurl'] . $urlrule[1],
		);

		//判断是否为首页文件，如果是，就不显示文件名，隐藏
		if (in_array(basename($url["url"]), array('index.html', 'index.htm', 'index.shtml', 'index.php'))) {
			$url["url"] = str_replace(array('\/', '//', '\\'), '/', dirname($url["url"]) . '/');
		}

		//把生成路径中的分页标签替换
		$url['path'] = str_replace('{$page}', $page, $url['path']);

		return $url;
	}

	/**
	 * 生成内容页相关地址
	 * @param type $data 文章数据
	 * @param type $page 当前分页号
	 * @return boolean
	 * Array
	 * (
	 *     [url] => http://news.abc.com/1970/web_01/2.html 访问路径
	 *     [path] => /record/1970/web_01/2.html 生成路径 动态木有
	 *     [page] => Array
	 *     (
	 *         [index] => http://news.abc.com/1970/web_01/2.html
	 *         [list] => http://news.abc.com/1970/web_01/2_{$page}.html
	 *     )
	 * )
	 */
	public function show($data = '', $page = 1) {
		static $_show = array();
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
		if (!$data['inputtime'] || !$data['id'] || !$data['catid']) {
			return false;
		}
		$guid = to_guid_string($data);
		//网站配置
		$config = cache("Config");
		//栏目id
		$catid = (int) $data['catid'];
		//信息id
		$id = (int) $data['id'];
		//自定义文件名
		$prefix = $data['prefix'];
		//取分页最大值
		$page = max(intval($page), 1);
		//真实发布时间
		if (is_numeric($data['inputtime']) == false) {
			$time = strtotime($data['inputtime']);
		} else {
			$time = (int) $data['inputtime'];
		}
		//当前栏目信息
		$category = getCategory($catid);
		//扩展配置
		$setting = $category['setting'];
		if (!isset($_show[$guid])) {
			//是否生成内容静态
			$content_ishtml = $setting['content_ishtml'];
			//内容规则ID
			$show_ruleid = $setting['show_ruleid'];
			//取得URL规则
			$urlrule = $this->urlrules[$show_ruleid]['urlrule'];
			if (empty($urlrule)) {
				return false;
			}
			//使用自定义函数生成规则
			if (substr($urlrule, 0, 1) == '=') {
				load("Content/urlrule");
				$fun = str_replace(substr($urlrule, 0, 1), "", $urlrule);
				$urlrule = call_user_func_array(trim($fun), array(
					"data" => $data,
					"page" => $page,
				));
			}
			$replace_l = array(); //需要替换的标签
			$replace_r = array(); //替换的内容
			//初始
			//父栏目目录
			if (strstr($urlrule, '{$categorydir}')) {
				//获取当前栏目父栏目路径
				$category_dir = $this->get_categorydir($catid);
				$replace_l[] = '{$categorydir}';
				$replace_r[] = $category_dir;
			}
			//栏目目录
			if (strstr($urlrule, '{$catdir}')) {
				$replace_l[] = '{$catdir}';
				$replace_r[] = $category['catdir'];
			}
			//栏目id
			if (strstr($urlrule, '{$catid}')) {
				$replace_l[] = '{$catid}';
				$replace_r[] = $catid;
			}
			//年份
			if (strstr($urlrule, '{$year}')) {
				$replace_l[] = '{$year}';
				$replace_r[] = date('Y', $time);
			}
			//月份
			if (strstr($urlrule, '{$month}')) {
				$replace_l[] = '{$month}';
				$replace_r[] = date('m', $time);
			}
			//日期
			if (strstr($urlrule, '{$day}')) {
				$replace_l[] = '{$day}';
				$replace_r[] = date('d', $time);
			}
			//文件名，如果有自定义文件名则使用自定义文件名，否则默认使用当前内容ID
			if ($content_ishtml && $prefix) {
				$fileName = trim($prefix);
			} else {
				$fileName = $id;
			}
			$replace_l[] = '{$id}';
			$replace_r[] = $fileName;
			//标签替换
			$urlrule = str_replace($replace_l, $replace_r, $urlrule);
			$_show[$guid] = $urlrule;
		} else {
			$urlrule = $_show[$guid];
		}
		//生成静态处理
		if ($setting['content_ishtml']) {
			//所有父ID
			$parentids = array();
			if ($category['arrparentid']) {
				$parentids = explode(',', $category['arrparentid']);
			}
			//把自身栏目id加入到父id数组中
			$parentids[] = $catid;
			$domain_dir = '';
			//循环查询父栏目是否设置了二级域名
			foreach ($parentids as $pid) {
				$r = getCategory($pid);
				if (!$r) {
					continue;
				}
				if ($r['domain'] && strpos($r['domain'], '?') === false) {
					$r['domain'] = preg_replace('/([(http|https):\/\/]{0,})([^\/]*)([\/]{1,})/i', '$1$2/', $r['domain'], -1); //取消掉双'/'情况
					//二级域名
					$domain = $r['domain'];
					//得到二级域名的目录
					$domain_dir = $this->get_categorydir($pid) . getCategory($pid, 'catdir') . '/';
				}
			}
		}
		//栏目绑定了域名，且需要生成静态
		if ($content_ishtml && $domain) {
			//检查是否保护绑定域名目录
			if (strpos($urlrule, $domain_dir) === false) {
				$urlrule = explode("|", $urlrule);
				//强制加上域名绑定目录
				foreach ($urlrule as $k => $url) {
					$urlrule[$k] = $domain_dir . $url;
				}
				$urlrule = implode("|", $urlrule);
			}
			$urlrule = str_replace(array($domain_dir, '\\'), array($domain, '/'), $urlrule);
		}
		$urlrule = explode("|", $urlrule);
		$url = array(
			"url" => ($page > 1 ? $urlrule[1] : $urlrule[0]),
			"path" => "",
		);
		//用于分页使用
		$url['page'] = array(
			"index" => $urlrule[0],
			"list" => $urlrule[1],
		);
		$url["url"] = str_replace('{$page}', $page, $url["url"]);
		//如果绑定域名，分析真实的生成目录
		$parse_url = parse_url($url['url']);
		if ($domain && $domain_dir) {
			$url['path'] = "/" . str_replace(array("//", "\\"), '/', $domain_dir . $parse_url['path']);
		} else {
			$url['path'] = "/" . str_replace(array("//", "\\"), '/', $parse_url['path']);
		}

		//判断是否为首页文件，如果是，就不显示文件名，隐藏
		if (in_array(basename($url["url"]), array('index.html', 'index.htm', 'index.shtml'))) {
			$url["url"] = dirname($url["url"]) . '/';
		}

		//判断是否有加域名
		if (!isset($parse_url['host'])) {
			$url['url'] = $config['siteurl'] . $url['url'];
			$url['page']['index'] = $config['siteurl'] . $url['page']['index'];
			$url['page']['list'] = $config['siteurl'] . $url['page']['list'];
		}

		if (strpos($url["url"], '://') === false) {
			$url["url"] = str_replace('//', '/', $url["url"]);
		}

		//把生成路径中的分页标签替换
		$url['path'] = str_replace('{$page}', $page, $url['path']);

		return $url;
	}

	/**
	 * 获取栏目的访问路径
	 * @param type $catid 栏目id
	 * @param type $page 当前分页码
	 * @return array Array
	 * (
	 *   [url] => http://news.abc.com/ 访问地址
	 *   [path] => record/index.html 生成路径 动态木有
	 *   [page] => Array 用于分页
	 *  (
	 *    [index] => http://news.abc.com/index_{$page}.html
	 *    [list] => http://news.abc.com/index.html
	 *   )
	 *  )
	 */
	public function category_url($catid, $page = 1, $category_ruleid = false) {
		//栏目数据
		$category = getCategory($catid);
		if (empty($category)) {
			return false;
		}
		//外部链接直接返回外部地址
		if ($category['type'] == 2) {
			return $category['url'];
		}
		//栏目扩展配置信息
		$setting = $category['setting'];
		//网站配置
		$config = cache("Config");
		//页码
		$page = max(intval($page), 1);
		static $_category_url = array();
		if (!isset($_category_url[$catid])) {
			//栏目URL生成规则ID
			$category_ruleid = $category_ruleid ? $category_ruleid : (int) $setting['category_ruleid'];
			//取得规则
			$urlrule = $this->urlrules[$category_ruleid]['urlrule'];
			//使用自定义函数生成规则
			if (substr($urlrule, 0, 1) == '=') {
				load("Content/urlrule");
				$fun = str_replace(substr($urlrule, 0, 1), "", $urlrule);
				$urlrule = call_user_func_array(trim($fun), array(
					"catid" => $catid,
					"page" => $page,
				));
			}
			$replace_l = array(); //需要替换的标签
			$replace_r = array(); //替换的内容
			//初始
			if (strstr($urlrule, '{$categorydir}')) {
				//获取当前栏目父栏目路径
				$category_dir = $this->get_categorydir($catid);
				$replace_l[] = '{$categorydir}';
				$replace_r[] = $category_dir;
			}
			if (strstr($urlrule, '{$catdir}')) {
				$replace_l[] = '{$catdir}';
				$replace_r[] = $category['catdir'];
			}
			$replace_l[] = '{$catid}';
			$replace_r[] = $catid;
			//标签替换
			$urlrule = str_replace($replace_l, $replace_r, $urlrule);
			$_category_url[$catid] = $urlrule;
		} else {
			$urlrule = $_category_url[$catid];
		}
		//检测是否要生成静态
		if ($setting['ishtml']) {
			//所有父ID
			$parentids = array();
			if ($category['arrparentid']) {
				$parentids = explode(',', $category['arrparentid']);
			}
			//把自身栏目id加入到父id数组中
			$parentids[] = $catid;
			$domain_dir = '';
			//循环查询父栏目是否设置了二级域名
			foreach ($parentids as $pid) {
				$r = getCategory($pid);
				if (!$r) {
					continue;
				}
				if ($r['domain'] && strpos($r['domain'], '?') === false) {
					$r['domain'] = preg_replace('/([(http|https):\/\/]{0,})([^\/]*)([\/]{1,})/i', '$1$2/', $r['domain'], -1); //取消掉双'/'情况
					//二级域名
					$domain = $r['domain'];
					//得到二级域名的目录
					$domain_dir = $this->get_categorydir($pid) . getCategory($pid, 'catdir') . '/';
				}
			}
			//绑定域名
			if ($domain && $domain_dir) {
				//检查是否保护绑定域名目录
				if (strpos($urlrule, $domain_dir) === false) {
					$urlrule = explode("|", $urlrule);
					//强制加上域名绑定目录
					foreach ($urlrule as $k => $url) {
						$urlrule[$k] = $domain_dir . $url;
					}
					$urlrule = implode("|", $urlrule);
				}
				$urlrule = str_replace(array($domain_dir, '\\'), array($domain, '/'), $urlrule);
			}
		}
		$urlrule = explode("|", $urlrule);
		$url = array(
			"url" => ($page > 1 ? $urlrule[1] : $urlrule[0]),
			"path" => "",
		);

		//用于分页使用
		$url['page'] = array(
			"index" => $urlrule[0],
			"list" => $urlrule[1],
		);

		//如果绑定域名，分析真实的生成目录
		$parse_url = parse_url($url['url']);
		if ($domain && $domain_dir) {
			$url['path'] = "/" . str_replace(array("//", "\\"), '/', $domain_dir . $parse_url['path']);
		} else {
			$url['path'] = "/" . str_replace(array("//", "\\"), '/', $parse_url['path']);
		}

		//判断是否为首页文件，如果是，就不显示文件名，隐藏
		if (in_array(basename($url["url"]), array('index.html', 'index.htm', 'index.shtml'))) {
			$url["url"] = dirname($url["url"]) . '/';
		}

		//判断是否有加域名
		if (!isset($parse_url['host'])) {
			$url['url'] = $config['siteurl'] . $url['url'];
			$url['page']['index'] = $config['siteurl'] . $url['page']['index'];
			$url['page']['list'] = $config['siteurl'] . $url['page']['list'];
		}

		//是否指定地址
		if ($setting['seturl']) {
			$url["url"] = $setting['seturl'];
		}

		if (strpos($url["url"], '://') === false) {
			$url["url"] = str_replace('//', '/', $url["url"]);
		}

		$url["url"] = str_replace('{$page}', $page, $url["url"]);

		//把生成路径中的分页标签替换
		$url['path'] = str_replace('{$page}', $page, $url['path']);

		return $url;
	}

	/**
	 * 获取Tags标签访问路径
	 * @param type $data Tags数据，可以是数组，tagsname或者id
	 * @param type $page 分页
	 * @param type $ruleid 规则ID
	 * @return type
	 */
	public function tags($data = '', $page = 1, $ruleid = 0) {
		static $_tags = array();
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
		$guid = to_guid_string($data);
		//网站配置
		$config = cache('Config');
		if (!isset($_tags[$guid])) {
			//字符串表示 tags
			if (is_string($data)) {
				$data = M('Tags')->where(array('tag' => $data))->find();
			} else if (is_numeric($data)) {
//tagsid
				$data = M('Tags')->where(array('tagid' => $data))->find();
			}
			if (empty($data)) {
				return false;
			}
			//url规则
			$urlrule = $this->urlrules[$ruleid ?: $config['tagurl']]['urlrule'];
			$urlrule = $urlrule ?: 'index.php?m=Tags&tag={$tag}|index.php?m=Tags&page={$page}&tag={$tag}';
			$replace_l = array(); //需要替换的标签
			$replace_r = array(); //替换的内容
			if (strstr($urlrule, '{$tag}')) {
				//获取当前栏目父栏目路径
				$replace_l[] = '{$tag}';
				$replace_r[] = $data['tag'];
			}
			if (strstr($urlrule, '{$tagid}')) {
				//获取当前栏目父栏目路径
				$replace_l[] = '{$tagid}';
				$replace_r[] = $data['tagid'];
			}
			//标签替换
			$_tags[$guid] = $urlrule = str_replace($replace_l, $replace_r, $urlrule);
		} else {
			$urlrule = $_tags[$guid];
		}
		$urlrule = explode('|', $urlrule);
		$url = array(
			'url' => ($page > 1 ? $urlrule[1] : $urlrule[0]),
			'path' => '',
		);
		//用于分页使用
		$url['page'] = array(
			'index' => $urlrule[0],
			'list' => $urlrule[1],
		);
		//加上域名
		$url['url'] = $config['siteurl'] . $url['url'];
		$url['page']['index'] = $config['siteurl'] . $url['page']['index'];
		$url['page']['list'] = $config['siteurl'] . $url['page']['list'];
		//替换分页号
		$url['url'] = str_replace('{$page}', $page, $url["url"]);
		return $url;
	}

	/**
	 * 生成自定义列表相关地址
	 * @staticvar array $_createListUrl
	 * @param type $data
	 * @param type $page
	 * @return boolean
	 * Array
	 * (
	 *     [url] => http://news.abc.com/1970/web_01/2.html 访问路径
	 *     [path] => /record/1970/web_01/2.html 生成路径 动态木有
	 *     [page] => Array
	 *     (
	 *         [index] => http://news.abc.com/1970/web_01/2.html
	 *         [list] => http://news.abc.com/1970/web_01/2_{$page}.html
	 *     )
	 * )
	 */
	public function createListUrl($data = '', $page = 1) {
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
			$data = M('Customlist')->where(array('id' => $data))->find();
			if (empty($data)) {
				$this->error = '没有数据';
				return false;
			}
		}
		static $_createListUrl = array();
		//网站配置
		$config = cache('Config');
		//页码
		$page = max(intval($page), 1);
		//取得规则
		if (!empty($data['urlrule'])) {
			$urlrule = $data['urlrule'];
		} else {
			$urlrule = $this->urlrules[$data['urlruleid']]['urlrule'];
		}
		//如果规则为空
		if (empty($urlrule)) {
			return false;
		}
		$guid = to_guid_string($data);
		if (!isset($_createListUrl[$guid])) {
			//使用自定义函数生成规则
			if (substr($urlrule, 0, 1) == '=') {
				load("Content/urlrule");
				$fun = str_replace(substr($urlrule, 0, 1), "", $urlrule);
				$urlrule = call_user_func_array(trim($fun), array(
					"id" => $id,
					"page" => $page,
				));
			}

			$replace_l = array(); //需要替换的标签
			$replace_r = array(); //替换的内容
			//年份
			if (strstr($urlrule, '{$year}')) {
				$replace_l[] = '{$year}';
				$replace_r[] = date('Y', $data['createtime']);
			}
			//月份
			if (strstr($urlrule, '{$month}')) {
				$replace_l[] = '{$month}';
				$replace_r[] = date('m', $data['createtime']);
			}
			//日期
			if (strstr($urlrule, '{$day}')) {
				$replace_l[] = '{$day}';
				$replace_r[] = date('d', $data['createtime']);
			}
			$replace_l[] = '{$id}';
			$replace_r[] = $data['id'];
			//标签替换
			$urlrule = str_replace($replace_l, $replace_r, $urlrule);
			$urlrule = explode('|', $urlrule);
			$_createListUrl[$guid] = $urlrule;
		} else {
			$urlrule = $_createListUrl[$guid];
		}

		$url = array(
			"url" => ($page > 1 ? $urlrule[1] : $urlrule[0]),
			"path" => "",
		);
		//用于分页使用
		$url['page'] = array(
			"index" => $urlrule[0],
			"list" => $urlrule[1],
		);
		//如果绑定域名，分析真实的生成目录
		$parse_url = parse_url($url['url']);
		$url['path'] = "/" . str_replace(array("//", "\\"), '/', $parse_url['path']);
		//判断是否为首页文件，如果是，就不显示文件名，隐藏
		if (in_array(basename($url["url"]), array('index.html', 'index.htm', 'index.shtml'))) {
			$url["url"] = dirname($url["url"]) . '/';
		}
		//判断是否有加域名
		if (!isset($parse_url['host'])) {
			$url['url'] = $config['siteurl'] . $url['url'];
			$url['page']['index'] = $config['siteurl'] . $url['page']['index'];
			$url['page']['list'] = $config['siteurl'] . $url['page']['list'];
		}
		if (strpos($url["url"], '://') === false) {
			$url["url"] = str_replace('//', '/', $url["url"]);
		}
		$url["url"] = str_replace('{$page}', $page, $url["url"]);
		//把生成路径中的分页标签替换
		$url['path'] = str_replace('{$page}', $page, $url['path']);
		return $url;
	}

	/**
	 * 根据栏目ID获取父栏目路径
	 * @param $catid
	 * @param $dir
	 */
	public function get_categorydir($catid, $dir = '') {
		//检查这个栏目是否有父栏目ID
		if (getCategory($catid, 'parentid')) {
			//取得父栏目目录
			$dir = getCategory(getCategory($catid, 'parentid'), 'catdir') . '/' . $dir;
			return $this->get_categorydir(getCategory($catid, 'parentid'), $dir);
		} else {
			return $dir;
		}
	}

	//根据栏目ID，取得栏目路径
	public function get_categorydirpath($catid) {
		if (!$catid) {
			return false;
		}
		$catdir = M("Category")->where(array("catid" => $catid))->getField("catdir");
		if (!$catdir) {
			return false;
		}
		$parent = $this->get_categorydir($catid);
		return $parent . $catdir . "/";
	}

}
