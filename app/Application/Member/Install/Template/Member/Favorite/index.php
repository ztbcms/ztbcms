<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的收藏 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
</head>
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="user_center">
    <template file="Member/Public/homeUserMenu.php"/>
    <div class="user_main">
      <div class="uMain_content">
        <div class="main_nav">
          <ul>
            <li class="like"><a class="on" href="{:U('Favorite/index')}">我的收藏</a></li>
          </ul>
        </div>
        <div class="main_nav2">
          <ul>
            <li  class="current"><a  href="javascript:;;">全部类型</a></li>
          </ul>
          <div id="tooltip" class="refresh"><a id="refresh" class="eda" type="0" cid="4" href="javascript:;;" title="查看帮助" ></a></div>
        </div>
        <div id="favoritesList" class="minHeight500">
          <div class="private_dance_list">
            <if condition=" empty($favorite) ">
            <div class="nothing">您没有任何收藏。</div>
            <else/>
            <ul id="list">
              <li class="title">
                <div class="song">标题</div>
                <div class="time">收藏时间</div>
                <div class="deleting">删除</div>
              </li>
              <volist name="favorite" id="vo">
              <li <if condition=" $i%2==0 ">class="c2"</if>>
                <div class="song">
                  <div class="aleft">
                  <a class="mname" href="{$vo.url}" target="p">{$vo.fid}.&nbsp;&nbsp;{$vo.title}</a>
                  </div>
                </div>
                <div class="time">{$vo.datetime|format_date}</div>
                <div class="action"><a class="del" did="{$vo.fid}" title="删除收藏" href="javascript:;"></a></div>
              </li>
              </volist>
            </ul>
            </if>
          </div>
          <div class="page">
            {$Page}
          </div>
        </div>
      </div>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script type="text/javascript" src="{$model_extresdir}js/dance.js"></script>
<script type="text/javascript">
danceLib.likeDelInit();
</script>
</body>
</html>
