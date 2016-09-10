<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>注册账号 - {$Config.sitename}</title>
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
</head>
<body>
<template file="Member/Public/regHeader.php"/>
<div class="register_body">
  <div class="register_title">会员登录</div>
  <div class="register_box">
    <div class="register_box_left" id="regData">
      <ul>
        <form onSubmit="return false;">
          <li>
            <div class="title">用户名</div>
            <div id="loginName" class="input ">
              <input id="username" class="input_normal" type="text" name="username"/>
            </div>
            <div id="musername"></div>
          </li>
          <li>
            <div class="title">邮箱</div>
            <div id="loginEmail" class="input ">
              <input id="remail" class="input_normal" type="text" name="remail"/>
            </div>
            <div id="memail"></div>
          </li>
          <li>
            <div class="title">设置密码</div>
            <div id="loginName" class="input">
              <input id="rpassword" class="input_normal" type="password" maxlength="20" name="rpassword"/>
            </div>
            <div id="mpassword"></div>
          </li>
          <li>
            <div class="title">确认密码</div>
            <div id="loginName" class="input">
              <input id="rpassword2" class="input_normal" type="password" maxlength="20" name="rpassword2"/>
            </div>
            <div id="mpassword2"></div>
          </li>
          <li>
            <div class="title">昵称</div>
            <div id="loginName" class="input">
              <input id="rnickname" class="input_normal" type="text" maxlength="20" name="rnickname"/>
            </div>
            <div id="mnickname"></div>
          </li>
          <li>
            <div class="title">验证码</div>
            <div id="loginName" class="input">
              <input id="rvCode" class="input_normal" type="text" maxlength="4" name="rvCode" style="width:70px;"/>
            </div>
            <div class="vcode">
              <div class="noleft"> <img align="absmiddle" src="{:U("Api/Checkcode/index","type=userregister&code_len=4&font_size=14&width=80&height=24&font_color=&background=")}" title="看不清？点击更换" id="authCode" /> </div>
              <div class="reloadCode"> <a href="javascript:;" id="changeAuthCode">看不清？换一张</a> </div>
            </div>
            <div id="mvCode"></div>
          </li>
          <li>
            <div class="noleft" id="register">
              <input id="submit" class="input_register" type="submit" value="立即注册"/>
            </div>
          </li>
        </form>
      </ul>
    </div>
    <div  class="register_box_right">
      <div class="title">已有账号？</div>
      <div class="reg"> <a href="{:U('Index/login')}">赶快登录吧>></a> </div>
      <div class="qq">
        <div class="or">或使用合作网站账号登录</div>
        <if condition=" $Member_config['qq_akey'] && $Member_config['qq_skey'] ">
        <a href="{:U('Connectqq/index')}" class="qq_login"> </a> 
        </if>
        <if condition=" $Member_config['sinawb_akey'] && $Member_config['sinawb_skey'] ">
        <a href="{:U('Connectsina/index')}" class="sina_login"> </a> 
        </if>
      </div>
      <div class="current"> <a href="javascript:;;">当前有
        <p>{$count}</p>
        个帅哥美女活跃！</a> </div>
      <ul class="clearfix">
      <volist name="heat" id="vo">
        <li> <a target="_blank" href="{:UM('Home/index',array('userid'=>$vo['userid']))}"> <img src="{:U('Api/Avatar/index',array('uid'=>$vo['userid'],'size'=>48))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'"> </a> </li>
      </volist>
      </ul>
    </div>
  </div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript" src="{$model_extresdir}js/register.js"></script> 
<script type="text/javascript">
	register.init();
	select.init('openQQ');
	</script>
</body>
</html>
