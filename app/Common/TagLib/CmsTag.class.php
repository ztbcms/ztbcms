<?php

// +----------------------------------------------------------------------
// | 标签解析库
// +----------------------------------------------------------------------

namespace Common\TagLib;

use Think\Template\TagLib;

class CmsTag extends TagLib {

	// 数据库where表达式
	protected $comparisonCms = array(
		'{eq}' => '=',
		'{neq}' => '<>',
		'{elt}' => '<=',
		'{egt}' => '>=',
		'{gt}' => '>',
		'{lt}' => '<',
	);
	// 标签定义
	protected $tags = array(
		//后台模板标签
		'admintemplate' => array('attr' => 'file', 'close' => 0),
		//前台模板标签
		'template' => array('attr' => 'file,theme', 'close' => 0),
		//区块缓存
		'blockcache' => array('attr' => 'cache', 'close' => 1),
		//上一篇
		'pre' => array('attr' => 'catid,id,target,msg,field', 'close' => 0),
		//下一篇
		'next' => array('attr' => 'catid,id,target,msg,field', 'close' => 0),
		//导航标签
		'navigate' => array('attr' => 'cache,catid,space,blank', 'close' => 0),
		//Form标签
		'form' => array("attr" => "function,parameter", "close" => 0),
		//SQL标签
		'get' => array("attr" => 'sql,cache,page,dbsource,return,num,pagetp,pagefun,table,order,where', 'level' => 3),
		//推荐位标签
		'position' => array('attr' => 'action,cache,num,return,posid,catid,thumb,order,where', 'level' => 3),
		//评论标签
		'comment' => array('attr' => 'action,cache,num,return,catid,hot,date', 'level' => 3),
		//Tags标签
		'tags' => array('attr' => 'action,cache,num,page,pagetp,pagefun,return,order,tag,tagid,where', 'level' => 3),
		//sp模块调用标签
		'spf' => array('attr' => 'module,action,cache,num,page,pagetp,pagefun,return,where,order', 'level' => 3),
		//内容标签
		'content' => array('attr' => 'action,cache,num,page,pagetp,pagefun,return,where,moreinfo,thumb,order,day,catid,output', 'level' => 3),
	);

	/**
	 * 模板包含标签
	 * 格式：<admintemplate file="模块/控制器/模板名"/>
	 * @param  $attr 属性字符串
	 * @param  $content 标签内容
	 * @return string 标签解析后的内容
	 */
	public function _admintemplate($attr, $content) {
		$file = explode("/", $attr['file']);
		$counts = count($file);
		if ($counts < 2) {
			return '';
		} else if ($counts < 3) {
			$file_path = "Admin/" . C('DEFAULT_V_LAYER') . "/{$attr['file']}";
		} else {
			$file_path = "$file[0]/" . C('DEFAULT_V_LAYER') . "/{$file[1]}/{$file[2]}";
		}
		//模板路径
		$TemplatePath = APP_PATH . $file_path . C("TMPL_TEMPLATE_SUFFIX");
		//判断模板是否存在
		if (file_exists_case($TemplatePath)) {
			//读取内容
			$tmplContent = file_get_contents($TemplatePath);
			//解析模板内容
			$parseStr = $this->tpl->parse($tmplContent);
			return $parseStr;
		}
		return '';
	}

	/**
	 * 加载前台模板
	 * 格式：<template file="Content/footer.php" theme="主题"/>
	 * @staticvar array $_templateParseCache
	 * @param  $attr file，theme
	 * @param  $content
	 * @return string|array 返回模板解析后的内容
	 */
	public function _template($attr, $content) {
		$config = cache('Config');
		$theme = $attr['theme'] ?: $config['theme'];
		$templateFile = $attr['file'];
		//不是直接指定模板路径的
		if (false === strpos($templateFile, C('TMPL_TEMPLATE_SUFFIX'))) {
			$templateFile = TEMPLATE_PATH . $theme . '/' . $templateFile . C('TMPL_TEMPLATE_SUFFIX');
		} else {
			$templateFile = TEMPLATE_PATH . $theme . '/' . $templateFile;
		}
		//判断模板是否存在
		if (!file_exists_case($templateFile)) {
			$templateFile = str_replace($theme . '/', 'Default/', $templateFile);
			if (!file_exists_case($templateFile)) {
				return '';
			}
		}
		//读取内容
		$tmplContent = file_get_contents($templateFile);
		//解析模板
		$parseStr = $this->tpl->parse($tmplContent);
		return $parseStr;
	}

	/**
	 * 区块内容缓存标签
	 * @param  $tag
	 * @param  $content
	 * @return string
	 */
	public function _blockcache($tag, $content) {
		$cacheIterateId = to_guid_string(array($tag, $content));
		//缓存时间
		$cache = (int) $tag['cache'] ?: 300;
		$parsestr = '<?php ';
		$parsestr .= ' $_cache = S("' . $cacheIterateId . '"); ';
		$parsestr .= ' if ($_cache) { ';
		$parsestr .= '    echo $_cache;';
		$parsestr .= ' }else{ ';
		$parsestr .= ' ob_start(); ';
		$parsestr .= ' ob_implicit_flush(0); ';
		$parsestr .= ' ?> ';
		$parsestr .= $content;
		$parsestr .= ' <?php';
		$parsestr .= ' $_html = ob_get_clean(); ';
		$parsestr .= ' if ($_html) { S("' . $cacheIterateId . '", $_html, ' . $cache . ');}';
		$parsestr .= ' echo $_html; ';
		$parsestr .= ' } ';
		$parsestr .= ' ?>';
		return $parsestr;
	}

