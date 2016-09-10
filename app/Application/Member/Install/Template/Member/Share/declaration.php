<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分享规范 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" media="all"/>
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
<style>
.continued a{display: inline;float: left;width:163px;height:40px;line-height:28px;background: no-repeat;color:#0C0B0B;text-align:center;margin-bottom:20px;font-size: 20px;}
</style>
</head>
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="center">
    <div class="main_nav">
      <div class="title"></div>
      <ul>
        <li class="share"><a href="{:U('Share/index')}">我分享的</a></li>
        <li class="deleting"><a href="{:U('Share/index',array('type'=>'check'))}">已审核的</a></li>
        <li class="audit"><a href="{:U('Share/index',array('type'=>'checking'))}">审核中的</a></li>
      </ul>
      <div class="action"><a class="on" target="_self" href="{:U('Share/add')}">发布分享</a></div>
      <div class="return"><a title="个人中心" target="_blank" href="{:U('Index/home')}">个人中心</a></div>
    </div>
    <div id="danceNewList" class="minHeight500">
      <div class="standard">
        <h3>分享声明</h3>
        <div class="text_box">
          <p>1、您在分享前应先阅读分享规范。</p>
          <p>2、本站不保证能收录您分享的每一篇资讯，分享前请先明确这一点。</p>
          <p>3、您分享的资讯可能不会被网站收录，这很可能只是单方面因为网站认为本资讯不适合本站的原因而非您的资讯有问题。</p>
          <p>4、如果您分享的资讯审核通过了，但被用户举报等问题，那么网站有权对已经通过审核的分享进行删除操作。</p>
          <p>5、对于多次分享不合格咨询的会员，本站将冻结一定时间的分享权限。</p>
          <p>如果您不能接受以上条款，请不要分享资讯，以免因为分享的资讯没有被收录或是已经收录的资讯被删除，而发生不愉快的事。</p>
        </div>
      </div>
      <div class="continued"><a  target="_self" href="{:U('Share/add','step=2')}">我同意</a></div>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
</body>
</html>
