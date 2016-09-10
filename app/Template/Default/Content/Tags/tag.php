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
   <tags action="lists" tag="$tag" num="5" page="$page" cache="3600">
    <div class="article_lanmu">
      <h2><span class="h2_text">{:getCategory($catid,'catname')}</span></h2>
      <volist name="data" id="vo">
      <dl>
        <dt><a href='{$vo.url}' title='{$vo.title}'><img src='<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>' alt='{$vo.title}'/></a></dt>
        <dd class="arc_title"><a href="{$vo.url}" title="{$vo.title}">{$vo.title}</a></dd>
        <dd class="arc_desc">{$vo.description}...</dd>
        <dd class="arc_info"><span>所属栏目：<a href='{:getCategory($catid,'url')}'>{:getCategory($catid,'catname')}</a></span> <span>更新日期：{$vo.updatetime|date="m-d H:i:s",###}</span> <span>阅读次数：{$vo.views}</span></dd>
        <div style="clear:both"></div>
      </dl>
      </volist>
    </div>
    <div class="fanye">
      <ul>
        {$pages}
      </ul>
      <div style="clear:both"></div>
    </div>
    </tags>
  </div>
  <!--article_list_left end-->
  <div class="article_list_right right">
    <div class="ad250" style="width:250px;height:250px;margin-bottom:8px;border:1px solid #ccc;overflow:hidden"> 
      <img src="http://placekitten.com/250/250" /> 
    </div>
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
    <div class="ad250" style="width:250px;height:250px;margin-bottom:8px;border:1px solid #ccc;overflow:hidden"> 
      <img src="http://placekitten.com/250/250" />
    </div>
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
    <!--随机推荐 end--> 
  </div>
  <div style="clear:both"></div>
</div>
<template file="Content/footer.php"/>
<script type="text/javascript">$(function (){$(window).toTop({showHeight : 100,});});</script>
</body>
</html>
