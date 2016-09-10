<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>添加头像 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$config_siteurl}statics/js/swfobject.js"></script>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<script type="text/javascript" src="{$model_extresdir}js/fullAvatarEditor.js"></script>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/passport.css" />
</head>
<body>
<template file="Member/Public/regHeader.php"/>
<!--修改头像-->
<div id="modifyAvatar" class="profile" style="float: none;">
  <div class="title">会员登录</div>
  <div class="avatar_box">
    <div class="avatarTitle"> 当前头像<span>设置头像</span> </div>
    <div class="myAvatar" style="margin-left: 0;"> <img class="avatar-160" id="my-avatar" width="160" height="160" src="{:U('Api/Avatar/index',array('uid'=>$uid,'size'=>180))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'"/> </div>
    <div class="myAvatarUpload">
    	<div id="myContent"></div>
        {$user_avatar}
    </div>
    <div class="style" id="next"> <a href="{:U("Member/Index/home")}" id="next">下一步</a> </div>
  </div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript">
//头像上传回调
function fullAvatarCallback(msg) {
    switch (msg.code) {
    case 1:
        
        break;
    case 2:
        
        break;
    case 3:
        if (msg.type == 0) {
            
        } else if (msg.type == 1) {
            alert("摄像头已准备就绪但用户未允许使用！");
        } else {
            alert("摄像头被占用！");
        }
        break;
    case 4:
        alert("图像文件过大！");
        break;
    case 5:
        if (msg.type == 0) {
            $('#my-avatar').attr('src',"{:U('Api/Avatar/index',array('uid'=>$uid,'size'=>180))}");
        } else {
			alert(msg.content.msg);
		}
        break;
    }
}
</script>
</body>
</html>
