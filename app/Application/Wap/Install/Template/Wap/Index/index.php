<?php if (!defined('CMS_VERSION')) exit(); ?>
<template file="Wap/header.php"/>
<div style="width:100%; height:200px; background:#F9C">
 <content action="lists" catid="4"  order="id DESC" num="6" thumb="1">
                <volist name="data" id="vo">
 <div style="float:left;margin-left:5px;margin-top:4px;"> 
              <p class=img><a  href="{:geturl($vo)}" target="_blank"><img style="width:170px;height:150px;border:1px solid #fff;" src="<if condition="$vo['thumb']">{:thumb($vo['thumb'],170,150)}
                      <else />
                      {$config_siteurl}statics/default/images/defaultpic.gif
                      </if>" border="0"></a></p>
              <p class=title style="text-align:center;"><a href="{:geturl($vo)}" title='{$vo.title}' target="_blank">{$vo.title|str_cut=###,10}</a></p>
            </div>
                </volist>
              </content>
              </div>
<template file="Wap/footer.php"/>
 
