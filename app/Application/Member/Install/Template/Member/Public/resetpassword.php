<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>重设密码 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/passport.css" />
<style>
/*tip message start*/
.tip_message,.tip_message .tip_ico_succ,.tip_message .tip_ico_fail,.tip_message .tip_ico_hits,.tip_message .tip_content,.tip_message .tip_end{ background-image:url({$model_extresdir}images/tip_message.png);_background-image:url({$model_extresdir}images/tip_message_ie6.png);color:#606060;float:left;font-size:14px;font-weight:bold;height:54px;line-height:54px;}
.tip_message{display:none;background:none;position:absolute;font-family:Arial,Simsun,"Arial Unicode MS",Mingliu,Helvetica;font-size:14px;}
.tip_message .tip_ico_succ
{background-position:-6px 0;background-repeat:no-repeat;width:45px;}
.tip_message .tip_ico_fail{background-position:-6px -108px;background-repeat:no-repeat;width:45px;}
.tip_message .tip_ico_hits{background-position:-6px -54px;background-repeat:no-repeat;width:45px;}
.tip_message .tip_end{background-position:0 0;background-repeat:no-repeat;width:6px;}
.tip_content{background-position:0 -161px;background-repeat:repeat-x;padding:0 20px 0 8px; word-break:keep-all;white-space:nowrap;}
.tip_message .tip_message_content{position:absolute; left:0; top:0; width:100%;height:100%;z-index:65530;}
.ie6_mask{position:absolute; left:0; top:0; width:100%;height:100%;background-color:transparent;z-index:-1;filter:alpha(opacity=0);}
/* tip message end */
</style>
<body>
<template file="Member/Public/regHeader.php"/>
<div class="anew_body">
  <div class="anew_title">重设密码</div>
  <div class="anew_box">
    <div class="text"><span> 重新设置您在&nbsp; <strong>{$Config.sitename}</strong> &nbsp;注册的会员账号登录密码。 </span></div>
    <div class="register_box_left">
      <ul id="resetPass">
        <li>
          <div class="rM_title">您的昵称：</div>
          <div class="rM_input">
            <input type="hidden" id="key" name="key" value="{$key}"/>
            {$userinfo.username}</div>
        </li>
        <li>
          <div class="rM_title">设置密码：</div>
          <div id="rM_rpassword" class="rM_input">
            <input id="rpassword" class="input_normal" type="password" maxlength="20" name="rpassword" style="width:190px;"/>
          </div>
          <div id="mpassword"></div>
        </li>
        <li>
          <div class="rM_title">确认密码：</div>
          <div id="rM_rpassword2" class="rM_input">
            <input id="rpassword2" class="input_normal" type="password" maxlength="20" name="rpassword2" style="width:190px;">
          </div>
          <div id="mpassword2"></div>
        </li>
        <li>
          <div class="rM_noleft">
            <input id="submit" class="submit" type="submit" value="修改账号密码">
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript" src="{$model_extresdir}js/lostpass.js"></script>
<script type="text/javascript">
	lostpass.resetInit();
</script>
</body>
</html>