	/**
	 * 获取上一篇标签
	 * 使用方法：
	 *      用法示例：<pre catid="1" id="1" target="1" msg="已经没有了" />
	 * 参数说明：
	 *          @catid		栏目id，可以传入数字,在内容页可以不传
	 *          @id		信息id，可以传入数字,在内容页可以不传
	 *          @target		是否新窗口打开，1 是 0否
	 *          @msg		当没有上一篇时的提示语
	 * @param  $tag
	 * @param  $content
	 * @return string
	 */
	public function _pre($tag, $content) {
		//当没有内容时的提示语
		$msg = !empty($tag['msg']) ? $tag['msg'] : '已经没有了';
		//是否新窗口打开
		$target = !empty($tag['blank']) ? ' target=\"_blank\" ' : '';
		//返回对应字段内容
		$field = $tag['field'] && in_array($tag['field'], array('id', 'title', 'url')) ? $tag['field'] : '';
		if (!$tag['catid']) {
			$tag['catid'] = '$catid';
		}
		if (!$tag['id']) {
			$tag['id'] = '$id';
		}

		$parsestr = '<?php ';
		$parsestr .= ' $_pre_r = \Content\Model\ContentModel::getInstance(getCategory(' . $tag['catid'] . ',"modelid"))->where(array("catid"=>' . $tag['catid'] . ',"status"=>99,"id"=>array("LT",' . $tag['id'] . ')))->order(array("id" => "DESC"))->field("id,title,url")->find(); ';
		if ($field) {
			$parsestr .= ' echo $_pre_r?$_pre_r["' . $field . '"]:""';
		} else {
			$parsestr .= ' echo $_pre_r?"<a class=\"pre_a\" href=\"".$_pre_r["url"]."\" ' . $target . '>".$_pre_r["title"]."</a>":"' . str_replace('"', '\"', $msg) . '";';
		}
		$parsestr .= ' ?>';
		return $parsestr;
	}

	/**
	 * 获取下一篇标签
	 * 使用方法：
	 *      用法示例：<next catid="1" id="1" target="1" msg="已经没有了" />
	 * 参数说明：
	 *          @catid		栏目id，可以传入数字,在内容页可以不传
	 *          @id		信息id，可以传入数字,在内容页可以不传
	 *          @target		是否新窗口打开，1 是 0否
	 *          @msg		当没有上一篇时的提示语
	 * @param  $tag
	 * @param  $content
	 * @return string
	 */
	public function _next($tag, $content) {
		//当没有内容时的提示语
		$msg = !empty($tag['msg']) ? $tag['msg'] : '已经没有了';
		//是否新窗口打开
		$target = !empty($tag['blank']) ? ' target=\"_blank\" ' : '';
		//返回对应字段内容
		$field = $tag['field'] && in_array($tag['field'], array('id', 'title', 'url')) ? $tag['field'] : '';
		if (!$tag['catid']) {
			$tag['catid'] = '$catid';
		}
		if (!$tag['id']) {
			$tag['id'] = '$id';
		}

		$parsestr = '<?php ';
		$parsestr .= ' $_pre_n = \Content\Model\ContentModel::getInstance(getCategory(' . $tag['catid'] . ',"modelid"))->where(array("catid"=>' . $tag['catid'] . ',"status"=>99,"id"=>array("GT",' . $tag['id'] . ')))->order(array("id" => "ASC"))->field("id,title,url")->find(); ';
		if ($field) {
			$parsestr .= ' echo $_pre_n?$_pre_n["' . $field . '"]:""';
		} else {
			$parsestr .= ' echo $_pre_n?"<a class=\"pre_a\" href=\"".$_pre_n["url"]."\" ' . $target . '>".$_pre_n["title"]."</a>":"' . str_replace('"', '\"', $msg) . '";';
		}
		$parsestr .= ' ?>';
		return $parsestr;
	}

