<?php if (!defined('CMS_VERSION')) exit(); ?>
  <template file="Wap/header.php"/>
<div style="width:100%; height:600px; background:#F9C">
   <h1 title="{$title}">{$title}</h1>
      <p class="info"> 出处:本站原创&nbsp;&nbsp;&nbsp;发布时间:{$updatetime}&nbsp;&nbsp;&nbsp;   您是第<span id="hits">0</span>位浏览者 </p>
      <p>
       {$content}
       </p>
        <p>  {$pages}  </p>
    </div>
    
    <script type="text/javascript">
$(function (){
	 
	$.get("{$config_siteurl}api.php?m=Hits&catid={$catid}&id={$id}", function (data) {
	    $("#hits").html(data.views);
	}, "json");
});
 
</script> 
<template file="Wap/footer.php"/>
