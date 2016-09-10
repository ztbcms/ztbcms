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
<link href="{$config_siteurl}statics/default/css/index.css" rel="stylesheet" type="text/css" />
<link href="{$config_siteurl}statics/default/css/layout.css" rel="stylesheet" type="text/css" />
<script src="{$config_siteurl}statics/js/jquery.js" type="text/javascript"></script>
<script src="{$config_siteurl}statics/default/js/w3cer.js" type="text/javascript"></script>
<base target="_blank" />
</head>
<body>
<template file="Content/header.php"/>
<div class="map"><span class="home_ico">当前位置：<a href="{$config_siteurl}">{$Config.sitename}</a></span>
  <p style="float:right;padding-right:15px;"></p>
</div>
<div class="w972 margin8">
  <div class="index_left left">
    <div class="top_left">
      <div class="facus_left left">
        <div class="index_slide">
          <div class="focusBox">
          <position action="position" posid="1">
            <ul class="pic">
            <volist name="data" id="vo">
              <li><a href="{$vo.data.url}" title="{$vo.data.title}"><img src="{$vo.data.thumb}"></a></li>
            </volist>
            </ul>
            <div class="txt-bg"></div>
            <div class="txt">
              <ul>
              <volist name="data" id="vo">
              	<li><a href="{$vo.data.url}" title="{$vo.data.title}">{$vo.data.title}</a></li>
              </volist>
              </ul>
            </div>
            <ul class="num">
              <li class=""><a>1</a><span></span></li>
              <li class=""><a>2</a><span></span></li>
              <li class=""><a>3</a><span></span></li>
            </ul>
            </position>
          </div>
          <script type="text/javascript">
