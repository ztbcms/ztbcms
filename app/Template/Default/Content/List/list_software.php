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
<script src="{$config_siteurl}statics/js/jquery.js" type="text/javascript"></script>
<script src="{$config_siteurl}statics/default/js/w3cer.js" type="text/javascript"></script>
</head>
<body>
<template file="Content/header.php"/>
<div class="map"><span class="home_ico">当前位置：<a href="{$config_siteurl}">{$Config.sitename}</a> &gt; <navigate catid="$catid" space=" &gt; " /></span>
  <p style="float:right;padding-right:15px;"></p>
</div>
<div class="article_list_con w972">
  <div class="article_list_left left">
    <content action="lists" catid="$catid" order="id DESC" num="18" page="$page">
    <div class="jslanmu">
      <h2><span class="h2_text">{:getCategory($catid,'catname')}</span></h2>
      <div style="clear:both"></div>
      <volist name="data" id="vo">
      <dl>
        <dt><a href="{$vo.url}" target="_blank" title="{$vo.title}"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}"/></a></dt>
        <dd class="span1"><a href="{$vo.url}" title="{$vo.title}" target="_blank">{$vo.title}</a></dd>
        <dd class="span2"><a href="{:getCategory($catid,'url')}" title="{:getCategory($catid,'catname')}">{:getCategory($catid,'catname')}</a>&nbsp;/&nbsp;{$vo.updatetime|date="m-d H:i:s",###} </dd>
      </dl>
      </volist>
      <div style="clear:both"></div>
    </div>
    <!--lanmu end-->
    <div class="fanye">
      <ul>
        {$pages}
      </ul>
      <div style="clear:both"></div>
    </div>
    </content>
    <!--翻页 end--> 
  </div>
  <!--article_list_left end-->
  <div class="article_list_right right">

     <div class="rand_pic">
      <h2><span class="h2_txt">推荐图文</span></h2>
      <ul>
      <content action="lists" catid="$catid"  order="id DESC" num="4" thumb="1">
       <volist name="data" id="vo">
        <li><a href="{$vo.url}" title="{$vo.title}"><img src='<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>' border='0' width='140' height='100' alt='{$vo.title}'><span title="{$vo.title}">{$vo.title|str_cut=###,50}</span></a></li>
       </volist>
      </content>
      </ul>
      <div style="clear:both"></div>
    </div>
    <!--热门推荐 end-->
    <div class="hot_tj">
      <h2><span class="h2_txt">热点推荐</span></h2>
      <ul>
      <content action="hits" catid="$catid"  order="weekviews DESC" num="10">
       <volist name="data" id="vo">
        <li><a href="{$vo.url}" title="{$vo.title}">{$vo.title|str_cut=###,42}</a></li>
      </volist>
      </content>
      </ul>
      <div style="clear:both"></div>
    </div>
  </div>
  <div style="clear:both"></div>
</div>
<template file="Content/footer.php"/>
<script type="text/javascript">$(function (){$(window).toTop({showHeight : 100,});});</script>
</body>
</html>
