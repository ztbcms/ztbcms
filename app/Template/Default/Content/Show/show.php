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
<link href="{$config_siteurl}statics/default/css/article_list.css" rel="stylesheet" type="text/css" />
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
<div class="w972" style="margin-top:8px;">
  <div class="article_article_left left w972">
    <div class="article_con w972">
      <h1 title="{$title}">{$title}</h1>
      <p class="info"> 出处:本站原创&nbsp;&nbsp;&nbsp;发布时间:{$updatetime}&nbsp;&nbsp;&nbsp;   您是第<span id="hits">0</span>位浏览者 </p>
      <div class="tool_con">
        <div class="btn_fontsize"><a onFocus="if(this.blur)this.blur()" href="javascript:put_layer(2);"><img src="{$config_siteurl}statics/default/images/btn_fontsize_up.gif" title="工具栏"/></a> </div>
        <div id="put_layer2" class="tools" style="display:block;"> 
          <script src="{$config_siteurl}statics/default/js/tools.js" language="javascript" type="text/javascript"></script> 
        </div>
        <div style="clear:both;"></div>
      </div>
      <div class="article_txt" id="a_fontsize">
        {$content}
        <if condition=" $voteid "> 
          <script language="javascript" src="{$Config.siteurl}index.php?g=Vote&m=Index&a=show&action=js&subjectid={$voteid}&type=2"></script> 
        </if>
        <if condition=" !empty($download) ">
          <ul class="tow-columns clearfix">
            <volist name="download" id="vo">
              <li class="l"><a href="{$vo.fileurl}" target="_blank" class="btn-download" title="下载所需积分{$vo.point}">{$vo.filename}</a></li>
            </volist>
          </ul>
        </if>
        <div class="fanye" style="border: 0px solid #CCC;">
      <ul>
        {$pages}
      </ul>
      <div style="clear:both"></div>
    </div>
      </div>
      <div class="contentpage">
        <ul>
        </ul>
        <div style="clear:both"></div>
      </div>
      <!--分享到-->
      <div class="share">
        <div class="ilike"> 
          <!-- 将此标记放在您希望显示like按钮的位置 -->
          <div class="bdlikebutton"></div>
          <!-- 将此代码放在适当的位置，建议在body结束前 --> 
          <script id="bdlike_shell"></script> 
          <script>
			var bdShare_config = {
				"type":"medium",
				"color":"blue",
				"uid":"627811",
				"likeText":"喜欢,顶一个",
				"likedText":"亲.您已顶过"
			};
			document.getElementById("bdlike_shell").src="http://bdimg.share.baidu.com/static/js/like_shell.js?t=" + new Date().getHours();
		  </script> 
        </div>
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
      </div>
      <!--end-->
      <div class="related">
        <div class="xiangguan">
          <h2>相关文章推荐</h2>
          <ul>
          <content action="relation" relation="$relation" catid="$catid"  order="id DESC" num="10" keywords="$keywords" nid="$id">
            <volist name="data" id="vo">
            <li><a href='{$vo.url}' target="_blank">{$vo.title}</a></li>
            </volist>
          </content>
          </ul>
        </div>
        <div class="like_article_ad"> </div>
        <div style="clear:both"></div>
      </div>
      <div class="page">
        <p>上一篇：<pre target="1" msg="已经没有了" /> </p>
        <p>下一篇：<next target="1" msg="已经没有了" /> </p>
      </div>
    </div>
    <!--评论部分-->
    <div class="duoshuo">
      <h2><span>此评论不代表本站观点</span>大家说</h2>
      <!--评论主体-->
      <div id="ds-reset" style="margin: 8px;"></div>
    </div>
  </div>

  <div style="clear:both"></div>
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
