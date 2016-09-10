<?php if (!defined('CMS_VERSION')) exit(); ?>
<!doctype html>
<!--[if lt IE 8 ]> <html class="no-js ie6-7"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
<meta charset="utf-8">
<title>
<if condition=" isset($SEO['title']) && !empty($SEO['title']) ">{$SEO['title']}</if>
{$SEO['site_title']}</title>
<link rel="stylesheet" href="{$Config.siteurl}statics/blog/css/style.css" type="text/css" media="screen" />
<link rel='stylesheet' id='wp-recentcomments-css'  href='{$Config.siteurl}statics/blog/css/wp-recentcomments.css?ver=2.0.6' type='text/css' media='screen' />
<link rel="alternate" type="application/rss+xml" title="{$SEO['site_title']} - Rss" href="{$Config.siteurl}index.php?m=Rss&rssid={$catid}" />
<meta name="description" content="{$SEO['description']}" />
<meta name="keywords" content="{$SEO['keyword']}" />
<link rel="canonical" href="{$Config.siteurl}" />
<!--[if IE 7]>
<style type="text/css">
#sidebar {
    padding-top:40px;
}
.cm #commentform p {
	float:none;
	clear:none;
}
</style>
<![endif]-->
<script type='text/javascript' src='{$Config.siteurl}statics/js/jquery.js'></script>
<script type='text/javascript' src='{$Config.siteurl}statics/blog/js/ls.js'></script>
<!--html5 SHIV的调用-->
<script type='text/javascript' src='{$Config.siteurl}statics/blog/js/html5.js'></script>
<script type='text/javascript' src='{$Config.siteurl}statics/js/jquery.waterfall.min.js'></script>
<style type="text/css">
#container {
	position: relative;
	padding-bottom: 60px;
	overflow: hidden
}
#container, #pagenavi, #submit, #footer, .navigation, .post-share, #post-related {
	user-select: none;
	-webkit-user-select: none;
	-moz-user-select: none
}
.post-home {
	float: left;
	width: 235px;
	margin: 0 6px 15px 7px;
	overflow: hidden;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border: 1px solid #AAA;
	-webkit-box-shadow: rgba(0,0,0,.2) 0 0 5px;
	-moz-box-shadow: rgba(0,0,0,.2) 0 0 5px;
	box-shadow: rgba(0,0,0,.2) 0 0 5px;
	background-color: #FFF;
}
.post-home .post-content p {
	padding: 0 24px;
	line-height: 1.5em
}
.post-thumbnail a, .post-noimg {
	width: 216px;
	padding: 9px;
	background-color: #f8f8f8;
	border-color: #fff #fff #ccc #fff;
	border-color: rgba(255,255,255,0.5) rgba(255,255,255,0.5) #ccc rgba(255,255,255,0.5);
	border-style: solid;
	border-width: 1px;
	border-radius: 3px 3px 0 0;
	-moz-border-radius: 3px 3px 0 0;
	-webkit-border-radius: 3px 3px 0 0;
}
.post-thumbnail embed {
	width: 265px;
	height: 265px;
	margin: 10px 0 0 10px;
}
.post-noimg a {
	font-size: 16px;
	text-shadow: 0 1px 0 white;
	padding: 9px 5px;
	width: 257px;
	border-width: 0 0 1px 0
}
.post-noimg p {
	padding: 10px 5px;
	border-top: 1px solid #fff;
	border-top: 1px solid rgba(255,255,255,0.9)
}
.post-thumbnail .title {
	padding: 10px 24px;
	font-size: 16px
}
.post-thumbnail .title a {
	color: #21759b
}
.post-thumbnail a, .post-thumbnail span {
	display: block
}
.post-thumbnail a:hover, .navigation .next:hover, .navigation .prev:hover {
	text-decoration: none
}
.post-thumbnail a:hover {
	background-color: #fff;
	background-color: rgba(255,255,255,.8)
}
.post-thumbnail img {
	width: 216px;
	height: auto
}
.post_title {
	margin: 3px 2px 3px 0;
	text-align: center;
}
.post_title a:link, .post_title a:visited {
	color: #777;
}
.post-info {
	height: 35px;
	padding: 10px 14px;
	text-align: center;
	text-shadow: 0 1px 0 #fff;
	border-radius: 0 0 3px 3px;
	-moz-border-radius: 0 0 3px 3px;
	-webkit-border-radius: 0 0 3px 3px
}
.post-info, .post-info a, .post-noimg a, .post-noimg p {
	color: #777
}
.post-info div {
	width: 64px;
	height: 35px;
	float: left
}
.post-info span {
	display: block;
	width: 67px;
	height: 20px;
	font-size: 18px
}
.grid {
	letter-spacing: 0px;
}
.current {
	margin: 5px;
}
</style>
</head>
<body  class="home blog">
<!--header START-->
<header id="header">
  <div class="top">
    <section class="logo">
      <hgroup>
        <h1> <a href="{$Config.siteurl}" title="" rel="home">个人博客</a> </h1>
      </hgroup>
      <!--<a class="logo-link" href="/"><img src="{$Config.siteurl}statics/blog/images/LOGO-min.png" width="311" height="99" alt="'"> </a>  --> 
    </section>
  </div>
  <nav class="menu-nav">
    <ul class="grid">
      <li class="g-u item cat log"> <a href="{$Config.siteurl}"> <i class="img g-u png"></i>
        <figure class="g-u cat-desc"> <span class="cat g-u">日志</span> <span class="sub-cat">记录点点滴滴</span> </figure>
        </a> </li>
      <li class="g-u item cat photo"> <a href="{$Config.siteurl}photo/"> <i class="img g-u png"></i>
        <figure class="g-u cat-desc"> <span class="cat g-u">相册</span> <span class="sub-cat">那一瞬间</span> </figure>
        </a> </li>
      <li class="g-u item cat music"> <a href="{$Config.siteurl}music/"> <i class="img g-u png"></i>
        <figure class="g-u cat-desc"> <span class="cat g-u">音乐</span> <span class="sub-cat">用心倾听</span> </figure>
        </a> </li>
      <li class="g-u item cat book"> <a href="http://www.ztbcms.com/2012/work_05/1.shtml"> <i class="img g-u png"></i>
        <figure class="g-u cat-desc"> <span class="cat g-u">留言</span> <span class="sub-cat">你说我说他说</span> </figure>
        </a> </li>
      <li class="g-u item cat choice"> <a href="#"> <i class="img g-u png"></i>
        <figure class="g-u cat-desc"> <span class="cat g-u">精华</span> <span class="sub-cat">精华日志和资源集合</span> </figure>
        </a> </li>
    </ul>
  </nav>
