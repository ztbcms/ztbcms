<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$Config.sitename} - 搜索</title>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<style type="text/css">
html {
	overflow: auto
}
body {
	font: 12px Arial;
	margin: 0;
	padding: 0
}
p, form {
	margin: 0;
	padding: 0
}
#ua {
	text-align: right;
	height: 16px;
	font-size: 14px;
	margin: 9px 10px 0
}
h1 {
	text-align: center;
	overflow: hidden;
	font-size: 0;
	margin: 25px auto 34px;
}
h1 img {
	border: none 0;
	display: inline-block;
	margin: 0 auto
}
#tb {
	margin: 0 0 7px 15px
}
#tb a, #tb b {
	font-size: 14px;
	margin-right: 22px;
	zoom: 1
}
#sc {
	width: 500px;
	height: 207px;
	margin: 0 auto
}
#s, .userInt {
	border-radius: 0;
	background: url({$config_siteurl}statics/images/soso_sp10.png) no-repeat
}
#s {
	position: relative;
	background-position: -90px 0;
	height: 36px;
	padding-left: 3px
}
#s_input {
	width: 402px;
	border: 0 none;
	float: left;
	font: 16px Arial;
	height: 18px;
	outline: 0 none;
	margin-top: 3px;
	padding: 7px 5px 5px;
}
#s_button {
	width: 85px;
	height: 36px;
	background: none;
	border: 0 none;
	float: left;
	text-indent: -9999px;
	cursor: pointer
}
#smart_pop {
	position: absolute;
	z-index: 99;
	top: 34px;
	left: 3px;
	width: 410px;
	border: 1px solid #d4d4d4;
	border-top: none;
	background: #fff
}
#smart_pop div div {
	padding-left: 5px;
	text-decoration: none;
	vertical-align: middle;
	height: 23px;
	cursor: default;
	font: bold 14px/23px Verdana, Arial;
	color: #333;
}
#smart_pop div div b {
	font-weight: normal
}
#smart_pop div.mouseover {
	background: #d1e5fc
}
#ad, #bm, #cp {
	text-align: center;
	margin-top: 9px
}
#ad {
	margin-top: 0
}
#bm {
	color: #666;
	margin: 16px 0 0;
	margin: 15px 0 0\9;
}
#bm a {
	margin: 0 6px
}
#cp, #cp a {
	color: #000;
	text-decoration: none
}
#bm a:hover, #cp a:hover {
	text-decoration: underline
}
a:link, a:visited, #tb a:link, #tb a:visited {
	color: #059
}
.userInt {
	width: 16px;
	height: 16px;
	background-position: -484px -41px;
	line-height: 999em;
	display: inline-block;
	overflow: hidden;
	vertical-align: text-bottom;
}
#ua a {
	zoom: 1;
}
#pl {
	font-size: 14px;
	text-align: center;
	margin-bottom: 95px;
}
#pl a {
	margin: 0 5px;
}
#tb {
	position: relative;
}

.jrLogo {
	background-color: #fff;
}
.jrLogo h1 {
	margin: 0 auto 9px;
	margin-bottom: 10px\0;
}
#tbh {
	width: 498px;
	height: 23px;
	background-color: #fffbdf;
	border: 1px solid #f9e389;
	white-space: nowrap;
	padding-top: 8px;
	margin: 9px auto 0;
	overflow: hidden;
	zoom: 1
}
#tbh strong {
	color: #f70;
	font: normal 14px/100% '\5FAE\8F6F\96C5\9ED1', '\5B8B\4F53';
	float: left;
	padding-left: 8px
}
#tbh a {
	font-size: 13px;
	line-height: 1.231;
	float: left;
	margin-right: 12px
}
</style>
</head>
<body>
<div id="ua"></div>
<h1><img src="{$config_siteurl}statics/images/hei_logo.png" alt="搜索" title="搜索"></h1>
<div id="sc" >
  <div id="s">
     <form name="flpage" action="{:U('Search/Index/index')}" method="post">
      <input type="hidden" name="g" value="Search" />
      <input type="text" value=""  id="s_input" name="q" x-webkit-speech placeholder="请输入关键字..." />
      <input type="submit" id="s_button" value="搜搜" />
    </form>
  </div>
</div>
<p id="cp">Copyright &copy; 2016 ztbcms.com. All Rights Reserved. </p>
</body>
</html>