	/**
	 * 导航标签
	 * 使用方法：
	 *      用法示例：<navigate catid="$catid" space=" &gt; " />
	 * 参数说明：
	 *          @catid		栏目id，可以传入数字，也可以传递变量 $catid
	 *          @space		分隔符，支持html代码
	 *          @blank		是否新窗口打开
	 *          @cache                              缓存时间
	 * @staticvar array $_navigateCache
	 * @param  $tag 标签属性
	 * @param  $content 表情内容
	 * @return array|string
	 */
	public function _navigate($tag, $content) {
		$key = to_guid_string(array($tag, $content));
		$cache = (int) $tag['cache'];
		if ($cache) {
			$data = S($key);
			if ($data) {
				return $data;
			}
		}
		//分隔符，支持html代码
		$space = !empty($tag['space']) ? $tag['space'] : '&gt;';
		//是否新窗口打开
		$target = !empty($tag['blank']) ? ' target="_blank" ' : '';
		$catid = $tag['catid'];
		$parsestr = '';
		//如果传入的是纯数字
		if (is_numeric($catid)) {
			$catid = (int) $catid;
			if (getCategory($catid) == false) {
				return '';
			}
			//获取当前栏目的 父栏目列表
			$arrparentid = array_filter(explode(',', getCategory($catid, 'arrparentid') . ',' . $catid));
			foreach ($arrparentid as $cid) {
				$parsestr[] = '<a href="' . getCategory($cid, 'url') . '" ' . $target . '>' . getCategory($cid, 'catname') . '</a>';
			}
			$parsestr = implode($space, $parsestr);
		} else {
			$parsestr = '';
			$parsestr .= '<?php';
			$parsestr .= '  $arrparentid = array_filter(explode(\',\', getCategory(' . $catid . ',"arrparentid") . \',\' . ' . $catid . ')); ';
			$parsestr .= '  foreach ($arrparentid as $cid) {';
			$parsestr .= '      $parsestr[] = \'<a href="\' . getCategory($cid,\'url\')  . \'" ' . $target . '>\' . getCategory($cid,\'catname\') . \'</a>\';';
			$parsestr .= '  }';
			$parsestr .= '  echo  implode("' . $space . '", $parsestr);';
			$parsestr .= '?>';
		}
		if ($cache) {
			S($key, $parsestr, $cache);
		}
		return $parsestr;
	}

	/**
	 * 标签：<form/>
	 * 作用：生成各种表单元素
	 * 用法示例：<form function="date" parameter="name,$valeu"/>
	 * 参数说明：
	 *          @function		表示所使用的方法名称，方法来源于Form.class.php这个类。
	 *          @parameter		所需要传入的参数，支持变量！
	 *
	 * @param  $tag
	 * @param $content
     * @return string
	 */
	public function _form($tag, $content) {
		$function = $tag['function'];
		if (empty($function)) {
			return false;
		}
		$parameter = explode(',', $tag['parameter']);
		foreach ($parameter as $k => $v) {
			if ($v == "''" || $v == '""') {
				$v = "";
			}
			$parameter[$k] = trim($v);
		}
		$parameter = $this->arr_to_html($parameter);

		$parseStr = "<?php ";
		$parseStr .= ' echo call_user_func_array(array("\Form","' . $function . '"),' . $parameter . ');';
		//$parseStr .= " echo Form::$function(".$tag['parameter'].");\r\n";
		$parseStr .= " ?>";
		return $parseStr;
	}

