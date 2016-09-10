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
<script src="{$config_siteurl}statics/js/jquery.js" type="text/javascript"></script>
<script src="{$config_siteurl}statics/default/js/w3cer.js" type="text/javascript"></script>
</head>
<body>
<template file="Content/header.php"/>
<div class="map"><span class="home_ico">当前位置：<a href="{$config_siteurl}">{$Config.sitename}</a> &gt; <navigate catid="$catid" space=" &gt; " /></span>
  <p style="float:right;padding-right:15px;"></p>
</div>
<div class="w972">
  <content action="category" catid="$catid"  order="listorder ASC" >
  <volist name="data" id="vo">
  <div class="list_image">
    <h2><span class="more right"><a href="{$vo.url}" target="_blank">更多>></a></span><span class="h2_text">{$vo.catname}</span></h2>
    <div style="clear:both"></div>
    <ul>
    <content action="lists" catid="$vo['catid']"  order="id DESC" num="8">
    <volist name="data" id="vo">
      <li><a href="{$vo.url}" title="{$vo.title}" target="_blank"><img src="<if condition="$vo['thumb']">{$vo.thumb}<else />{$config_siteurl}statics/default/images/defaultpic.gif</if>" alt="{$vo.title}"/><span>{$vo.title|str_cut=###,40}</span></a></li>
    </volist>
    </content>
    </ul>
    <div style="clear:both"></div>
  </div>
  </volist>
  </content>
</div>
<template file="Content/footer.php"/>
<script type="text/javascript">$(function (){$(window).toTop({showHeight : 100,});});</script>
</body>
</html>
