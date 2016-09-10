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
 
       <link href="{$config_siteurl}statics/default/style/css.css" rel="stylesheet" type="text/css" />
<div id="wrapper">
<div id="picSlideWrap" class="clearfix">
        <h3 class="titleh3">{$title}</h3>
        <h4 class="titleh4">发布时间：{$updatetime}  |  您是第<span id="hits">0</span>位浏览者 </p></h4>
    
        <div class="imgnav" id="imgnav"> 
    
             <div id="img"> 
               <volist name="imgs" id="vo">
                <img src="{$vo.url}" width="780" height="570"/>
               </volist>
                <div id="front" title="上一张"><a href="javaScript:void(0)" class="pngFix"></a></div>
                <div id="next" title="下一张"><a href="javaScript:void(0)" class="pngFix"></a></div>
             </div>
             
             <div id="content">
    　　         <p>  {$content}</p>
                <p align="right">{$updatetime}</p>
             </div>
              
             <div id="cbtn">
                <i class="picSildeLeft"><img src="{$config_siteurl}statics/default/style/picSlideLeft.gif"/></i> 
                <i class="picSildeRight"><img src="{$config_siteurl}statics/default/style/picSlideRight.gif"/></i> 
                <div id="cSlideUl">
                    <ul>
                    <volist name="imgs" id="vo">
                        <li><img src="{$vo.url}"/><tt></tt></li>
                     </volist>
                    </ul>
  </div>
             </div>         
            
        </div>
    
    </div> 

<script>
$(document).ready(function(){                          
    var index=0;
    var length=$("#img img").length;
    var i=1;
    
    //关键函数：通过控制i ，来显示图片
    function showImg(i){
        $("#img img")
            .eq(i).stop(true,true).fadeIn(800)
            .siblings("img").hide();
         $("#cbtn li")
            .eq(i).addClass("hov")
            .siblings().removeClass("hov");
    }
    
    function slideNext(){
        if(index >= 0 && index < length-1) {
             ++index;
             showImg(index);
        }else{
			if(confirm("已经是最后一张,点击确定重新浏览！")){
				showImg(0);
				index=0;
				aniPx=(length-5)*142+'px'; //所有图片数 - 可见图片数 * 每张的距离 = 最后一张滚动到第一张的距离
				$("#cSlideUl ul").animate({ "left": "+="+aniPx },200);
				i=1;
			}
            return false;
        }
        if(i<0 || i>length-5) {return false;}						  
               $("#cSlideUl ul").animate({ "left": "-=142px" },200)
               i++;
    }
     
    function slideFront(){
       if(index >= 1 ) {
             --index;
             showImg(index);
        }
        if(i<2 || i>length+5) {return false;}
               $("#cSlideUl ul").animate({ "left": "+=142px" },200)
               i--;
    }	
        $("#img img").eq(0).show();
        $("#cbtn li").eq(0).addClass("hov");
        $("#cbtn tt").each(function(e){
            var str=(e+1)+"/"+length;
            $(this).html(str)
        })
    
        $(".picSildeRight,#next").click(function(){
               slideNext();
           })
        $(".picSildeLeft,#front").click(function(){
               slideFront();
           })
        $("#cbtn li").click(function(){
            index  =  $("#cbtn li").index(this);
            showImg(index);
        });	
		$("#next,#front").hover(function(){
			$(this).children("a").fadeIn();
		},function(){
			$(this).children("a").fadeOut();
		})
    })	
</script>
   
        <div class="fanye" style="border: 0px solid #CCC;">
      <ul>
        {$pages}
      </ul>
      <div style="clear:both"></div>
    </div>
      
      <div class="page">
        <p>上一篇：<pre target="1" msg="已经没有了" /> </p>
        <p>下一篇：<next target="1" msg="已经没有了" /> </p>
      </div>
    
    <!--评论部分-->
    <div class="duoshuo">
      <h2><span>此评论不代表本站观点</span>大家说</h2>
      <!--评论主体-->
      <div id="ds-reset" style="margin: 8px;"></div>
    </div>
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