jQuery(".focusBox").slide({ titCell:".num li", mainCell:".pic",effect:"fold", autoPlay:true,trigger:"click",startFun:function(i){jQuery(".focusBox .txt li").eq(i).animate({"bottom":0}).siblings().animate({"bottom":-36});}});
</script> 
        </div>
        <!--index_slider end-->
        <div class="software">
          <h2><span class="more right"><a href="{:getCategory(9,'url')}" target="_blank">更多>></a></span><span class="h2_txt">常用软件</span></h2>
          <ul>
            <li><a href="{$config_siteurl}2013/software_06/31.html" title="photoshop CS5绿化破解版免费下载"><img src="{$config_siteurl}statics/default/images/ps_ico.png" alt="PS软件"/></a></li>
            <li><a href="{$config_siteurl}2013/software_06/32.html" title="Adobe Dreamweaver cs6汉化破解版免费下载"><img src="{$config_siteurl}statics/default/images/dw_ico.png" alt="Dreamweaver软件"/></a></li>
            <li><a href="{$config_siteurl}2013/software_06/33.html" title="Flash cs3下载"><img src="{$config_siteurl}statics/default/images/fl_ico.png" alt="Flash软件"/></a></li>
            <li><a href="{$config_siteurl}2013/software_06/33.html" title="Fireworks cs3下载"><img src="{$config_siteurl}statics/default/images/fw_ico.png" alt="Fireworks软件"/></a></li>
            <div style="clear:both"></div>
          </ul>
          <div class="softlist">
          <content action="lists" catid="9" order="id DESC" num="3">
          <volist name="data" id="vo">
            <dl>
              <dt><a href="{$vo.url}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="HttpWatch - 一款网站HTTP监"/></a></dt>
              <dd class="softname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,12}</a></dd>
              <dd class="softinfo">{$vo.description|str_cut=###,15}...</dd>
            </dl>
          </volist>
          </content>
          </div>
        </div>
        <!--常用软件 end--> 
      </div>
      <div class="top_right right">
        <div class="top_news">
        <position action="position" posid="2">
          <div class="news_bg"></div>
          <volist name="data" id="vo">
          <h1><a href="{$vo.data.url}" title="{$vo.data.title}">{$vo.data.title}</a></h1>
          <p> {$vo.data.description}<a href="{$vo.data.url}" target="_blank">[查看全文]</a></p>
          </volist>
        </position>
        </div>
        
        <!--首页头条end-->
        <div class="top_info">
          <h2><span class="more right"><a href="{:getCategory(7,'url')}" target="_blank">更多&gt;&gt;</a></span><span class="h2_txt">站长推荐</span></h2>
          <div id="ztlist1" class="ztlist" style="display:block">
          <position action="position" posid="3">
          <volist name="data" id="vo">
            <dl>
              <dt><a href="{$vo.data.url}" title="{$vo.data.title}"><img src="{$vo.data.thumb}" alt="{$vo.data.title}" /></a></dt>
              <dd class="info_tt"><a href="{$vo.data.url}" title="{$vo.data.title}">{$vo.data.title}</a></dd>
              <dd class="info_txt">{$vo.data.description}</dd>
            </dl>
          </volist>
          </position>
          </div>
        </div>
        <!--专题推荐end--> 
      </div>
      <div style="clear:both"></div>
    </div>
    <!--index_TOP_RIGHT end--> 
    <!--网页特效-->
    <div class="index_tab">
      <h2 class="h2">网页特效</h2>
      <ul class="tabs" id="tabs">
        <li><a href="{:getCategory(16,'url')}" tab="tab1" hidefocus="true" title="焦点幻灯片">JS幻灯片</a></li>
        <li><a href="{:getCategory(17,'url')}" tab="tab2" hidefocus="true" title="导航菜单">导航菜单</a></li>
      </ul>
      <div style="clear:both"></div>
      <ul class="tab_conbox">
        <li id="tab1" class="tab_con" style="display:block;">
        <content action="lists" catid="16"  order="id DESC" num="3">
        <volist name="data" id="vo">
          <dl>
            <dt><a href="{$vo.url}"><img src="{$vo.thumb}" alt="{$vo.title}"/></a></dt>
            <dd class="span1"><a href="{$vo.url}"  title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
            <dd class="span2"><a href="{:getCategory(16,'url')}" title="JS幻灯片">JS幻灯片</a>&nbsp;/&nbsp;{$vo.updatetime|date="m-d",###} </dd>
          </dl>
        </volist>
        </content>
          <div style="clear:both"></div>
        </li>
        <li id="tab2" class="tab_con">
         <content action="lists" catid="17"  order="id DESC" num="3">
         <volist name="data" id="vo">
          <dl>
            <dt><a href="{$vo.url}"><img src="{$vo.thumb}" alt="{$vo.title}"/></a></dt>
            <dd class="span1"><a href="{$vo.url}"  title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
            <dd class="span2"><a href="{:getCategory(17,'url')}" title="导航菜单">导航菜单</a>&nbsp;/&nbsp;{$vo.updatetime|date="m-d",###} </dd>
          </dl>
         </volist>
        </content>
          <div style="clear:both"></div>
        </li>
      </ul>
    </div>
    
    <!--网页素材-->
    <div class="index_tab">
      <h2 class="h2">建站素材</h2>
      <ul class="tabs" id="tabss">
        <li><a href="{:getCategory(18,'url')}" tab="tab1s" hidefocus="true">PNG图标</a></li>
        <li><a href="{:getCategory(19,'url')}" tab="tab2s" hidefocus="true">GIF图标</a></li>
      </ul>
      <div style="clear:both"></div>
      <ul class="tab_conbox">
        <li id="tab1s" class="tab_cons" style="display: block; ">
        <content action="lists" catid="18"  order="id DESC" num="3">
        <volist name="data" id="vo">
          <dl>
            <dt><a href="{$vo.url}"><img src="{$vo.thumb}" alt="{$vo.title}"/></a></dt>
            <dd class="span1"><a href="{$vo.url}"  title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
            <dd class="span2"><a href="{:getCategory(18,'url')}" title="PNG图标">PNG图标</a>&nbsp;/&nbsp;{$vo.updatetime|date="m-d",###} </dd>
          </dl>
        </volist>
        </content>
          <div style="clear:both"></div>
        </li>
        <li id="tab2s" class="tab_con">
         <content action="lists" catid="19"  order="id DESC" num="3">
         <volist name="data" id="vo">
          <dl>
            <dt><a href="{$vo.url}"><img src="{$vo.thumb}" alt="{$vo.title}"/></a></dt>
            <dd class="span1"><a href="{$vo.url}"  title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
            <dd class="span2"><a href="{:getCategory(19,'url')}" title="GIF图标">GIF图标</a>&nbsp;/&nbsp;{$vo.updatetime|date="m-d",###} </dd>
          </dl>
         </volist>
        </content>
          <div style="clear:both"></div>
        </li>
      </ul>
    </div>
  </div>
  <!--index_left end-->
  <div class="index_right right">
    <div class="quick_nav">
      <h2><span class="h2_txt">快捷导航</span></h2>
      <ul>
        <li><a href="http://www.css88.com/jqapi-1.9/" title="jquery1.9在线手册"><img  src="{$config_siteurl}statics/default/images/jq.png"/ alt="jquery1.8.3在线手册"><span>jquery手册</span></a></li>
        <li><a href="http://www.css88.com/book/css/" title="CSS3在线手册"><img  src="{$config_siteurl}statics/default/images/3.gif"/ alt="CSS3在线手册"><span>CSS3手册</span></a></li>
        <li><a title="字体专区"><img  src="{$config_siteurl}statics/default/images/1.gif" alt="字体专区"/><span>字体专区</span></a></li>
        <li><a href="{:getCategory(9,'url')}" title="常用软件"><img  src="{$config_siteurl}statics/default/images/4.gif" alt="软件专区"/><span>软件专区</span></a></li>
        <li><a href="{:getCategory(3,'url')}" title="PS专区"><img  src="{$config_siteurl}statics/default/images/5.gif" alt="在线P图"/><span>在线PS</span></a></li>
        <li><a href="{:getCategory(7,'url')}" title="站长快迅"><img  src="{$config_siteurl}statics/default/images/2.gif" alt="站长快讯"/><span>站长快讯</span></a></li>
        <div style="clear:both;"></div>
      </ul>
    </div>
    <div class="hot_news">
      <h2><span class="h2_txt">热门点击</span></h2>
      <ul>
      <content action="hits" modelid="1"  order="weekviews DESC" num="10">
      <volist name="data" id="vo">
        <li><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></li>
      </volist>
      </content>
      </ul>
    </div>
    <div class="right_tag">
      <h2><span class="h2_txt">网站云标签</span></h2>
      <div class="right_tag_con"> 
      <tags action="top"  num="12"  order="hits DESC">
      <volist name="data" id="vo">
          <a title="{$vo.tag}" href="{$vo.url}">{$vo.tag}</a> 
      </volist>
      </tags> 
      </div>
    </div>
  </div>
  <div style="clear:both"></div>
  <!--top part end--> 
</div>
<!--top part end-->
<div class="w972s margin8">
  <div class="web_jc">
    <h2><span class="more right"><a href="{:getCategory(1,'url')}" target="_blank">更多>></a></span><span class="h2_txt">网页教程</span></h2>
    <content action="hits" catid="1"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="1"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div class="web_jc" style="margin-left:8px;">
    <h2><span class="more right"><a href="{:getCategory(2,'url')}" target="_blank">更多>></a></span><span class="h2_txt">前端开发</span></h2>
    <content action="hits" catid="2"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="2"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div class="web_jc" style="margin-left:7px;">
    <h2><span class="more right"><a href="{:getCategory(3,'url')}" target="_blank">更多>></a></span><span class="h2_txt">PS教程</span></h2>
    <content action="hits" catid="3"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="3"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div style="clear:both"></div>
</div>
<div class="big_ad1 margin8" style="padding: 0;"> 
  <img src="http://placekitten.com/970/102" />
</div>
<div class="w972s margin8">
  <div class="web_jc">
    <h2><span class="more right"><a href="{:getCategory(6,'url')}" target="_blank">更多>></a></span><span class="h2_txt">SEO优化</span></h2>
    <content action="hits" catid="6"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="6"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div class="web_jc" style="margin-left:8px;">
    <h2><span class="more right"><a href="{:getCategory(7,'url')}" target="_blank">更多>></a></span><span class="h2_txt">站长杂谈</span></h2>
    <content action="hits" catid="7"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="7"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div class="web_jc" style="margin-left:7px;">
    <h2><span class="more right"><a href="{:getCategory(9,'url')}" target="_blank">更多>></a></span><span class="h2_txt">常用软件</span></h2>
    <content action="hits" catid="9"  order="weekviews DESC" num="1">
    <volist name="data" id="vo">
    <dl>
      <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}" /></a></dt>
      <dd class="textname"><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,15}</a></dd>
      <dd  class="texttxt" title="{$vo.description}">{$vo.description|str_cut=###,40}</dd>
      <div style="clear:both"></div>
    </volist>
    </content>
    </dl>
    <ul>
    <content action="lists" catid="9"  order="id DESC" num="7">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}"><span class="right"> {$vo.updatetime|date="m-d",###}</span>{$vo.title|str_cut=###,18}</a></li>
    </volist>
    </content>
    </ul>
  </div>
  <div style="clear:both"></div>
</div>
<div class="big_ad1" style="padding: 0;"> 
  <img src="http://lorempixel.com/970/102" />
</div>
<div class="art_pic">
  <h2><span class="more right"><a href="{:getCategory(8,'url')}">更多>></a></span><span class="h2_txt">设计欣赏/artist</span></h2>
  <ul>
    <content action="lists" catid="8"  order="id DESC" num="4">
    <volist name="data" id="vo">
    <li><a href="{$vo.url}"><span>{$vo.title}</span><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}"/></a></li>
    </volist>
    </content>
    <div style="clear:both"></div>
  </ul>
</div>
<template file="Content/footer.php"/>
<script type="text/javascript">$(function (){$(window).toTop({showHeight : 100,});});</script>
</body>
</html>