</header>
<!--header END-->
<div id="main" class="grid">
  <div id="container" style="margin-top: 25px; width: 1000px; margin-right: auto;margin-bottom: 0px; margin-left: auto; ">
    <content action="lists" catid="$catid" order="id DESC" num="50" page="$page">
      <volist name="data" id="vo" offset="0" length="10">
        <div id="post-{$vo.id}" class="post-home">
          <div class="post-thumbnail"> <a href="{$vo.url}" title="{$vo.title}" target="_blank"><img width="216" height="300" src="{$vo.thumb}" alt="{$vo.title}"></a></div>
          <div class="post_title"> <a href="{$vo.url}" title="{$vo.title}" target="_blank">{$vo.title}</a> </div>
          <div class="post-info">
            <div><span>{$vo.views}</span>Views</div>
            <comment action="get_comment" catid="$vo['catid']" id="$vo['id']">
              <div><span><a href="{$vo.url}" title="{$vo.title}" target="_blank">{$data.total}</a></span>Comments</div>
            </comment>
            <div><span>{$vo.username}</span>Authors</div>
          </div>
        </div>
      </volist>
    </content>
  </div>
  <div id="loading" style="text-align:center; height:32px;line-height:32px;"><img style="vertical-align:middle;" src="{$Config.siteurl}statics/blog/images/load.gif">努力的加载数据中~~</div>
  <div class="wp-pagenavi" style="display:none;">{$pages}</div>
</div>
<template file="Content/footer.php"/> 
<!--[if lte IE 6]>
<script src="http://letskillie6.googlecode.com/svn/trunk/2/zh_CN.js"></script>
<![endif]--> 
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F6a7ac600fcf5ef3f164732dcea2e2ba5' type='text/javascript'%3E%3C/script%3E"));
</script> 
<script type="text/tmpl" id="photo-template">
<div id="post-<%=id%>" class="post-home">
    <div class="post-thumbnail">
        <a href="<%=url%>" title="<%=title%>" target="_blank"><img  src="<%=thumb%>" alt="<%=title%>"></a>
    </div>
    <div class="post_title">
        <a href="<%=url%>" title="<%=title%>" target="_blank"><%=title%></a>
    </div>
    <div class="post-info">
        <div><span><%=hits%></span>Views</div>
        <div><span><a href="<%=url%>" title="<%=title%>" target="_blank"><%=commcount%></a></span>Comments</div>
        <div><span><%=username%></span>Authors</div>
    </div>
</div>
</script> 
<script type="text/javascript">
$(function(){
	//瀑布流加载
	$("#container").waterfall({
		itemSelector:'.post-home',
		columnCount:4,
		columnWidth:250,
		endFn:function(){
			$("#loading").hide();
		}
	});
	
});
var LoadHTML = "";
var bonscroll = false;
//初始化ajax加载页码
var AjaxPage = "<?php echo ($page*5)-3?>";
//相册数据加载
function Ajaxphoto(page) {
    LoadHTML = $("#container").html();
    var data = "";
    var render = tmpl("photo-template");
    //判断是不是要翻页 ajax 一次加载10条，而文档分页是以一次50条，也就是一页需要ajax5次
    if (page - ( <?php echo  $page ?> * 5) == 0) {
        $(".wp-pagenavi").show();
        $("#loading").hide();
        return;
    }
    $.ajax({
        type: "get",
        url: "{$Config.siteurl}index.php?a=index&m=Index&g=Datacall&id=3",
        data: "page=" + page,
        dataType: "json",
        async: false,
        success: function (r) {
            //判断是否还有需要加载的数据
            if (Math.ceil(r.info.count / r.info.limit) < AjaxPage) {
                bonscroll = true;
                $("#loading").html("已经没有数据了~~~").fadeOut(4000);
                $(".wp-pagenavi").show();
                return;
            }
            AjaxPage = r.info.page + 1;
            $.each(r.data, function (i, v) {
                data += render(v);
            });
            $("#container").append(data).waterfall({
                endFn: function () {
                    bonscroll = false;
                    $("#loading").hide();
                }
            });
        }
    });
}
$(window).scroll(function () {
    //防止在加载过程再次加载
    if (bonscroll) {
        return;
    }
    bonscroll = true;
    var scrollHeight = $(document).height(); //网页页面高度
    var clientHeight = $(window).height(); //可见高度
    var scrollTop = document.documentElement.scrollTop + document.body.scrollTop; // 滚动条位置
    if (parseInt(scrollTop + clientHeight) / scrollHeight > 0.7) {
        $("#loading").show();
        Ajaxphoto(AjaxPage);
    } else {
        bonscroll = false;
    }
});
</script>
</body>
</html>
