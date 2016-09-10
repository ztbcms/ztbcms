<?php if (!defined('CMS_VERSION')) exit(); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><if condition=" isset($SEO['title']) && !empty($SEO['title']) ">{$SEO['title']}</if>{$SEO['site_title']}</title>
<meta name="description" content="{$SEO['description']}" />
<meta name="keywords" content="{$SEO['keyword']}" />
<!--[if IE 5]>
<style type="text/css">
body,html{text-align:center}
*{ text-align:left}
.header .search .text{height:26px;}
</style>
<![endif]-->
<link href="{$Config.siteurl}statics/css/reset.css" rel="stylesheet" type="text/css" />
<link href="{$Config.siteurl}statics/css/default_blue.css" rel="stylesheet" type="text/css" />
<link href="{$Config.siteurl}statics/css/download.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<style type="text/css">
    	 body, html{ background:#FFF!important;}
    </style>
    	<a href="{$fileurl}" class="xzs_btn"></a>
	</body>
</html>