	/**
	 * 内容标签 output参数等于1时，调用content_output.class.php处理
	 * 标签：<content></content>
	 * 作用：内容模型相关标签，可调用栏目，列表等常用信息
	 * 用法示例：<content action="lists" catid="$catid"  order="id DESC" num="4" page="$page"> .. HTML ..</content>
	 * 参数说明：
	 * 	基本参数
	 * 		@action		调用方法（必填）
	 * 		@page		当前分页号，默认$page，当传入该参数表示启用分页，一个页面只允许有一个page，多个标签使用多个page会造成不可预知的问题。
	 * 		@num		每次返回数据量
	 * 		@catid		栏目id（必填），列表页，内容页可以使用 $catid 获取当前栏目。
	 * 	公用参数：
	 * 		@cache		数据缓存时间，单位秒
	 * 		@pagefun                      分页函数，默认page
	 * 		@pagetp		分页模板，必须是变量传递
	 * 		@return		返回值变量名称，默认data
	 * 	#当action为lists时，调用栏目信息列表标签
	 * 	#用法示例：<content action="lists" catid="$catid"  order="id DESC" num="4" page="$page"> .. HTML ..</content>
	 * 	独有参数：
	 * 		@order		排序，例如“id DESC”
	 * 		@where		sql语句的where部分 例如：`thumb`!='' AND `status`=99
	 * 		@thumb		是否仅必须缩略图，1为调用带缩略图的
	 * 		@moreinfo                    是否调用副表数据 1为是
	 * 	#当action为hits时，调用排行榜
	 * 	#用法示例：<content action="hits" catid="$catid"  order="weekviews DESC" num="10"> .. HTML ..</content>
	 * 	独有参数：
	 * 		@order		排序，例如“weekviews DESC”
	 * 		@day		调用多少天内的排行
	 * 		@where		sql语句的where部分
	 * 	#当action为relation时，调用相关文章
	 * 	#用法示例：<content action="relation" relation="$relation" catid="$catid"  order="id DESC" num="5" keywords="$keywords"> .. HTML ..</content>
	 * 	独有参数：
	 * 		@nid		排除id 一般是 $id，排除当前文章
	 * 		@keywords	内容页面取值：$keywords，也就是关键字
	 * 		@relation		内容页取值$relation，当有$relation时keywords参数失效
	 * 		@where		sql语句的where部分
	 * 	#当action为category时，调用栏目列表
	 * 	#用法示例：<content action="category" catid="$catid"  order="listorder ASC" > .. HTML ..</content>
	 * 	独有参数：
	 * 		@order		排序，例如“id DESC”
	 * 		@where                          sql语句的where部分 例如：`child` = 0
	 *
	 *
	 * @param string $tag 标签属性
	 * @param string $content  标签内容
	 * @return string
	 */
	public function _content($tag, $content) {
		$tag['catid'] = $catid = $tag['catid'];
		//每页显示总数
		$tag['num'] = $num = (int) $tag['num'];
		//当前分页参数
		$tag['page'] = $page = (isset($tag['page'])) ? ((substr($tag['page'], 0, 1) == '$') ? $tag['page'] : (int) $tag['page']) : 0;
		//分页函数，默认page
		$tag['pagefun'] = $pagefun = empty($tag['pagefun']) ? "page" : trim($tag['pagefun']);
		//数据返回变量
		$tag['return'] = $return = empty($tag['return']) ? "data" : $tag['return'];
		//方法
		$tag['action'] = $action = trim($tag['action']);
		//sql语句的where部分
		if (isset($tag['where']) && $tag['where']) {
			$tag['where'] = $this->parseSqlCondition($tag['where']);
		}
		//拼接php代码
		$parseStr = '<?php';
		$parseStr .= ' $content_tag = \Think\Think::instance("\Content\TagLib\Content");' . "\r\n";
		//如果有传入$page参数，则启用分页。
		if ($page && in_array($action, array('lists'))) {
			//分页配置处理
			$pageConfig = $this->resolvePageParameter($tag);
			//进行信息数量统计 需要 action catid where
			$parseStr .= ' $count = $content_tag->count(' . self::arr_to_html($tag) . ');' . "\r\n";
			//分页函数
			$parseStr .= ' $_page_ = ' . $pagefun . '($count ,' . $num . ',' . $page . ',' . self::arr_to_html($pageConfig) . ');';
			$tag['count'] = '$count';
			$tag['limit'] = '$_page_->firstRow.",".$_page_->listRows';
			//总分页数，生成静态时需要
			$parseStr .= ' $GLOBALS["Total_Pages"] = $_page_->Total_Pages;';
			//显示分页导航
			$parseStr .= ' $pages = $_page_->show("default");';
			//分页总数
			$parseStr .= ' $pagetotal = $_page_->Total_Pages;';
			//总信息数
			$parseStr .= ' $totalsize = $_page_->Total_Size;';
		}
		$parseStr .= ' if(method_exists($content_tag, "' . $action . '")){';
		$parseStr .= ' $' . $return . ' = $content_tag->' . $action . '(' . self::arr_to_html($tag) . ');';
		$parseStr .= ' }';

		$parseStr .= ' ?>';
		//解析模板
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * 评论标签
	 * 标签：<comment></comment>
	 * 作用：评论标签
	 * 用法示例：<comment action="get_comment" catid="$catid" id="$id"> .. HTML ..</comment>
	 * 参数说明：
	 * 	基本参数
	 * 		@action		调用方法（必填）
	 * 		@catid		栏目id（必填），列表页，内容页可以使用 $catid 获取当前栏目。
	 * 	公用参数：
	 * 		@cache		数据缓存时间，单位秒
	 * 		@return		返回值变量名称，默认data
	 * 	#当action为get_comment时，获取评论总数
	 * 	#用法示例：<comment action="get_comment" catid="$catid" id="$id"> .. HTML ..</comment>
	 * 	独有参数：
	 * 		@id				信息ID
	 * 	#当action为lists时，获取评论数据列表
	 * 	#用法示例：<comment action="lists" catid="$catid" id="$id"> .. HTML ..</comment>
	 * 	独有参数：
	 * 		@id		信息ID
	 * 		@hot		排序方式｛0：最新｝
	 * 		@date		时间格式 Y-m-d H:i:s A
	 * 		@where                          sql语句的where部分
	 *    #当action为bang时，获取评论排行榜
	 * 	#用法示例：<comment action="bang" num="10"> .. HTML ..</comment>
	 * 	独有参数：
	 * 		@num		返回信息数
	+----------------------------------------------------------
	 * @param string $attr 标签属性
	 * @param string $content  标签内容
     * @return boolean|string
	 */
	public function _comment($tag, $content) {
		if (!isModuleInstall('Comments')) {
			return false;
		}
		/* 属性列表 */
		$num = (int) $tag['num']; //每页显示总数
		$return = empty($tag['return']) ? "data" : $tag['return']; //数据返回变量
		$action = $tag['action']; //方法

		$parseStr = '<?php';
		$parseStr .= ' $comment_tag = \Think\Think::instance("\Comments\TagLib\Comments");';
		$parseStr .= ' if(method_exists($comment_tag, "' . $action . '")){';
		$parseStr .= ' $' . $return . ' = $comment_tag->' . $action . '(' . self::arr_to_html($tag) . ');';
		$parseStr .= ' }';
		$parseStr .= ' ?>';
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * Tags标签
	 * 标签：<tags></tags>
	 * 作用：Tags标签
	 * 用法示例：<tags action="lists" tag="$tag" num="4" page="$page" order="updatetime DESC"> .. HTML ..</tags>
	 * 参数说明：
	 * 	基本参数
	 * 		@action		调用方法（必填）
	 * 		@page		当前分页号，默认$page，当传入该参数表示启用分页，一个页面只允许有一个page，多个标签使用多个page会造成不可预知的问题。
	 * 		@num		每次返回数据量
	 * 	公用参数：
	 * 		@cache		数据缓存时间，单位秒
	 * 		@return		返回值变量名称，默认data
	 * 		@pagefun                      分页函数，默认page()
	 * 		@pagetp		分页模板
	 * 		@where                        sql语句的where部分 例如：`child` = 0
	 * 	#当action为lists时，获取tag标签列表
	 * 	#用法示例：<tags action="lists" tag="$tag" num="4" page="$page" order="updatetime DESC"> .. HTML ..</tags>
	 * 	独有参数：
	 * 		@tag                               标签名，例如：厦门 支持多个，多个用空格或者英文逗号
	 * 		@tagid                            标签id 多个使用英文逗号隔开
	 * 		@order                            排序
	 * 		@num                             每次返回数据量
	 * 	#当action为top时，获取tag点击排行榜
	 * 	#用法示例：<tags action="top"  num="4"  order="tagid DESC"> .. HTML ..</tags>
	 * 	独有参数：
	 * 		@num                            每次返回数据量
	+----------------------------------------------------------
	 * @param array $tag 标签属性
	 * @param string $content  标签内容
     * @return string
	 */
	public function _tags($tag, $content) {
		/* 属性列表 */
		//每页显示总数
		$tag['num'] = $num = (int) $tag['num'];
		//当前分页参数
		$tag['page'] = $page = (isset($tag['page'])) ? ((substr($tag['page'], 0, 1) == '$') ? $tag['page'] : (int) $tag['page']) : 0;
		//分页函数，默认page
		$tag['pagefun'] = $pagefun = empty($tag['pagefun']) ? "page" : trim($tag['pagefun']);
		//数据返回变量
		$tag['return'] = $return = empty($tag['return']) ? "data" : $tag['return'];
		//方法
		$tag['action'] = $action = trim($tag['action']);
		//sql语句的where部分
		if ($tag['where']) {
			$tag['where'] = $this->parseSqlCondition($tag['where']);
		}
		$tag['where'] = $where = $tag['where'];

		$parseStr = '<?php';
		$parseStr .= ' $Tags_tag = \Think\Think::instance("\Content\TagLib\Tags");';
		//如果有传入$page参数，则启用分页。
		if ($page && in_array($action, array('lists'))) {
			//分页配置处理
			$pageConfig = $this->resolvePageParameter($tag);
			$parseStr .= ' $count = $Tags_tag->count(' . self::arr_to_html($tag) . ');';
			$parseStr .= ' $_page_ = ' . $pagefun . '($count ,' . $num . ',' . $page . ',' . self::arr_to_html($pageConfig) . ');';
			$tag['count'] = '$count';
			$tag['limit'] = '$_page_->firstRow.",".$_page_->listRows';
			//总分页数，生成静态时需要
			$parseStr .= ' $GLOBALS["Total_Pages"] = $_page_->Total_Pages;';
			//显示分页导航
			$parseStr .= ' $pages = $_page_->show("default");';
			//分页总数
			$parseStr .= ' $pagetotal = $_page_->Total_Pages;';
			//总信息数
			$parseStr .= ' $totalsize = $_page_->Total_Size;';
		}
		$parseStr .= ' if(method_exists($Tags_tag, "' . $action . '")){';
		$parseStr .= '     $' . $return . ' = $Tags_tag->' . $action . '(' . self::arr_to_html($tag) . ');';
		$parseStr .= ' };';

		$parseStr .= ' ?>';
		//解析模板
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * 推荐位标签
	 * 标签：<position></position>
	 * 作用：推荐位标签
	 * 用法示例：<position action="position" posid="1"> .. HTML ..</position>
	 * 参数说明：
	 * 	公用参数：
	 * 		@cache		数据缓存时间，单位秒
	 * 		@return		返回值变量名称，默认data
	 * 		@where                          sql语句的where部分
	 * 	#当action为position时，获取推荐位
	 * 	独有参数：
	 * 		@posid		推荐位ID(必填)
	 * 		@catid		调用栏目ID
	 * 		@thumb		是否仅必须缩略图
	 * 		@order		排序例如
	 * 		@num		每次返回数据量
	 *
	+----------------------------------------------------------
	 * @param array $tag 标签属性
	 * @param string $content  标签内容
     * @return string
	 */
	public function _position($tag, $content) {
		/* 属性列表 */
		$return = empty($tag['return']) ? "data" : $tag['return']; //数据返回变量
		$action = $tag['action']; //方法
		//sql语句的where部分
		if ($tag['where']) {
			$tag['where'] = $this->parseSqlCondition($tag['where']);
		}

		$parseStr = '<?php';
		$parseStr .= ' $Position_tag = \Think\Think::instance("\Content\TagLib\Position");';
		$parseStr .= ' if(method_exists($Position_tag, "' . $action . '")){';
		$parseStr .= '     $' . $return . ' = $Position_tag->' . $action . '(' . self::arr_to_html($tag) . ');';
		$parseStr .= ' };';
		$parseStr .= ' ?>';
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * 标签：<get></get>
	 * 作用：特殊标签，SQL查询标签
	 * 用法示例：<get sql="SELECT * FROM cms_article  WHERE status=99 ORDER BY inputtime DESC" page="$page" num="5"> .. HTML ..</get>
	 * 参数说明：
	 * 	@sql		SQL语句，强烈建议只用于select类型语句，其他SQL有严重安全威胁，同时不建议直接在SQL语句中使用外部变量，如:$_GET,$_POST等。
	 * 	@page		当前分页号，默认$page，当传入该参数表示启用分页，一个页面只允许有一个page，多个标签使用多个page会造成不可预知的问题。
	 * 	@num		每次返回数据量
	 * 	@cache		数据缓存时间，单位秒
	 * 	@return		返回值变量名称，默认data
	 * 	@pagefun	                    分页函数，默认page()
	 * 	@pagetp		分页模板
	 *
	 * +----------------------------------------------------------
	 * @param  $tag
	 * @param  $content
     * @return string|boolean
	 */
	public function _get($tag, $content) {
		//当前分页参数
		$tag['page'] = $page = (isset($tag['page'])) ? ((substr($tag['page'], 0, 1) == '$') ? $tag['page'] : (int) $tag['page']) : 0;
		//数据返回变量
		$tag['return'] = $return = empty($tag['return']) ? "data" : $tag['return'];
		//分页函数，默认page
		$tag['pagefun'] = $pagefun = empty($tag['pagefun']) ? "page" : trim($tag['pagefun']);
		//缓存时间
		$tag['cache'] = $cache = (int) $tag['cache'];
		//每页显示总数
		$tag['num'] = $num = isset($tag['num']) && intval($tag['num']) > 0 ? intval($tag['num']) : 20;
		//SQL语句
		if ($tag['sql']) {
			$tag['sql'] = $this->parseSqlCondition($tag['sql']);
		}
		$tag['sql'] = $sql = str_replace(array("think_", "cms_"), C("DB_PREFIX"), strtolower($tag['sql']));
		//数据源
		$tag['dbsource'] = $dbsource = $tag['dbsource'];
		//表名
		$table = str_replace(C("DB_PREFIX"), '', $tag['table']);
		if (!$sql && !$table) {
			return false;
		}
		//删除，插入不执行！这样处理感觉有点鲁莽了，，，-__,-!
		if (strpos($tag['sql'], "delete") || strpos($tag['sql'], "insert")) {
			return false;
		}
		//分页配置处理
		if ($page) {
			$pageConfig = $this->resolvePageParameter($tag);
		}
		//如果使用table参数方式，使用类似tp的查询语言效果
		if ($table) {
			$table = strtolower($table);
			//条件
			$tableWhere = array();
			foreach ($tag as $key => $val) {
				if (!in_array($key, explode(',', $this->tags['get']['attr']))) {
					$tableWhere[$key] = $this->parseSqlCondition($val);
				}
			}
			if ($tag['where']) {
				$tableWhere['_string'] = $this->parseSqlCondition($tag['where']);
			}
		}
		$parseStr = ' <?php ';

		//有启用分页
		if ($page) {
			if ($table) {
				$parseStr .= ' $cache = ' . $cache . ';';
				$parseStr .= ' $cacheID = to_guid_string(array(' . self::arr_to_html($tableWhere) . ',' . $page . '));';
				//缓存处理
				$parseStr .= ' if($cache && $_return = S($cacheID)){ ';
				$parseStr .= ' $count = $_return["count"];';
				$parseStr .= ' }else{ ';
				$parseStr .= ' $get_db = M(ucwords("' . $table . '"));';
				//如果定义了数据源
				if ($dbsource) {
//                    $dbSource = F('dbSource');
					//                    $dbConfig = $dbSource[$dbsource];
					//                    if ($dbConfig) {
					//                        $db = 'mysql://' . $dbConfig['username'] . ':' . $dbConfig['password'] . '@' . $dbConfig['host'] . ':' . $dbConfig['port'] . '/' . $dbConfig['dbname'];
					//                    }
					//                    $parseStr .= ' $get_db->db(1,"' . $db . '"); ';
				}
				//取得信息总数
				$parseStr .= ' $count = $get_db->where(' . self::arr_to_html($tableWhere) . ')->count();';
				$parseStr .= ' } ';
			} else {
				//分析SQL语句
				if ($_sql = preg_replace('/select([^from].*)from/i', "SELECT COUNT(*) as count FROM ", $tag['sql'])) {
					//判断是否变量传递
					if (substr(trim($sql), 0, 1) == '$') {
						$parseStr .= ' $sql = str_replace(array("think_", "cms_"), C("DB_PREFIX"),' . $sql . ');';
						$parseStr .= ' $_count_sql = preg_replace("/select([^from].*)from/i", "SELECT COUNT(*) as count FROM ", $sql);';
						$parseStr .= ' $_sql = $sql;';
					} else {
						//统计SQL
						$parseStr .= ' $_count_sql = "' . str_replace('"', '\"', $_sql) . '";';
						$parseStr .= ' $_sql = "' . str_replace('"', '\"', $sql) . '";';
					}
					$parseStr .= ' $cache = ' . $cache . ';';
					$parseStr .= ' $cacheID = to_guid_string(array($_sql,' . $page . '));';
					//缓存处理
					$parseStr .= ' if($cache && $_return = S($cacheID)){ ';
					$parseStr .= ' $count = $_return["count"];';
					$parseStr .= ' }else{ ';
					$parseStr .= ' $get_db = M(); ';
					//如果定义了数据源
					if ($dbsource) {
//                        $dbSource = F('dbSource');
						//                        $dbConfig = $dbSource[$dbsource];
						//                        if ($dbConfig) {
						//                            $db = 'mysql://' . $dbConfig['username'] . ':' . $dbConfig['password'] . '@' . $dbConfig['host'] . ':' . $dbConfig['port'] . '/' . $dbConfig['dbname'];
						//                        }
						//                        $parseStr .= ' $get_db->db(1,"' . $db . '"); ';
					}
					//取得信息总数
					$parseStr .= ' $count = $get_db->query($_count_sql);';
					$parseStr .= ' $count = $count[0]["count"]; ';
					$parseStr .= ' } ';
				} else {
					return false;
				}
			}
			$parseStr .= ' $_page_ = ' . $pagefun . '($count ,' . $num . ',' . $page . ',' . self::arr_to_html($pageConfig) . ');';
			//显示分页导航
			$parseStr .= ' $pages = $_page_->show();';
			//总分页数
			$parseStr .= ' $GLOBALS["Total_Pages"] = $_page_->Total_Pages;';
			//分页总数
			$parseStr .= ' $pagetotal = $_page_->Total_Pages;';
			//总信息数
			$parseStr .= ' $totalsize = $_page_->Total_Size;';
			//缓存判断
			$parseStr .= ' if($cache && $_return){ ';
			$parseStr .= '      $' . $return . ' = $_return["data"]; ';
			$parseStr .= ' }else{ ';

			if ($table) {
				if ($tag['order']) {
					$parseStr .= ' $get_db->order("' . $tag['order'] . '"); ';
				}
				$parseStr .= '      $' . $return . ' = $get_db->where(' . self::arr_to_html($tableWhere) . ')->limit($_page_->firstRow.",".$_page_->listRows)->select();';
			} else {
				$parseStr .= '      $' . $return . ' = $get_db->query($_sql." LIMIT ".$_page_->firstRow.",".$_page_->listRows." ");';
			}

			//缓存处理
			$parseStr .= '      if($cache){ S($cacheID ,array("count"=>$count,"data"=>$' . $return . '),$cache); }; ';
			$parseStr .= ' } ';
		} else {
			$parseStr .= ' $cache = ' . $cache . ';';
			if ($table) {
				$parseStr .= ' $cacheID = to_guid_string(' . self::arr_to_html($tableWhere) . ');';
				$parseStr .= ' if(' . $cache . ' && $_return = S( $cacheID ) ){ ';
				$parseStr .= '      $' . $return . '=$_return;';
				$parseStr .= ' }else{ ';
				$parseStr .= ' $get_db = M(ucwords("' . $table . '"));';
				if ($tag['order']) {
					$parseStr .= ' $get_db->order("' . $tag['order'] . '"); ';
				}
				$parseStr .= '      $' . $return . '=$get_db->where(' . self::arr_to_html($tableWhere) . ')->limit(' . $num . ')->select();';
			} else {
				$parseStr .= ' $cacheID = to_guid_string(' . self::arr_to_html($tag) . ');';
				$parseStr .= ' if(' . $cache . ' && $_return = S( $cacheID ) ){ ';
				$parseStr .= '      $' . $return . '=$_return;';
				$parseStr .= ' }else{ ';
				//判断是否变量传递
				if (substr(trim($sql), 0, 1) == '$') {
					$parseStr .= ' $_sql = str_replace(array("think_", "cms_"), C("DB_PREFIX"),' . $sql . ');';
				} else {
					$parseStr .= ' $_sql = "' . str_replace('"', '\"', $sql) . '";';
				}
				$parseStr .= ' $get_db = M();';
				$parseStr .= '      $' . $return . '=$get_db->query($_sql." LIMIT ' . $num . ' ");';
			}
			$parseStr .= '      if(' . $cache . '){ S( $cacheID  ,$' . $return . ',$cache); }; ';
			$parseStr .= ' } ';
		}
		$parseStr .= '  ?>';
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * spf标签，用于调用模块扩展标签
	 * 标签：<spf></spf>
	 * 作用：调用非系统内置标签，例如安装新模块后，例如新模块（Demo）目录下TagLib/DemoTagLib.class.php(类名为DemoTagLib)
	 *          用法就是 <spf module="Demo" action="lists"> .. HTML ..</spf> lists表示类DemoTagLib中一个public方法。
	 * 用法示例：<spf module="Like"> .. HTML ..</spf>
	 * 参数说明：
	 * 	基本参数
	 * 		@module                     对应模块（必填）
	 * 		@action		调用方法（必填）
	 * 		@page		当前分页号，默认$page，当传入该参数表示启用分页，一个页面只允许有一个page，多个标签使用多个page会造成不可预知的问题。
	 * 		@num		每次返回数据量
	 * 	公用参数：
	 * 		@cache		数据缓存时间，单位秒
	 * 		@pagefun                      分页函数，默认page
	 * 		@pagetp		分页模板，必须是变量传递
	 * 		@return		返回值变量名称，默认data
	 * @staticvar array $sp_iterateParseCache
	 * @param  $tag
	 * @param  $content
	 * @return string
	 */
	public function _spf($tag, $content) {
		//模块
		$tag['module'] = $mo = ucwords($tag['module']);
		//每页显示总数
		$tag['num'] = $num = (int) $tag['num'];
		//当前分页参数
		$tag['page'] = $page = (isset($tag['page'])) ? ((substr($tag['page'], 0, 1) == '$') ? $tag['page'] : (int) $tag['page']) : 0;
		//分页函数，默认page
		$tag['pagefun'] = $pagefun = empty($tag['pagefun']) ? "page" : trim($tag['pagefun']);
		//数据返回变量
		$tag['return'] = $return = empty($tag['return']) ? "data" : $tag['return'];
		//方法
		$tag['action'] = $action = trim($tag['action']);
		//sql语句的where部分
		if ($tag['where']) {
			$tag['where'] = $this->parseSqlCondition($tag['where']);
		}
		$tag['where'] = $where = $tag['where'];

		//拼接php代码
		$parseStr = '<?php';
		$parseStr .= '  import("' . $mo . 'TagLib", APP_PATH . "' . $mo . '/TagLib/"); ';
		$parseStr .= '  $' . $mo . 'TagLib = \Think\Think::instance("\\' . $mo . '\\TagLib\\' . $mo . 'TagLib"); ';
		//如果有传入$page参数，则启用分页。
		if ($page) {
			//分页配置处理
			$pageConfig = $this->resolvePageParameter($tag);
			//进行信息数量统计 需要 action catid where
			$parseStr .= ' $count = $' . $mo . 'TagLib->count(' . self::arr_to_html($tag) . ');' . "\r\n";
			//分页函数
			$parseStr .= ' $_page_ = ' . $pagefun . '($count ,' . $num . ',' . $page . ',' . self::arr_to_html($pageConfig) . ');';
			$tag['count'] = '$count';
			$tag['limit'] = '$_page_->firstRow.",".$_page_->listRows';
			//总分页数，生成静态时需要
			$parseStr .= ' $GLOBALS["Total_Pages"] = $_page_->Total_Pages;';
			//显示分页导航
			$parseStr .= ' $pages = $_page_->show("default");';
			//分页总数
			$parseStr .= ' $pagetotal = $_page_->Total_Pages;';
			//总信息数
			$parseStr .= ' $totalsize = $_page_->Total_Size;';
		}
		$parseStr .= ' if(method_exists($' . $mo . 'TagLib, "' . $action . '")){';
		$parseStr .= ' $' . $return . ' = $' . $mo . 'TagLib->' . $action . '(' . self::arr_to_html($tag) . ');';
		$parseStr .= ' }';
		$parseStr .= ' ?>';
		$parseStr .= $this->tpl->parse($content);
		return $parseStr;
	}

	/**
	 * 转换数据为HTML代码
	 * @param array $data 数组
     * @return boolean
	 */
	private static function arr_to_html($data) {
		if (is_array($data)) {
			$str = 'array(';
			foreach ($data as $key => $val) {
				if (is_array($val)) {
					$str .= "'$key'=>" . self::arr_to_html($val) . ",";
				} else {
					//如果是变量的情况
					if (strpos($val, '$') === 0) {
						$str .= "'$key'=>$val,";
					} else if (preg_match("/^([a-zA-Z_].*)\(/i", $val, $matches)) {
                        //判断是否使用函数
						if (function_exists($matches[1])) {
							$str .= "'$key'=>$val,";
						} else {
							$str .= "'$key'=>'" . self::newAddslashes($val) . "',";
						}
					} else {
						$str .= "'$key'=>'" . self::newAddslashes($val) . "',";
					}
				}
			}
			return $str . ')';
		}
		return false;
	}

	/**
	 * 返回经addslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	protected static function newAddslashes($string) {
		if (!is_array($string)) {
			return addslashes($string);
		}

		foreach ($string as $key => $val) {
			$string[$key] = self::newAddslashes($val);
		}

		return $string;
	}

	/**
	 * 检查是否变量
	 * @param  $variable
	 * @return bool
	 */
	protected function variable($variable) {
		return substr(trim($variable), 0, 1) == '$';
	}

	/**
	 * 解析条件表达式
	 * @access public
	 * @param string $condition 表达式标签内容
	 * @return array
	 */
	protected function parseSqlCondition($condition) {
		$condition = str_ireplace(array_keys($this->comparisonCms), array_values($this->comparisonCms), $condition);
		return $condition;
	}

	/**
	 * 解析分页参数
	 * @param $tag
	 * @return array
	 */
	protected function resolvePageParameter(&$tag) {
		if (empty($tag)) {
			return array();
		}
		//分页设置
		$config = array();
		foreach ($tag as $key => $value) {
			if ($key && substr($key, 0, 5) == "page_") {
				//配置名称
				$name = str_replace('page_', '', $key);
				if (substr($value, 0, 1) == '$') {
					$config[$name] = $value;
				} else {
					$config[$name] = $this->parseSqlCondition($value);
				}
				unset($tag[$key]);
			}
		}
		//兼容 pagetp 参数
		if (!empty($tag['pagetp'])) {
			$config['tpl'] = (substr($tag['pagetp'], 0, 1) == '$') ? $tag['pagetp'] : '';
		}
		//标签默认开启自定义分页规则
		$config['isrule'] = true;
		return $config;
	}

}
