<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link href="favicon.ico" rel="shortcut icon" />
<link rel="canonical" href="{$config_siteurl}" />
<title><if condition=" isset($SEO['title']) && !empty($SEO['title']) ">{$SEO['title']}</if>{$SEO['site_title']}</title>
<meta name="description" content="{$SEO['description']}" />
<meta name="keywords" content="{$SEO['keyword']}" />
<link href="{$config_siteurl}statics/default/css/images.css" rel="stylesheet" type="text/css" />
<link href="{$config_siteurl}statics/default/css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "{$config_siteurl}",
    JS_ROOT: "statics/js/"
};
</script>
<script src="{$config_siteurl}statics/js/jquery.js" type="text/javascript"></script>
<script src="{$config_siteurl}statics/default/js/w3cer.js" type="text/javascript"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/ajaxForm.js"></script>
</head>
<body>
<template file="Content/header.php"/>
<div class="map"><span class="home_ico">当前位置：<a href="{$config_siteurl}">{$Config.sitename}</a> &gt; <navigate catid="$catid" space=" &gt; " /></span>
  <p style="float:right;padding-right:15px;"></p>
</div>
<div class="w972">
  <div class="article_con">
    <h1 title="{$title}">{$title}</h1>
    <p class="info"> 作者:佚名&nbsp;&nbsp;&nbsp;更新时间:{$updatetime}&nbsp;&nbsp;&nbsp; 点击次数:<span id="hits">0</span>次 </p>
    <!-- /info -->
    <div class="img_con">
      <center>
       {$content}
      </center>
      <div class="fanye" style="border: 0px solid #CCC;">
      <ul>
        {$pages}
      </ul>
      <div style="clear:both"></div>
    </div>
    </div>
  </div>
</div>
<div style="height:90px;border:1px solid #ccc;margin:10px auto 0;width:970px;overflow:hidden;text-align:center;padding:5px 0;background:#fafafa;"> 
  <img src="http://lorempixel.com/960/90" />
</div>
<div class="shangxiaye">
  <ul>
    <li>上一篇：<pre target="1" msg="已经没有了" /> </li>
    <li>下一篇：<next target="1" msg="已经没有了" /> </li>
  </ul>
  <!-- JiaThis Button BEGIN -->
  <div class="jiathis_style_32x32"> <a class="jiathis_button_qzone"></a> <a class="jiathis_button_tsina"></a> <a class="jiathis_button_tqq"></a> <a class="jiathis_button_renren"></a> <a class="jiathis_button_kaixin001"></a> <a class="jiathis_button_cqq"></a> <a class="jiathis_button_copy"></a> <a class="jiathis_button_email"></a> <a class="jiathis_button_baidu"></a> <a class="jiathis_button_douban"></a> <a href="http://www.jiathis.com/share" class="jiathis jiathis_txt jiathis_separator jtico jtico_jiathis" target="_blank"></a> <a class="jiathis_counter_style"></a> </div>
  <script type="text/javascript" >
var jiathis_config={
	summary:"",
	hideMore:false
}
</script> 
  <script type="text/javascript" src="http://v3.jiathis.com/code/jia.js" charset="utf-8"></script> 
  <!-- JiaThis Button END -->
  <div style="clear:both"></div>
</div>
<!--评论部分-->
<div class="duoshuo">
  <h2><span>此评论不代表本站观点</span>说说你的看法吧</h2>
  <div id="ds-reset" style="margin: 8px;"></div>
</div>
<template file="Content/footer.php"/>
<script type="text/javascript">
$(function (){
	$(window).toTop({showHeight : 100});
	//点击
	$.get("{$config_siteurl}api.php?m=Hits&catid={$catid}&id={$id}", function (data) {
	    $("#hits").html(data.views);
	}, "json");
});
//评论
var commentsQuery = {
    'catid': '{$catid}',
    'id': '{$id}',
    'size': 10
};
(function () {
    var ds = document.createElement('script');
    ds.type = 'text/javascript';
    ds.async = true;
    ds.src = GV.DIMAUB+'statics/js/comment/embed.js';
    ds.charset = 'UTF-8';
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ds);
})();
//评论结束
</script> 
</body>
</html>
